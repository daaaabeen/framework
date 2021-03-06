<?php
/**
*  Create On 2010-7-12
*  Author Been
*  QQ:281443751
*  Email:binbin1129@126.com
**/
namespace Beenlee\Framework\Storage;

use Beenlee\Framework\Storage\DbException;

class Db {
    protected $_dbConf = null;
    protected $_conn = array('master' => null, 'slaver' => null );
    
    public function __construct($dbArr){

        $this->_dbConf['master']['_host']   =   $dbArr['master']['host'];
        $this->_dbConf['master']['_database']   =   $dbArr['master']['database'];
        $this->_dbConf['master']['_user']   =   $dbArr['master']['user'];
        $this->_dbConf['master']['_pw'] =   $dbArr['master']['pw'];
        $this->_dbConf['master']['_port'] =   $dbArr['master']['port'];
        $this->_dbConf['master']['_charset']    =   $dbArr['master']['charset'];

        if (isset($this->_dbConf['slaver'])) {
            $this->_dbConf['slaver']['_host']   =   $dbArr['slaver']['host'];
            $this->_dbConf['slaver']['_database']   =   $dbArr['slaver']['database'];
            $this->_dbConf['slaver']['_user']   =   $dbArr['slaver']['user'];
            $this->_dbConf['slaver']['_pw'] =   $dbArr['slaver']['pw'];
            $this->_dbConf['slaver']['_charset']    =   $dbArr['slaver']['charset'];
        }
    }

    /**
    * 连接所需的数据库
    */
    function connect($masterOrSlaver){
        $conn = mysqli_connect(
            $this->_dbConf[$masterOrSlaver]['_host'],
            $this->_dbConf[$masterOrSlaver]['_user'],
            $this->_dbConf[$masterOrSlaver]['_pw'],
            $this->_dbConf[$masterOrSlaver]['_database'],
            $this->_dbConf[$masterOrSlaver]['_port']
        );
        if (!$conn) {
            throw new DbException(mysqli_error($conn), mysqli_errno($conn));
        }

        mysqli_query($conn, 'set names ' . $this->_dbConf[$masterOrSlaver]['_charset']);
        return $this->_conn[$masterOrSlaver] = $conn;
    }
    
    /**
     *
     * @return boolean  true/false
     */
    public function del($sql){
        $conn = $this->getConn('master');
        if (mysqli_query($conn, $sql)) {
            $affectedRows = mysqli_affected_rows($conn);
            return $affectedRows > 0 ? $affectedRows : false;
        }
        else {
            throw new DbException(mysqli_error($conn), mysqli_errno($conn));
        }
    }

    /**
     * 更新数据
     * return bool true/false
     */
    public function update($sql){
        $conn = $this->getConn('master');
        if (mysqli_query($conn, $sql)) {
            // return mysqli_affected_rows($conn);
            $affectedRows = mysqli_affected_rows($conn);
            return $affectedRows > 0 ? $affectedRows : true;
        }
        else {
            throw new DbException(mysqli_error($conn), mysqli_errno($conn));
        }
    }

    /**
     * 插入数据
     * 
     * @return int|boolean 最后一条自增id 没有自增返回true 失败返回false
     */
    public function insert ($sql) {
        $conn = $this->getConn('master');
        if (!mysqli_query($conn, $sql)) {
            throw new DbException(mysqli_error($conn), mysqli_errno($conn));
        }
        if (mysqli_affected_rows($conn) > 0) {
            $lastId = mysqli_insert_id($conn);
            return $lastId > 0 ? $lastId : true;
        }
        else {
            return false;
        }
    }

    /**
     * 从数据库里抓一行记录
     * 
     * @return Array|boolean
     */
    public function fetchRow ($sql) {
        $conn = $this->getConn('slaver');
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row;
        }

        throw new DbException(mysqli_error($conn), mysqli_errno($conn));
    }
    
    /**
     * 从数据库里抓多行记录
     * 
     * @return Array|boolean 
     */     
    public function fetchAll($sql){
        $conn = $this->getConn('slaver');
        $result = mysqli_query($conn, $sql);
        if ($result) {
            if(mysqli_num_rows($result) > 0 ){
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result))
                {
                    $output[] = $row;
                }
                mysqli_free_result($result);
                return $output;
            }
            return [];
        }

        throw new DbException(mysqli_error($conn), mysqli_errno($conn));

    }
    

    public function getTotal($table, $filter = null){
        
        $return = 0;
        $sql = 'select count(*) as num from `'.$table.'` ';
        if ($filter) {
            $sql .= 'WHERE ' . $filter . ' ';
        }
        if ($row = $this -> fetchRow($sql)) $return = $row["num"];

        return $return;
    }
        
    public function getConn($masterOrSlaver){
        // 若存在
        if ($this -> _conn[$masterOrSlaver]) {
            return $this -> _conn[$masterOrSlaver];
        }
        
        // 若存在数据库配置信息
        if (isset($this -> _dbConf[$masterOrSlaver])) {
            return $this -> connect($masterOrSlaver);
        }
        
        // 如果是slave 尝试连接master
        if ($masterOrSlaver === 'slaver') {
            return $this -> getConn('master');
        }
        return null;
    }

    public function beginTransaction () {
        $link = $this->getConn('master');
        return mysqli_begin_transaction($link);
    }

    public function commit () {
        $link = $this->getConn('master');
        return mysqli_commit($link);
    }

    public function rollback () {
        $link = $this->getConn('master');
        return mysqli_rollback($link);
    }

    public function __distruct() {
        if ($this->_conn["master"]) {
            mysqli_close($this->_conn["master"]);
        }

        if ($this->_conn["slaver"]) {
            mysqli_close($this->_conn["slaver"]);
        }
    }
}