<?php

namespace BinSoul\Db\MySQL;

use BinSoul\Db\ConnectionSettings;

/**
 * Implements the {@see ConnectionSettings} interface for the MySQL platform.
 */
class MySQLConnectionSettings implements ConnectionSettings
{
    /** @var string */
    private $host;
    /** @var string */
    private $schema;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var int */
    private $port;
    /** @var string */
    private $charset = 'utf-8';
    /** @var bool */
    private $isWritable;

    /**
     * Constructs an instance of this class.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $schema
     * @param bool   $isWritable
     * @param string $charset
     * @param int    $port
     */
    public function __construct($host, $username, $password, $schema, $isWritable, $charset = 'utf-8', $port = 3306)
    {
        $this->host = $host;
        $this->schema = $schema;
        $this->username = $username;
        $this->password = $password;
        $this->isWritable = $isWritable;
        $this->charset = $charset;
        $this->port = (int) $port;
    }

    public static function fromArray(array $settings)
    {
        $defaults = [
            'host' => '',
            'port' => 3306,
            'username' => '',
            'password' => '',
            'schema' => '',
            'charset' => 'utf-8',
            'isWritable' => true,
        ];

        $data = array_merge($defaults, $settings);

        return new self(
            $data['host'],
            $data['username'],
            $data['password'],
            $data['schema'],
            $data['isWritable'],
            $data['charset'],
            $data['port']
        );
    }

    public function isWritable()
    {
        return $this->isWritable;
    }

    /**
     * Returns the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Returns the schema.
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns the charset.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }
}
