<?php

namespace BinSoul\Db\MySQL;

/**
 * Fetches data from a {@see mysqli_result} instance.
 */
class QueryResult extends AbstractResult
{
    /**
     * @param \mysqli_result $resource
     * @param string         $column
     *
     * @return mixed[]
     */
    protected function fetchResource($resource, $column)
    {
        $fields = $resource->fetch_fields();
        if (count($fields) == 0) {
            return [];
        }

        $result = $resource->fetch_all(MYSQLI_ASSOC);
        if (!is_array($result)) {
            return [];
        }

        $resource->free();

        $this->fixTypes($result, $fields, $column);

        return $result;
    }
}
