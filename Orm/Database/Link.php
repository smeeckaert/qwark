<?php
namespace Qwark\Orm\Database;

use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use Qwark\Orm\Tools;

class Link
{
    /**
     * @var \PDO $dbh
     */
    protected $dbh;
    protected $dbInfos;
    /** @var  GenericBuilder */
    protected $builder;
    protected $name;

    public function __construct($dsn, $user, $password, $options, $name)
    {
        $this->name = $name;
        $this->dbInfos = array('dsn' => $dsn, 'user' => $user, 'password' => $password, 'options' => $options);
    }

    protected function initDbh()
    {
        if (empty($this->dbh)) {
            $this->dbh = new \PDO($this->dbInfos['dsn'], $this->dbInfos['user'], $this->dbInfos['password'], $this->dbInfos['options']);
        }
    }

    public function name()
    {
        return $this->name;
    }

    /**
     * @return GenericBuilder
     */
    public function builder()
    {
        if (empty($this->builder)) {
            $this->initBuilder();
        }
        return $this->builder;
    }

    protected function initBuilder()
    {
        // @todo adapt to DMBS type
        $this->builder = new GenericBuilder();
    }

    public function close()
    {
        $this->dbh = null;
    }

    public function lastInsertId()
    {
        $this->initDbh();
        return $this->dbh->lastInsertId();
    }

    public function prepare($sql, $values)
    {
        $this->initDbh();
        $query = $this->dbh->prepare($sql);
        foreach ($values as $key => $value) {
            $query->bindValue($key, $value);
        }
        $query->execute();
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        if (!$query) {
            throw new \Exception(Tools::implode($this->dbh->errorInfo(), ' :: '));
        }
        return $query;
    }

    public function query($sql)
    {
        $this->initDbh();
        $query = $this->dbh->query($sql);
        if (!$query) {
            throw new \Exception(Tools::implode($this->dbh->errorInfo(), ' :: '));
        }
        return $query;
    }
}