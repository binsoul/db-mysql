<?php

namespace BinSoul\Test\Db\Platform\MySQL\Platform;

use BinSoul\Db\Platform\MySQL\Platform\BuiltinFunctions;

class BuiltinFunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function validBuiltinFunction()
    {
        return [
            ['NOW()'],
            ['column1=rtrim(ltrim(column2))'],
        ];
    }

    /**
     * @dataProvider validBuiltinFunction
     */
    public function test_contains_builtin_function($string)
    {
        $builtin = new BuiltinFunctions();
        $this->assertTrue($builtin->containsFunction($string));
    }

    public function invalidBuiltinFunction()
    {
        return [
            ['NOW'],
            ['FOO()'],
            ['NOW-()'],
            ['column1 IN (1,2,3)'],
        ];
    }

    /**
     * @dataProvider invalidBuiltinFunction
     */
    public function test_doesnt_contain_builtin_function($string)
    {
        $builtin = new BuiltinFunctions();
        $this->assertFalse($builtin->containsFunction($string));
    }
}
