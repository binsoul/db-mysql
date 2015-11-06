<?php

namespace BinSoul\Db\Platform\MySQL;

use BinSoul\Db\Result;

/**
 * Implements the {@see Result} interface for the MySQL platform.
 */
abstract class AbstractResult implements Result
{
    /** @var bool */
    private $isFetched = false;
    /** @var \mysqli_result|\mysqli_stmt */
    private $resource;
    /** @var int */
    private $affectedRows = 0;
    /** @var int */
    private $autoIncrementID = 0;

    /**
     * @param \mysqli_result|\mysqli_stmt|null $result
     * @param \mysqli|\mysqli_stmt|null        $metadata
     */
    public function __construct($result, $metadata)
    {
        $this->resource = $result;

        if ($metadata !== null) {
            $this->affectedRows = $metadata->affected_rows;
            $this->autoIncrementID = $metadata->insert_id;
        }
    }

    /**
     * @param mixed[][] $rows
     * @param object[]  $fields
     * @param string    $column
     */
    protected function fixTypes(array &$rows, array $fields, $column)
    {
        $types = [];
        $fixColumns = [];
        foreach ($fields as $field) {
            if ($column != '' && $field->name != $column) {
                continue;
            }

            $types[$field->name] = $field->type;
            if ($field->type == MYSQLI_TYPE_DECIMAL ||
                $field->type == MYSQLI_TYPE_NEWDECIMAL
            ) {
                $fixColumns[] = $field->name;
            }
        }

        if (count($fixColumns) == 0) {
            return;
        }

        foreach ($rows as &$row) {
            foreach ($fixColumns as $column) {
                if ($row[$column] === null) {
                    continue;
                }

                switch ($types[$column]) {
                    case MYSQLI_TYPE_DECIMAL:
                    case MYSQLI_TYPE_NEWDECIMAL:
                        $row[$column] = (float) $row[$column];

                        break;
                }
            }
        }
    }

    /**
     * @param \mysqli_stmt $resource
     * @param string       $column
     *
     * @return mixed[]
     */
    abstract protected function fetchResource($resource, $column);

    /**
     * @param string $column
     *
     * @return mixed[]
     */
    private function fetch($column = '')
    {
        if ($this->isFetched) {
            throw new \LogicException('The result has already been fetched.');
        }

        $this->isFetched = true;

        $result = [];

        if ($this->resource === null || is_bool($this->resource)) {
            return $result;
        }

        $result = $this->fetchResource($this->resource, $column);

        if ($column != '') {
            return array_column($result, $column);
        }

        return $result;
    }

    public function all()
    {
        return $this->fetch();
    }

    public function row()
    {
        $result = $this->fetch();
        if (count($result) > 0) {
            return $result[0];
        }

        return;
    }

    public function column($name)
    {
        return $this->fetch($name);
    }

    public function cell($name)
    {
        $data = $this->fetch($name);
        if (count($data) > 0) {
            return $data[0];
        } else {
            return;
        }
    }

    public function autoIncrementID()
    {
        return $this->autoIncrementID;
    }

    public function affectedRows()
    {
        return $this->affectedRows;
    }
}
