<?php
    if (isset($IncludeConfig)) {
        if ($IncludeConfig) include "config.php";
    } else {
        include "config.php";
    }
    
    class Connection {
        protected static $con;
        protected static $res;
        
        protected static $hasrow;
        protected static $rowcount;
        
        function OpenConnection() {
            global $svr, $db, $usr, $pwd;
            
            if (!isset(self::$con)) {
                self::$con = new mysqli(ServerName, DbUser, DbPwd, DbName);
                
                self::$con->set_charset('utf8');
            }
            
            if (!self::$con) {
                self::writelog('Connection Failed');

                die;
            }
        }
        
        function execute($qry) {
            try {
                mysqli_query(self::$con,$qry);
            } catch (Exception $ex) {
                self::writelog($ex->getMessage);
                self::$rowcount = 0;
                self::$hasrow = false;
            }
        }
        
        function query($qry, $log_query = false) {
            if ($log_query) self::writelog("Query Log\n$qry");
            
            self::$res = mysqli_query(self::$con, $qry);
            
            self::$rowcount = mysqli_num_rows(self::$res);  
            self::$hasrow = self::$rowcount > 0;
            
            if(!self::$res) {
                self::writelog("$qry\n".mysqli_error(self::$con));
                self::$rowcount = 0;
            }
        }
        
        function CloseRecordset() {
            while (self::$con->more_results()) {
                self::$res->close();
                
                self::$con->next_result();
                self::$res = mysqli_use_result(self::$con);
            }
        }
        
        function getrows() {
            try {
                $rows = array();
                
                while ($row = self::getrow()) array_push($rows, $row);
                
                return $rows;
            } catch (Exception $ex) {
                self::writelog($ex->getMessage);
//                $today = date("m/d/Y H:m:s");
//                $file = fopen(self::$logfile, "a");
//                fwrite($file, $today."\n".$ex->getMessage."\n".PHP_EOL);
//                fclose($file);
            }
        }
        
        function getrow() {
            try {
                return mysqli_fetch_assoc(self::$res);
            } catch (Exception $ex) {
                self::writelog($ex->getMessage);
//                $today = date("m/d/Y H:m:s");
//                $file = fopen(self::$logfile, "a");
//                fwrite($file, $today."\n".$ex->getMessage."\n".PHP_EOL);
//                fclose($file);
            }
        }
        
        function rowcount() {
            return self::$rowcount;
        }
        
        function hasrow() {
            if (self::$rowcount > 0) {
                return true;
            } else {
                return false;
            }
        }
        
        function escape($str) {
            return mysqli_real_escape_string(self::$con, $str);
        }
        
        function CloseConnection() {
            mysqli_close(self::$con);
        }
        
        function writelog($err) {
            $logfile = "error.php";
            $logpath = $_SERVER['DOCUMENT_ROOT'];
            $logpath = $logpath . '/online_exam/db';
            
            $today = date("m/d/Y H:m:s");
            $file = fopen($logpath . '/' . $logfile, "a");
            
            fwrite($file, $today . "\n" . $_SERVER['PHP_SELF'] . "\n" . $err . "\n" . PHP_EOL);
            fclose($file);
        }
    }
    
//    $cn = new Connection;
//    $cn->OpenConnection();
//
//    $cn->query("SELECT * FROM ausers LIMIT 10");
//    
//    while ($row=$cn->getrow()) {
//        echo $row["username"] . "<br>";
//    }
//
//    $cn->CloseConnection();
//
//    echo 'here';
?>

