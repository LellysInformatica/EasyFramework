<?php

App::uses('Datasource', 'Model');
App::uses('ValueParser', 'Model');

abstract class PdoDatasource extends Datasource {

    protected $affectedRows;
    protected $lastQuery;
    protected $params = array(
        'fields' => '*',
        'joins' => array(),
        'conditions' => array(),
        'groupBy' => null,
        'having' => null,
        'order' => null,
        'offset' => null,
        'limit' => null
    );

    public function __construct($config) {
        parent::__construct($config);
        $this->connect();
    }

    public function dsn() {
        return $this->config['dsn'];
    }

    public function connect($dsn = null) {
        if (!$this->connection) {
            if (is_null($dsn)) {
                $dsn = $this->dsn();
            }
            $this->connection = new PDO($dsn);
            $this->connected = true;
        }

        return $this->connection;
    }

    public function disconnect() {
        $this->connected = false;
        $this->connection = null;

        return true;
    }

    public function begin() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }

    public function insertId() {
        return $this->connection->lastInsertId();
    }

    public function affectedRows() {
        return $this->affectedRows;
    }

    public function alias($fields) {
        if (is_array($fields)) {
            if (is_hash($fields)) {
                foreach ($fields as $alias => $field) {
                    if (!is_numeric($alias)) {
                        $fields[$alias] = $field . ' AS ' . $alias;
                    }
                }
            }

            $fields = implode(',', $fields);
        }

        return $fields;
    }

    public function join($params) {
        if (is_array($params)) {
            $params += array(
                'type' => null,
                'on' => null
            );

            $join = 'JOIN ' . $this->alias($params['table']);

            if ($params['type']) {
                $join = strtoupper($params['type']) . ' ' . $join;
            }

            if ($params['on']) {
                $join .= ' ON ' . $params['on'];
            }
        } else {
            $join = $params;
        }

        return $join;
    }

    public function order($order) {
        if (is_array($order)) {
            $order = implode(',', $order);
        }

        return $order;
    }

    public function logQuery($sql) {
        return $this->lastQuery = $sql;
    }

    public function query($sql, $values = array()) {
        $this->logQuery($sql);
        $query = $this->connection->prepare($sql);
        $query->setFetchMode(PDO::FETCH_OBJ);

        $query->execute($values);

        $this->affectedRows = $query->rowCount();

        return $query;
    }

    public function fetchAll($result, $fetchMode = PDO::FETCH_OBJ) {
        return $result->fetchAll($fetchMode);
    }

    public function escape($value) {
        if (is_null($value)) {
            return 'NULL';
        } else {
            return $this->connection->quote($value);
        }
    }

    public function create($params) {
        $params += $this->params;

        $values = array_values($params['data']);
        $sql = $this->renderInsert($params);

        $query = $this->query($sql, $values);

        return $query;
    }

    public function read($params) {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();
        $values = $query->values();

        $sql = $this->renderSelect($params);
        $query = $this->query($sql, $values);

        $fetchedResult = $this->fetchAll($query);

        return $fetchedResult;
    }

    public function update($params) {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();
        $values = array_merge(array_values($params['values']), $query->values());

        $sql = $this->renderUpdate($params);
        $query = $this->query($sql, $values);

        return $query;
    }

    public function delete($params) {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();
        $values = $query->values();

        $sql = $this->renderDelete($params);
        $query = $this->query($sql, $values);

        return $query;
    }

}