<?php

// if this file accidentally gets included twice this would cause a fatal error due to attempting to define already defined functions.
// There are several ways to check if this file was already included, I chose to do so by defining a constant and checking for it.
// The function definitions NEED TO BE INSIDE AN IF. Simply doing a "if(defined) return;" at the top of the file will not work
// because (I think) function definitions get hoisted meaning the redefinition would always happen before the if condition can exit the file early.
if (!defined('DATABASE_FUNCTIONS_DEFINED')) {
    define('DATABASE_FUNCTIONS_DEFINED', true);

    function connectToDatabase($forceReConnect = false): PDO
    {
        static $db; // persistent across function calls
        $db_host = '127.0.0.1';
        $db_port = 8889;
        $db_user = 'root';
        $db_password = 'root';
        $db_db = 'db_wtp';

        if ($forceReConnect || !$db) {
            try {
                $db = new PDO(
                    "mysql:host=$db_host; port=$db_port; dbname=$db_db",
                    $db_user,
                    $db_password
                );
            } catch (PDOException $e) {
                echo "Error!: " . $e->getMessage() . "<br />";
                die();
            }
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        }

        return $db;
    }

    /***********************
     *  RAW SQL FUNCTIONS  *
     **********************/

    function sql_fetchAll(string $sql, array $vars = NULL, int $mode = PDO::FETCH_ASSOC): array
    {
        $db = connectToDatabase();
        $stmt = $db->prepare($sql);
        $stmt->execute($vars);
        return $stmt->fetchAll($mode);
    }

    function sql_fetch(string $sql, array $vars = NULL, int $mode = PDO::FETCH_ASSOC): array | false
    {
        $db = connectToDatabase();
        $stmt = $db->prepare($sql);
        $stmt->execute($vars);
        return $stmt->fetch($mode);
    }

    function sql_execute(string $sql, array $vars = NULL): PDOStatement|false
    {
        $stmt = connectToDatabase()->prepare($sql);
        $stmt->execute($vars);
        return $stmt;
    }

    /**************************************
     *  USER FRIENDLY DATABASE FUNCTIONS  *
     *************************************/

    // helper functions:

    function _getPlaceholders(array $values): array
    {
        return array_map(fn($column) => ":$column", array_keys($values));
    }

    function _toWhereSql(array $where = NULL): string
    {
        if (!$where) return '';
        $toCondition = fn($column, $placeHolder) => "$column = $placeHolder";
        $columns = array_keys($where);
        $placeHolders = _getPlaceholders($where);
        return 'WHERE ' . implode(' AND ', array_map($toCondition, $columns, $placeHolders));
    }

    function _toOrderBySql(array $orderBy = NULL): string
    {
        if (!$orderBy) return '';
        $toOrder = fn($column, $ASC_DESC) => "$column $ASC_DESC";
        $columns = array_keys($orderBy);
        $ASC_DESC = array_values($orderBy);
        return 'ORDER BY ' . implode(', ', array_map($toOrder, $columns, $ASC_DESC));
    }

    function _prefixKeys(array $array, string $prefix): array
    {
        $prefixedArray = [];
        foreach ($array as $key => $value) {
            $prefixedArray["$prefix$key"] = $value;
        }
        return $prefixedArray;
    }

    function _LeftJoinTables(array $tables): string
    {
        if (!is_array($tables[array_key_first($tables)])) $tables = [$tables];
        $sql = "";
        foreach ($tables as $join) {
            [$table1, $table2] = array_keys($join);
            [$column1, $column2] = array_values($join);
            if (!$sql) $sql = $table1;
            $sql .= " LEFT JOIN $table2 ON $table1.$column1 = $table2.$column2";
        }
        return $sql;
    }

    // userfriendly functions:

    function insert(string $tableName, array $values): int|false
    {
        $columns = implode(', ', array_keys($values));
        $placeHolders = implode(', ', _getPlaceholders($values));
        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeHolders)";
        sql_execute($sql, $values);
        $idAsString = connectToDatabase()->lastInsertId();
        return $idAsString ? (int)$idAsString : false;
    }

    function fetch(string|array $table, array $where): array | false
    {
        if (is_array($table)) $table = _LeftJoinTables($table);
        $whereSql = _toWhereSql($where);
        return sql_fetch("SELECT * FROM $table $whereSql LIMIT 1", $where);
    }

    function fetchAll(string|array $table, array $where = NULL, array $orderBy = NULL): array
    {
        if (is_array($table)) $table = _LeftJoinTables($table);
        $whereSql = _toWhereSql($where);
        $orderBySql = _toOrderBySql($orderBy);
        return sql_fetchAll("SELECT * FROM $table $whereSql $orderBySql", $where);
    }

    function fetchAllPairs(string|array $table, array $where = NULL, array $orderBy = NULL): array
    {
        if (is_array($table)) $table = _LeftJoinTables($table);
        $whereSql = _toWhereSql($where);
        $orderBySql = _toOrderBySql($orderBy);
        return sql_fetchAll("SELECT * FROM $table $whereSql $orderBySql", $where, PDO::FETCH_KEY_PAIR);
    }

    function delete(string $tableName, array $where): int
    {
        if (!$where) throw new Exception('DATABASE: Delete without a Where Clause is not allowed!');
        $whereSql = _toWhereSql($where);
        return sql_execute("DELETE FROM $tableName $whereSql", $where)
            ->rowCount();
    }

    function update(string $tableName, array $where, array $values): int
    {
        if (!$where) throw new Exception('DATABASE: Update without a Where Clause is not allowed!');
        $where = _prefixKeys($where, 'SQLwhere_');
        $whereSql = _toWhereSql($where);
        $columns = implode(', ', array_keys($values));
        $placeHolders = implode(', ', _getPlaceholders($values));
        return sql_execute("UPDATE $tableName ($columns) SET ($placeHolders) $whereSql", [...$values, ...$where])
            ->rowCount();
    }
};
