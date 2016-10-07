<?php

namespace Arsenals\Core\Abstracts;

use Arsenals\Core\Config;
use Arsenals\Core\Database\SessionFactory;
use Arsenals\Core\Exceptions\QueryException;

if (!defined('APP_NAME')) {
    exit('Access Denied!');
}
/**
 * 抽象模型.
 *
 * @author 管宜尧<mylxsw@126.com>
 */
abstract class Model extends Arsenals
{
    protected $_table_name = null;
    protected $_table_prefix;
    protected $_conn = null;

    /**
     * @var 最后一次分页查询到的总记录数量
     */
    protected $page_record_counts = 0;
    /**
     * 最后一次查询的总页数.
     *
     * @var number
     */
    protected $page_counts = 1;

    public function __construct()
    {
        // 初始化当前模型对应的数据表名称
        if ($this->_table_name == null) {
            $class_name = get_called_class();
            $config = Config::load('database');
            $this->_table_prefix = strtolower($config['global']['prefix']);
            $this->_table_name = $this->_table_prefix
                    .substr(strtolower($class_name), strrpos($class_name, '\\') + 1);
        }
        $this->_conn = SessionFactory::getSession();
        parent::__construct();
    }

    /**
     * 获取数据库连接对象
     *
     * 要获取真是对象需要调用getConn()->getRealConnection()
     */
    public function getConn()
    {
        return $this->_conn;
    }

    /**
     * 载入单个数据对象
     *
     * @param mixed  $conditions 条件
     * @param string $table
     *
     * @return object|null
     */
    public function load($conditions = [], $table = null)
    {
        if (!is_array($conditions)) {
            $conditions = ['id' => $conditions];
        }
        $table = is_null($table) ? $this->_table_name : $this->getTableName($table);
        $sql = 'SELECT * FROM '.$table;
        $args = [];
        if (count($conditions) > 0) {
            $sql .= ' WHERE ';
            $conditions_result = $this->_init_conditions($conditions);
            $sql .= $conditions_result[0];
            $args = $conditions_result[1];
        }
        $result = $this->_conn->query($sql, $args);
        if (count($result) > 0) {
            return $result[0];
        }
    }

    /**
     * 删除元素.
     *
     * @param mixed  $conditions 条件
     * @param string $table
     *
     * @return int
     */
    public function delete($conditions, $table = null)
    {
        if (is_array($conditions) && count($conditions) > 0) {
            $table = is_null($table) ? $this->_table_name : $this->getTableName($table);
            $sql = 'DELETE FROM `'.$table.'` WHERE ';
            $conditions_result = $this->_init_conditions($conditions);
            $sql .= $conditions_result[0];
            $args = $conditions_result[1];
            $this->_conn->query($sql, $args, true);
        } else {
            throw new \Arsenals\Core\Exceptions\QueryException('Perform the delete operation must specify the query criteria!');
        }
    }

    /**
     * 查出指定表中的数据记录列表（支持分页）.
     *
     * 查出指定表中的数据记录，支持分页，通过指定不同的查询条件，实现查询不同
     * 结果集。
     *
     * @param array $conditions 查询条件数组
     * @param bool|int 是否分页或者是分页的当前页码
     * @param int 每页显示的记录数量
     * @param string $table
     *
     * @return array
     */
    public function find($conditions = [], $order = '',
        $index = false, $per = 15, $table = null)
    {
        $table = is_null($table) ? $this->_table_name : $this->getTableName($table);

        $args = [];
        $sql = " FROM {$table} ";

        if (count($conditions) > 0) {
            $sql .= ' WHERE ';
            $conditions_result = $this->_init_conditions($conditions);
            $sql .= $conditions_result[0];
            $args = $conditions_result[1];
        }

        if ($order != '') {
            $sql .= ' ORDER BY '.$order;
        }

        // $index 为FALSE，则不分页，直接查询所有数据
        if ($index === false) {
            return $this->_conn->query("SELECT * {$sql}", $args);
        }

        // 分页查询
        return $this->select("SELECT * {$sql}", $args, $index, $per);
    }

    /**
     * 更新单个数据对象
     *
     * @param array  $data
     * @param string $table
     *
     * @return int
     */
    public function update($data, $conditions, $table = null)
    {
        $table = is_null($table) ? $this->_table_name : $this->getTableName($table);
        $sql = "UPDATE `{$table}` SET ";

        foreach ($data as $k => $v) {
            $sql .= "{$k}='".$this->escape($v)."',";
        }

        $sql = trim($sql, ',');
        $sql .= ' WHERE '.$this->_init_conditions_no_prepare($conditions);

        $this->_conn->query($sql, null, true);

// 		$table_datas = array_merge($this->_datas_, $data);
// 		$pk = $table_datas[$this->getPk()];
// 		unset($table_datas[$this->getPk()]);
// 		// 执行字段校验等
// 		$this->_create($table_datas);

// 		$sql = 'UPDATE `' . $this->get_table_name() . '` SET ' ;
// 		$args = array();
// 		foreach ($this->get_table_fields() as $field=>$v) {
// 			if(array_key_exists($field, $table_datas)){
// 				$sql .= "{$field} = ? , ";
// 				array_push($args, $table_datas[$field]);
// 			}
// 		}
// 		$sql = trim(trim($sql), ',') . ' WHERE ' . $this->getPk() . '= ? ';
// 		array_push($args, $pk);
// 		return $this->_conn->query($sql, $args);
    }

    /**
     * 保存数据.
     *
     * @param array  $data  要保存的数据（key-value对应field-value）
     * @param string $table 要操作的表名，默认是当前表
     *
     * @return int 插入数据的ID
     */
    public function save($data = [], $table = null)
    {
        // $table_datas = array_merge($this->_datas_, $data);

        // 执行字段校验等
        //$this->_create($table_datas);
        $table = is_null($table) ? $this->_table_name : $this->getTableName($table);

        $sql = 'INSERT INTO `'.$table.'` (';
        $args = [];
        foreach ($data as $field => $v) {
            $sql .= "{$field}, ";
            array_push($args, $v);
        }
        $sql = trim(trim($sql), ',').') VALUES('
            .implode(array_fill(0, count($args), '?'), ',').')';

        $this->_conn->query($sql, $args, true);

        return $this->getLastInsertId();
    }

    /**
     * SELECT 查询（支持分页）.
     *
     * @param sring    $sql   要执行的sql
     * @param array    $args  参数
     * @param bool|int $index 为FALSE则不分页，数字进行分页
     * @param int      $per   每页数量
     *
     * @return array
     */
    public function select($sql, $args = [], $index = false, $per = 15)
    {
        if ($index === false) {
            return $this->_conn->query($sql, $args);
        }
        $sql = trim($sql);
        $index = intval($index);
        $per = intval($per);

        // 分页查询
        // 首先查询出总记录数量
        $count_res = $this->_conn->query('SELECT COUNT(*) AS C '.substr($sql, strpos(strtoupper($sql), ' FROM ')), $args);
        $this->page_record_counts = $count_res[0]['C'];
        // 总页数
        $this->page_counts = $this->page_record_counts / $per + 1;
        // 判断当前页码是否正确
        if ($index <= 0 || $index > $this->page_counts) {
            $index = 1;
        }
        $sql .= ' LIMIT '.($index - 1) * $per.', '.$per;

        return $this->_conn->query($sql, $args);
    }

    /**
     * 查询出表中所有数据.
     *
     * 如果要查询出所有的数据，则$limit为null即可
     *
     * @param number $limit
     * @param string $table
     *
     * @throws QueryException
     */
    public function lists($limit = null, $table = null)
    {
        $table = is_null($table) ? $this->_table_name : $this->getTableName($table);

        $sql = "SELECT * FROM `{$table}` ";
        // 添加查询记录数量限制
        if (!\is_null($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->_conn->query($sql);
    }

    /**
     * 执行sql语句.
     *
     * @param string $sql  要执行的sql语句
     * @param array  $args 预处理的变量
     *
     * @return array | int
     */
    public function query($sql, $args = [], $insert = false)
    {
        return $this->_conn->query($sql, $args, $insert);
    }

    /**
     * 初始化查询条件数组为字符串，无prepare.
     */
    protected function _init_conditions_no_prepare($conditions)
    {
        $res = $this->_init_conditions($conditions, false);

        return $res[0];
    }

    /**
     * 初始化查询条件数组为字符串.
     *
     * @param array $conditions 查询条件数组
     * @param array $is_prepare 是否使用prepare
     *
     * @return string
     */
    protected function _init_conditions($conditions, $is_prepare = true)
    {
        $sql = '';
        $args = [];
        $join_method = 'AND';
        if (array_key_exists('_OR', $conditions)) {
            if ($conditions['_OR'] === true) {
                $join_method = 'OR';
            }
            unset($conditions['_OR']);
        }


        foreach ($conditions as $c_k => $c_v) {
            $c_cmd_pos = strstr($c_k, '%');
            $val = $is_prepare ? '?' : "'{$this->escape($c_v)}'";

            if ($c_cmd_pos === false) {
                $sql .= "{$c_k} = {$val} ";
                $is_prepare && array_push($args, $c_v);
            } else {
                $c_cmd = preg_split('/%/', $c_k, 2);
                switch ($c_cmd[1]) {
                    case 'LIKE':
                        $sql .= "{$c_cmd[0]} LIKE {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'EQ':
                        $sql .= "{$c_cmd[0]} = {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'NEQ':
                        $sql .= "{$c_cmd[0]} <> {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'GT':
                        $sql .= "{$c_cmd[0]} > {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'GET':
                        $sql .= "{$c_cmd[0]} >= {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'LT':
                        $sql .= "{$c_cmd[0]} < {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'LET':
                        $sql .= "{$c_cmd[0]} <= {$val} ";
                        $is_prepare && array_push($args, $c_v);
                        break;
                    case 'IS_NULL':
                        $sql .= "{$c_cmd[0]} IS NULL ";
                        break;
                    case 'IS_NOT_NULL':
                        $sql .= "{$c_cmd[0]} IS NOT NULL ";
                        break;
                    default:
                }
            }
            $sql .= " {$join_method} ";
        }

        return [rtrim(trim($sql), $join_method), $args];
    }

    /**
     * 设置模型属性.
     *
     * @param string $name  字段名
     * @param mixed  $value 字段值
     *
     * @return object
     */
    public function __set($name, $value)
    {
        // 		if(str_start_with($name, '_')){
// 			$this->{$name} = $value;
// 			return $this;
// 		}
// 		if(array_key_exists($name, $this->get_table_fields())){
// 			$this->_datas_[$name] = $value;
// 			return $this;
// 		}else{
// 			show_error("表{$this->_table_name_}中不存在字段{$name}!");
// 		}
    }

    /**
     * 实现查询方法.
     *
     * 实现了通过调用方法by_, eq_等类似方法+字段名从表中执行查询
     * 方法含有如下：by_ eq_ like_ neq_ gt_ gte_ lt_ lte_
     *
     * @param string name 调用的方法名
     * @param array $arguments 参数数组
     *
     * @return object 返回的为query对象，如果要获取值需要调用row(_array), result(_array)
     */
    public function __call($name, $arguments)
    {
        // 		$name_array = preg_split('/_/', $name, 2);
// 		if(count($name_array) != 2 ||
// 		!array_key_exists($name_array[1], $this->get_table_fields())){
// 			show_error("调用的方法{$name}不存在!");
// 		}

// 		$sql = 'SELECT * FROM `' . $this->get_table_name() . '` WHERE ';
// 		switch ($name_array[0]) {
// 			case 'by':
// 			case 'eq':
// 				$sql .= $name_array[1] . ' = ? ';
// 				break;
// 			case 'like':
// 				$sql .= $name_array[1] . ' LIKE ? ';
// 				break;
// 			case 'gt':
// 				$sql .= $name_array[1] . ' > ? ';
// 				break;
// 			case 'lt':
// 				$sql .= $name_array[1] . ' < ? ';
// 				break;
// 			case 'gte':
// 				$sql .= $name_array[1] . ' >= ? ';
// 				break;
// 			case 'lte':
// 				$sql .= $name_array[1] . ' <= ? ';
// 				break;
// 			case 'neq':
// 				$sql .= $name_array[1] . ' <> ? ';
// 				break;
// 			default:
// 				show_error("调用的方法{$name}不存在!");
// 		}

// 		return $this->_conn->query($sql, $arguments);
    }

    /**
     * 字符串转义，安全处理sql值
     *
     * @param string $str
     *
     * @return string
     */
    protected function escape($str)
    {
        return $this->_conn->escape($str);
    }

    /**
     * 获取表名.
     *
     * @param string $tablename 需要获取的表名，不带前缀!
     *
     * @return string
     */
    protected function getTableName($tablename = null)
    {
        if (is_null($tablename)) {
            return $this->_table_name;
        }

        return $this->_table_prefix.$tablename;
    }

    /**
     * 最后一次分页查询的总记录数量.
     *
     * @return number
     */
    protected function getPageRecordCounts()
    {
        return $this->page_record_counts;
    }

    /**
     * 最后一次查询的总页数.
     *
     * @return number
     */
    protected function getPageCounts()
    {
        return $this->page_counts;
    }

    /**
     * 最后一次执行插入操作的ID.
     */
    public function getLastInsertId()
    {
        return $this->_conn->lastInsertId();
    }

    /**
     * 没有记录异常.
     */
    protected function noRecoredException($message)
    {
        throw new \Arsenals\Core\Exceptions\NoRecoredException($message);
    }

    /**
     * 查询异常.
     */
    protected function queryException($message)
    {
        throw new \Arsenals\Core\Exceptions\QueryException($message);
    }

    /**
     * 自动启用事务支持
     * 注意： 该方法有待测试！！！！
     */
    protected function transaction($callback)
    {
        $this->_conn->beginTrans();
        try {
            $args = func_get_args();
            array_shift($args);
            call_user_func_array($callback, $args);
            $this->_conn->commit();
        } catch (\Exception $e) {
            $this->_conn->rollback();
            throw new $e();
        }
    }
}
