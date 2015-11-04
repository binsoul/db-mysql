<?php

namespace BinSoul\Db\MySQL;

use BinSoul\Db\Connection;
use BinSoul\Db\Exception\ConnectionException;
use BinSoul\Db\Exception\OperationException;

/**
 * Implements the {@see Connection} interface for the MySQL platform.
 */
class DefaultConnection implements Connection
{
    /**
     * List of connection error codes.
     *
     * @var int[]
     */
    private static $connectionErrors = [
        1205, // ER_LOCK_WAIT_TIMEOUT,
        2006, // CR_SERVER_GONE_ERROR,
        2013, // CR_SERVER_LOST,
    ];

    /** @var bool */
    private $hasNativeDriver;
    /** @var bool */
    private $connected;
    /** @var \mysqli */
    private $mysqli;
    /** @var int */
    private $transactionCount;
    /** @var MySQLConnectionSettings */
    private $settings;

    /**
     * Constructs an instance of this class.
     *
     * @param MySQLConnectionSettings $settings
     * @param bool                    $hasNativeDriver
     */
    public function __construct(MySQLConnectionSettings $settings, $hasNativeDriver = true)
    {
        $this->settings = $settings;
        $this->hasNativeDriver = $hasNativeDriver;
    }

    /**
     * Destructs an instance of this class.
     */
    public function __destruct()
    {
        $this->close();
    }

    public function open()
    {
        if ($this->connected) {
            return false;
        }

        $this->mysqli = mysqli_init();

        $this->connected = @$this->mysqli->real_connect(
            $this->settings->getHost(),
            $this->settings->getUsername(),
            $this->settings->getPassword(),
            $this->settings->getSchema()
        );

        if (!$this->connected) {
            throw new ConnectionException(
                sprintf(
                    'Unable to connect to host "%s" as user "%s". %s.',
                    $this->settings->getHost(),
                    $this->settings->getUsername(),
                    mysqli_connect_error()
                ),
                '',
                mysqli_connect_errno()
            );
        }

        @$this->mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        @$this->mysqli->set_charset($this->settings->getCharset());

        return true;
    }

    public function close()
    {
        if (!$this->connected) {
            return false;
        }

        while ($this->transactionCount > 0) {
            $this->rollback();
        }

        @$this->mysqli->close();
        $this->connected = false;

        return true;
    }

    /**
     * Returns the type string for \mysqli_stmt::bind_param.
     *
     * @param mixed[] $parameters
     *
     * @return string
     */
    private function buildTypes($parameters)
    {
        $result = '';

        foreach ($parameters as $parameter) {
            if (is_int($parameter)) {
                $result .= 'i';
            } elseif (is_float($parameter)) {
                $result .= 'd';
            } elseif (is_string($parameter)) {
                $result .= 's';
            } elseif (is_array($parameter)) {
                if (count($parameters) > 0) {
                    $result .= $this->buildTypes($parameter);
                }
            } else {
                $result .= 'b';
            }
        }

        return $result;
    }

    /**
     * Binds the given parameters to the given statement.
     *
     * @param \mysqli_stmt $statement
     * @param mixed[]      $parameters
     *
     * @return \mysqli_stmt
     */
    private function bindParameters($statement, array $parameters)
    {
        $variables = [];
        foreach (array_keys($parameters) as $key) {
            if (!is_array($parameters[$key])) {
                $variables[] = &$parameters[$key];
            } else {
                foreach (array_keys($parameters[$key]) as $k) {
                    $variables[] = &$parameters[$key][$k];
                }
            }
        }

        $statement->bind_param($this->buildTypes($parameters), ...$variables);
    }

    public function execute($statement, array $parameters)
    {
        if (count($parameters) == 0) {
            $result = @$this->mysqli->query($statement);
            if ($result === false) {
                throw $this->buildExecption($statement, $this->mysqli->errno, $this->mysqli->error);
            }

            $result = new QueryResult($result, $this->mysqli);
        } else {
            $stmt = @$this->mysqli->prepare($statement);
            if ($stmt === false) {
                throw $this->buildExecption($statement, $this->mysqli->errno, $this->mysqli->error);
            }

            $this->bindParameters($stmt, $parameters);

            $status = @$stmt->execute();
            if ($status === false) {
                throw $this->buildExecption($statement, $stmt->errno, $stmt->error);
            }

            if ($this->hasNativeDriver && ($resource = $stmt->get_result()) !== false) {
                $result = new QueryResult($resource, $stmt);
            } else {
                $result = new StatementResult($stmt, $stmt);
            }
        }

        return $result;
    }

    public function begin()
    {
        if ($this->transactionCount == 0) {
            if (!@$this->mysqli->autocommit(false)) {
                return false;
            }

            @$this->mysqli->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $result = @$this->mysqli->query('START TRANSACTION');
        } else {
            $result = @$this->mysqli->query('SAVEPOINT point'.$this->transactionCount);
        }

        if ($result) {
            ++$this->transactionCount;
        }

        return $result;
    }

    public function commit()
    {
        if ($this->transactionCount == 0) {
            return false;
        }

        if ($this->transactionCount == 1) {
            $result = @$this->mysqli->commit();
        } else {
            $result = @$this->mysqli->query('RELEASE SAVEPOINT point'.($this->transactionCount - 1));
        }

        $this->popTransaction();

        return $result;
    }

    public function rollback()
    {
        if ($this->transactionCount == 0) {
            return false;
        }

        if ($this->transactionCount == 1) {
            $result = @$this->mysqli->rollback();
        } else {
            $result = @$this->mysqli->query('ROLLBACK TO point'.($this->transactionCount - 1));
        }

        $this->popTransaction();

        return $result;
    }

    /**
     * Removes a transaction from the current stack.
     */
    private function popTransaction()
    {
        --$this->transactionCount;

        if ($this->transactionCount == 0) {
            @$this->mysqli->autocommit(true);
        }
    }

    /**
     * Throws an exception depending on the mysqli error.
     *
     * @param string $statement
     * @param int    $errno
     * @param string $error
     *
     * @returns ConnectionException|OperationException
     */
    private function buildExecption($statement, $errno, $error)
    {
        if (in_array($errno, self::$connectionErrors)) {
            return new ConnectionException($error, $statement, $errno);
        }

        return new OperationException($error, $statement, $errno);
    }
}
