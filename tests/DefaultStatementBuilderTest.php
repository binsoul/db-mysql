<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\MySQL\DefaultStatementBuilder;

class DefaultStatementBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function selectStatement()
    {
        return [
            [
                'table',
                ['*'],
                'column=?',
                'SELECT * FROM `table` WHERE column=?'
            ],
            [
                'table',
                ['column'],
                'column=?',
                'SELECT `column` FROM `table` WHERE column=?'
            ],
            [
                'table',
                ['column AS a'],
                '',
                'SELECT column AS a FROM `table`'
            ],
            [
                'table t',
                ['t.column'],
                '',
                'SELECT t.column FROM table t'
            ],
        ];
    }

    /**
     * @dataProvider selectStatement
     */
    public function test_select_statement($table, $columns, $condition, $expected)
    {
        $builder = new DefaultStatementBuilder();
        $this->assertEquals($expected, $builder->selectStatement($table, $columns, $condition));
    }

    public function insertStatement()
    {
        return [
            [
                'table',
                [
                    'col1' => 'value1',
                    'col2' => 'value2'
                ],
                'INSERT INTO `table` (`col1`,`col2`) VALUES(?,?)'
            ],
            [
                'table',
                [
                    'col1' => 'value1',
                    'col2' => null
                ],
                'INSERT INTO `table` (`col1`,`col2`) VALUES(?,NULL)'
            ],
            [
                'table',
                [
                    'col1' => 'value1',
                    'col2' => 'now()'
                ],
                'INSERT INTO `table` (`col1`,`col2`) VALUES(?,now())'
            ],
        ];
    }

    /**
     * @dataProvider insertStatement
     */
    public function test_insert_statement($table, $data, $expected)
    {
        $builder = new DefaultStatementBuilder();
        $this->assertEquals($expected, $builder->insertStatement($table, $data));
    }

    public function updateStatement()
    {
        return [
            [
                'table',
                [
                    'col1' => 'value',
                    'col2' => 'value'
                ],
                'a=b',
                'UPDATE `table` SET `col1`=?,`col2`=? WHERE a=b'
            ],
            [
                'table',
                [
                    'col1' => 'value',
                    'col2' => null
                ],
                '',
                'UPDATE `table` SET `col1`=?,`col2`=NULL'
            ],
            [
                'table',
                [
                    'col1' => 'value',
                    'col2' => 'now()'
                ],
                '',
                'UPDATE `table` SET `col1`=?,`col2`=now()'
            ],
        ];
    }

    /**
     * @dataProvider updateStatement
     */
    public function test_update_statement($table, $data, $condition, $expected)
    {
        $builder = new DefaultStatementBuilder();
        $this->assertEquals($expected, $builder->updateStatement($table, $data, $condition));
    }

    public function deleteStatement()
    {
        return [
            [
                'table',
                '',
                'DELETE FROM `table`'
            ],
            [
                'table',
                'a=b',
                'DELETE FROM `table` WHERE a=b'
            ],
        ];
    }

    /**
     * @dataProvider deleteStatement
     */
    public function test_delete_statement($table, $condition, $expected)
    {
        $builder = new DefaultStatementBuilder();
        $this->assertEquals($expected, $builder->deleteStatement($table, $condition));
    }

    public function insertParameter()
    {
        return [
            [
                [
                    'col1' => 'val1',
                    'col2' => 'val2'
                ],
                [
                    'val1',
                    'val2'
                ]
            ],
            [
                [
                    'col1' => true,
                    'col2' => false
                ],
                [
                    1,
                    0
                ]
            ],
            [
                ['col1' => null],
                []
            ],
            [
                ['col1' => 'now()'],
                []
            ],
        ];
    }

    /**
     * @dataProvider insertParameter
     */
    public function test_insert_parameters($data, $expected)
    {
        $builder = new DefaultStatementBuilder();
        $this->assertEquals($expected, $builder->insertParameters($data));
    }

    /**
     * @dataProvider insertParameter
     */
    public function test_update_parameters($data, $expected)
    {
        $builder = new DefaultStatementBuilder();
        $this->assertEquals($expected, $builder->updateParameters($data));
    }
}
