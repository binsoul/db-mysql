<?php

namespace BinSoul\Db\MySQL;

use BinSoul\Db\Connection;
use BinSoul\Db\Definition\Column;
use BinSoul\Db\Definition\ForeignKey;
use BinSoul\Db\Definition\Index;
use BinSoul\Db\Definition\PrimaryKey;
use BinSoul\Db\Definition\Schema;
use BinSoul\Db\Definition\Table;
use BinSoul\Db\DefinitionProvider;

/**
 * Implements the {@see DefinitionProvider} interface for the MySQL platform.
 */
class DefaultDefinitionProvider implements DefinitionProvider
{
    /** @var Connection */
    private $connection;

    /**
     * Constructs an instance of this class.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function listSchemas()
    {
        $result = [];

        $statement = 'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME';
        $rows = $this->connection->execute($statement);
        foreach ($rows->all() as $row) {
            $result[] = new Schema($row['SCHEMA_NAME']);
        }

        return $result;
    }

    public function listTables(Schema $schema = null)
    {
        $result = [];

        $schemaFilter = 'DATABASE()';
        if ($schema !== null) {
            $schemaFilter = '\''.$schema->getName().'\'';
        }

        $statement = '
          SELECT
            TABLE_NAME, TABLE_TYPE
          FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = '.$schemaFilter.'
          ORDER BY TABLE_NAME
        ';

        $rows = $this->connection->execute($statement);
        foreach ($rows->all() as $row) {
            if ($row['TABLE_TYPE'] == 'BASE TABLE') {
                $result[] = new Table($row['TABLE_NAME']);
            }
        }

        return $result;
    }

    public function listColumns(Table $table, Schema $schema = null)
    {
        $result = [];

        $schemaFilter = 'DATABASE()';
        if ($schema !== null) {
            $schemaFilter = '\''.$schema->getName().'\'';
        }

        $factory = new DataTypeFactory();

        $statement = '
          SELECT
            *
          FROM information_schema.COLUMNS
          WHERE
            TABLE_SCHEMA = '.$schemaFilter.' AND
            TABLE_NAME = \''.$table->getName().'\'
          ORDER BY ORDINAL_POSITION
        ';

        $rows = $this->connection->execute($statement);
        foreach ($rows->all() as $row) {
            $type = $factory->parseSQLType($row['COLUMN_TYPE']);
            if ($type === null) {
                throw new \RuntimeException(
                    sprintf(
                        'Unknown column type "%s" for column "%s".',
                        $row['COLUMN_TYPE'],
                        $row['COLUMN_NAME']
                    )
                );
            }

            $column = new Column(
                $row['COLUMN_NAME'],
                $type,
                $row['IS_NULLABLE'] == 'YES'
            );

            if ($row['IS_NULLABLE'] && $row['COLUMN_DEFAULT'] !== null) {
                $column->setDefaultValue($row['COLUMN_DEFAULT']);
            }

            if ($row['EXTRA'] == 'auto_increment') {
                $column->setAutoincrement();
            }

            $column->setComment($row['COLUMN_COMMENT']);

            $result[] = $column;
        }

        return $result;
    }

    public function getPrimaryKey(Table $table, Schema $schema = null)
    {
        $indexes = $this->buildIndexes($table, $schema);
        foreach ($indexes as $data) {
            if ($data['type'] == 'PRIMARY') {
                return new PrimaryKey(array_values($data['columns']));
            }
        }

        return;
    }

    public function listForeignKeys(Table $table, Schema $schema = null)
    {
        $result = [];

        $schemaFilter = 'DATABASE()';
        if ($schema !== null) {
            $schemaFilter = '\''.$schema->getName().'\'';
        }

        $statement = '
          SELECT
			  tc.CONSTRAINT_NAME,
			  kc.COLUMN_NAME,
			  kc.REFERENCED_TABLE_NAME,
			  kc.REFERENCED_COLUMN_NAME,
			  rc.UPDATE_RULE,
			  rc.DELETE_RULE
			FROM information_schema.TABLE_CONSTRAINTS tc
			  LEFT JOIN
			    information_schema.KEY_COLUMN_USAGE kc
			    ON kc.CONSTRAINT_NAME  = tc.CONSTRAINT_NAME AND
			       kc.TABLE_SCHEMA = tc.TABLE_SCHEMA AND kc.TABLE_NAME = tc.TABLE_NAME
			  LEFT JOIN
			    information_schema.REFERENTIAL_CONSTRAINTS rc
			    ON rc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME AND tc.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
			WHERE
			  tc.CONSTRAINT_TYPE = \'FOREIGN KEY\' AND
			  tc.TABLE_SCHEMA = '.$schemaFilter.' AND
			  tc.TABLE_NAME = \''.$table->getName().'\'
		';

        $rows = $this->connection->execute($statement);
        foreach ($rows->all() as $row) {
            $result[] = new ForeignKey(
                $row['CONSTRAINT_NAME'],
                $row['COLUMN_NAME'],
                $row['REFERENCED_TABLE_NAME'],
                $row['REFERENCED_COLUMN_NAME'],
                $row['UPDATE_RULE'],
                $row['DELETE_RULE']
            );
        }

        return $result;
    }

    public function listIndexes(Table $table, Schema $schema = null)
    {
        $result = [];

        $indexes = $this->buildIndexes($table, $schema);
        foreach ($indexes as $name => $data) {
            if ($data['type'] == 'PRIMARY') {
                continue;
            }

            $result[] = new Index($name, array_values($data['columns']), $data['type']);
        }

        return $result;
    }

    /**
     * Returns all indexes of the given table.
     *
     * @param Table  $table
     * @param Schema $schema
     *
     * @return mixed[]
     */
    private function buildIndexes(Table $table, Schema $schema = null)
    {
        $schemaFilter = 'DATABASE()';
        if ($schema !== null) {
            $schemaFilter = '\''.$schema->getName().'\'';
        }

        $statement = '
          SELECT
            s.INDEX_NAME,
            s.COLUMN_NAME,
            tc.CONSTRAINT_TYPE
		  FROM information_schema.statistics s
		  LEFT OUTER JOIN
		    information_schema.TABLE_CONSTRAINTS tc
		    ON tc.CONSTRAINT_NAME = s.INDEX_NAME AND tc.TABLE_SCHEMA = s.TABLE_SCHEMA AND tc.TABLE_NAME = s.TABLE_NAME
		  WHERE
			  s.TABLE_SCHEMA = '.$schemaFilter.' AND
			  s.TABLE_NAME = \''.$table->getName().'\'
          ORDER BY s.INDEX_NAME, s.SEQ_IN_INDEX
        ';

        $rows = $this->connection->execute($statement);
        $indexes = [];
        foreach ($rows->all() as $row) {
            $name = $row['INDEX_NAME'];
            if (!isset($indexes[$name])) {
                if ($row['CONSTRAINT_TYPE'] == 'PRIMARY KEY') {
                    $type = 'PRIMARY';
                } elseif ($row['CONSTRAINT_TYPE'] == 'UNIQUE') {
                    $type = 'UNIQUE';
                } else {
                    $type = 'INDEX';
                }

                $indexes[$name] = [
                    'type' => $type,
                    'columns' => [],
                ];
            }

            $indexes[$name]['columns'][] = $row['COLUMN_NAME'];
        }

        return $indexes;
    }
}
