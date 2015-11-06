<?php

namespace BinSoul\Db\Platform\MySQL;

use BinSoul\Db\Platform\MySQL\Platform\BuiltinFunctions;
use BinSoul\Db\StatementBuilder;

/**
 * Implements the {@see StatementBuilder} interface for the MySQL platform.
 */
class DefaultStatementBuilder implements StatementBuilder
{
    /** @var BuiltinFunctions */
    private $builtinFunctions;

    /**
     * @return BuiltinFunctions
     */
    private function builtinFunctions()
    {
        if ($this->builtinFunctions === null) {
            $this->builtinFunctions = new BuiltinFunctions();
        }

        return $this->builtinFunctions;
    }

    public function selectStatement($table, array $columns, $condition = '')
    {
        $escapedColumns = [];
        foreach ($columns as $column) {
            $escapedColumns[] = $this->escapeColumn($column);
        }

        $result = 'SELECT '.implode(',', $escapedColumns).' FROM '.$this->escapeTable($table);

        if ($condition != '') {
            $result .= ' WHERE '.$condition;
        }

        return $result;
    }

    public function insertStatement($table, array $data)
    {
        $escapedColumns = [];
        foreach (array_keys($data) as $column) {
            $escapedColumns[] = $this->escapeColumn($column);
        }

        $builtinFunctions = $this->builtinFunctions();

        $values = '';
        foreach ($data as $column => $value) {
            if ($value === null) {
                $values .= 'NULL,';

                continue;
            } elseif (is_string($value) && $builtinFunctions->containsFunction($value)) {
                $values .= $value.',';

                continue;
            }

            $values .= '?,';
        }

        $values = substr($values, 0, -1);

        return 'INSERT INTO '.$this->escapeTable($table).' ('.implode(',', $escapedColumns).') VALUES('.$values.')';
    }

    public function insertParameters(array $data)
    {
        return $this->filterParameters($data);
    }

    public function updateStatement($table, array $data, $condition = '')
    {
        $builtinFunctions = $this->builtinFunctions();

        $values = '';
        foreach ($data as $column => $value) {
            $escapedColumn = $this->escapeColumn($column);

            if ($value === null) {
                $values .= $escapedColumn.'=NULL,';

                continue;
            } elseif (is_string($value) && $builtinFunctions->containsFunction($value)) {
                $values .= $escapedColumn.'='.$value.',';

                continue;
            }

            $values .= $escapedColumn.'=?,';
        }

        $values = substr($values, 0, -1);

        $result = 'UPDATE '.$this->escapeTable($table).' SET '.$values;

        if ($condition != '') {
            $result .= ' WHERE '.$condition;
        }

        return $result;
    }

    public function updateParameters(array $data)
    {
        return $this->filterParameters($data);
    }

    public function deleteStatement($table, $condition = '')
    {
        $result = 'DELETE FROM '.$this->escapeTable($table);

        if ($condition != '') {
            $result .= ' WHERE '.$condition;
        }

        return $result;
    }

    /**
     * Escapes a table name.
     *
     * @param string $name name of the table
     *
     * @return string
     */
    private function escapeTable($name)
    {
        if (strpos($name, '.') || strpos($name, '`') || strpos($name, ' ')) {
            return $name;
        }

        return '`'.$name.'`';
    }

    /**
     * Escapes a column name.
     *
     * @param string $name name of the column
     *
     * @return string
     */
    private function escapeColumn($name)
    {
        if (strpos($name, '.') || strpos($name, '`') || strpos($name, ' ')) {
            return $name;
        }

        if ($name == '*') {
            return $name;
        }

        return '`'.$name.'`';
    }

    /**
     * Removes all values from the given array which are not used as bound parameters.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function filterParameters(array $data)
    {
        $builtinFunctions = $this->builtinFunctions();

        $result = [];
        foreach ($data as $value) {
            if ($value === null) {
                continue;
            }

            if ($value === false) {
                $value = 0;
            } elseif ($value === true) {
                $value = 1;
            }

            if (is_string($value) && $builtinFunctions->containsFunction($value)) {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }
}
