<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\MySQL\MySQLConnectionSettings;

class MySQLConnectionSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function test_construction_from_array()
    {
        $data = [
            'host' => 'localhost',
            'port' => 1234,
            'username' => 'root',
            'password' => 'password',
            'schema' => 'schema',
            'charset' => 'utf-8',
            'isWritable' => true,
        ];

        $settings = MySQLConnectionSettings::fromArray($data);

        $this->assertEquals('localhost', $settings->getHost());
        $this->assertEquals(1234, $settings->getPort());
        $this->assertEquals('root', $settings->getUsername());
        $this->assertEquals('password', $settings->getPassword());
        $this->assertEquals('schema', $settings->getSchema());
        $this->assertEquals('utf-8', $settings->getCharset());
        $this->assertTrue($settings->isWritable());
    }
}
