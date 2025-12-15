<?php

class Harviacode
{

    private $host;
    private $user;
    private $password;
    private $database;
    private $sql;
    private $port;

    public function __construct()
    {
        $this->connection();
    }

    public function connection()
    {
        // $subject = file_get_contents('../application/config/database.php');
        // $string = str_replace("defined('BASEPATH') OR exit('No direct script access allowed');", "", $subject);

        // $con = 'core/connection.php';
        // $create = fopen($con, "w") or die("Change your permision folder for application and harviacode folder to 777");
        // fwrite($create, $string);
        // fclose($create);

        // require $con;

        require 'koneksi.php';
        
        // $this->sql = new pdo($this->host, $this->user, $this->password, $this->database);
        try {
            $this->sql = new mysqli($this->host, $this->user, $this->password, $this->database);
            if ($this->sql->connect_error) {
                echo $this->sql->connect_error . ", please check  connection";
                die();
            }
            // $this->sql = new PDO ("mysql:host=$this->host;dbname=$this->database","$this->user","$this->password");
            // $this->sql = new PDO ("mysqli:host=$this->host;dbname=$this->database","$this->user","$this->password");

        } catch (Exception $e) {

            echo $e->getMessage();
        }

        // unlink($con);
    }

    public function table_list()
    {
        //$query = "select * from information_schema.tables where table_schema != 'information_schema' and table_schema != 'pg_catalog'";
        $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=?";
        $stmt = $this->sql->prepare($query) or die("Error code :" . $this->sql->errno . " (not_primary_field)");
        $stmt->bind_param('s', $this->database);
        $stmt->bind_result($table_name);
        $stmt->execute();
        while ($stmt->fetch()) {
            $fields[] = array('table_name' => $table_name);
        }
        return $fields;
        $stmt->close();
        $this->sql->close();

        foreach ($stmt as $row) {
            $arr = array($row['table_schema'], $row['table_name']);
            $gabung = implode('.', $arr);
            $key = $row['key_column'];
            $value = "$gabung-$key";

            $fields[] = array('table_name' => $value);
        }
        return $fields;
        // $stmt->close();
        // $this->sql->close();
    }

    function primary_field($table)
    {
        $query = "SELECT COLUMN_NAME,COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_KEY = 'PRI'";
        $stmt = $this->sql->prepare($query) or die("Error code :" . $this->sql->errno . " (primary_field)");
        $stmt->bind_param('ss', $this->database, $table);
        $stmt->bind_result($column_name, $column_key);
        $stmt->execute();
        $stmt->fetch();
        return $column_name;
        $stmt->close();
        $this->sql->close();
    }

    function not_primary_field($table)
    {
        $query = "SELECT COLUMN_NAME,COLUMN_KEY,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_KEY <> 'PRI' order by ORDINAL_POSITION asc";
        $stmt = $this->sql->prepare($query) or die("Error code :" . $this->sql->errno . " (not_primary_field)");
        $stmt->bind_param('ss', $this->database, $table);
        $stmt->bind_result($column_name, $column_key, $data_type);
        $stmt->execute();
        while ($stmt->fetch()) {
            $fields[] = array(
                'column_name' => $column_name,
                'column_key' => $column_key,
                'data_type' => $data_type
            );
        }
        return $fields;
        $stmt->close();
        $this->sql->close();
    }

    public function table_relation($table)
    {

        $query = "SELECT 
    TABLE_NAME, 
    COLUMN_NAME as column_name, 
    CONSTRAINT_NAME, 
    REFERENCED_TABLE_NAME as referenced_table, 
    REFERENCED_COLUMN_NAME as referenced_column,
    TABLE_SCHEMA
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    TABLE_NAME = '$table'
    AND TABLE_SCHEMA = '$this->database'
    AND REFERENCED_TABLE_NAME IS NOT NULL";
         //echo $query;
        $stmt = $this->sql->query($query);
        $urut = 0;
        foreach ($stmt as $row) {
            $referenced_table = $row['referenced_table'];
            $referenced_column = $row['referenced_column'];
            $query1 = "SELECT column_name, 
    data_type, 
    is_nullable, 
    column_default
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_NAME = '$referenced_table'
    and COLUMN_NAME !='$referenced_column'
    and TABLE_SCHEMA ='$this->database'
   order by ORDINAL_POSITION asc limit 1";
            //echo $query1;
            $stmt1 = $this->sql->query($query1);
            $field_relasi = $stmt1->fetch_assoc();
            //print_r($field_relasi);
            if ($field_relasi) {
                $fields[] = array(
                    'column_name' => $row['column_name'],
                    'referenced_table' => $row['referenced_table'],
                    'referenced_column' => $row['referenced_column'],
                    // 'referenced_schema' => $row['referenced_schema'],
                    'field_relasi' => $field_relasi['COLUMN_NAME'] == 'id_user' ? 'email' : $field_relasi['COLUMN_NAME']
                );
                $urut++;
            }
        }
        if ($urut != 0) {
            return $fields;
        } else {
            return $fields[] = [];
        }
    }

    public function cek_field_relation($table, $column_name)
    {

        $query = "SELECT 
    TABLE_NAME, 
    COLUMN_NAME as column_name, 
    CONSTRAINT_NAME, 
    REFERENCED_TABLE_NAME as referenced_table, 
    REFERENCED_COLUMN_NAME as referenced_column
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    TABLE_NAME = '$table'
     AND TABLE_SCHEMA = '$this->database'
    AND REFERENCED_TABLE_NAME IS NOT NULL and column_name  = '$column_name'";
        // echo $query;
        $stmt = $this->sql->query($query);
        $urut = 0;
        $row = $stmt->fetch_assoc();
        if ($row) {
            $referenced_table = $row['referenced_table'];
            $referenced_column = $row['referenced_column'];
            $query1 = "SELECT column_name, 
    data_type, 
    is_nullable, 
    column_default
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_NAME = '$referenced_table'
    and COLUMN_NAME !='$referenced_column'
     AND TABLE_SCHEMA = '$this->database'
   order by ORDINAL_POSITION asc limit 1";
            //echo $query1;
            $stmt1 = $this->sql->query($query1);
            //$stmt1->execute();
            $field_relasi = $stmt1->fetch_assoc();
            // print_r($field_relasi['COLUMN_NAME']);
            // die();
            if ($field_relasi) {
                $fields = array(
                    'column_name' => $row['column_name'],
                    'referenced_table' => $row['referenced_table'],
                    'referenced_column' => $row['referenced_column'],
                    'field_relasi' => $field_relasi['COLUMN_NAME'] == 'id_user' ? 'email' : $field_relasi['COLUMN_NAME']
                );
                return $fields;
            }
        } else {
            return 0;
        }
    }


    function all_field($table)
    {
        $query = "SELECT COLUMN_NAME,COLUMN_KEY,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? order by ORDINAL_POSITION asc";
        $stmt = $this->sql->prepare($query) or die("Error code :" . $this->sql->errno . " (not_primary_field)");
        $stmt->bind_param('ss', $this->database, $table);
        $stmt->bind_result($column_name, $column_key, $data_type);
        $stmt->execute();
        while ($stmt->fetch()) {
            $fields[] = array('column_name' => $column_name, 'column_key' => $column_key, 'data_type' => $data_type);
        }
        return $fields;
        $stmt->close();
        $this->sql->close();
    }
}

$hc = new Harviacode();
