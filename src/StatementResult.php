<?php

namespace BinSoul\Db\MySQL;

/**
 * Fetches data from a {@see mysqli_stmt} instance.
 */
class StatementResult extends AbstractResult
{
    /**
     * @param \mysqli_stmt $resource
     * @param string       $column
     *
     * @return mixed[]
     */
    protected function fetchResource($resource, $column)
    {
        $result = [];

        $metadata = $resource->result_metadata();
        $fields = $metadata->fetch_fields();
        if (count($fields) == 0) {
            return [];
        }

        $variables = [];
        $data = [];

        foreach ($fields as $field) {
            $variables[] = &$data[$field->name];
        }

        $resource->bind_result(...$variables);

        while ($resource->fetch()) {
            $clone = [];
            foreach ($data as $key => $value) {
                $clone[$key] = $value;
            }

            $result[] = $clone;
        }

        $resource->free_result();

        $this->fixTypes($result, $fields, $column);

        return $result;
    }
}
