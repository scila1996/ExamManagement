<?php

namespace App\System\Database;

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/System.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/database/Sql_Result.php';

use App\System\System;

class Mysql extends \mysqli
{
	private $stmt_affected_rows = NULL;
	public function __construct()
	{
		$db = System::get_config('db');
		parent::__construct(
			$db['host'],
			$db['user'],
			$db['password'],
			$db['db'],
			$db['port']
		);
		if ($this->connect_error)
		{
			$code = $this->connect_errno;
			$err = $this->connect_error;
			throw new \Exception("#$code : $err", $code);
		}
		if ($db['charset'])
		{
			$this->set_charset($db['charset']);
		}
		if ($db['collation'])
		{
			parent::query("SET collation_connection = $db[collation]");
		}

	}
	private function _stmt_get_type($value)
	{
		if ($value === NULL) return 's';
		$type = array('i', 'd', 's');
		$get_type = substr(gettype($value), 0, 1);
		$key = array_search($get_type, $type);
		return $key === FALSE ? FALSE : $get_type;
	}
	private function _array_copy($arr)
	{
		$r = array();
		foreach ($arr as $k => $v)
		{
			$r[$k] = $v;
		}
		return $r;
	}
	private function _get_num_rows()
	{
		$result = parent::query('SELECT FOUND_ROWS() AS total');
		if (!$result) return 0;
		$num = $result->fetch_object()->total;
		$result->free();
		return $num;
	}
	private function _get_result_from_stmt($stmt)
	{
		$arr_field = array();
		$arr_ret = array();
		$arr_bind_result = array();
		if ($field_result = $stmt->result_metadata())
		{
			$stmt->store_result();
			$i = 0;
			while (($field = $field_result->fetch_field()))
			{
				$pvar = 'p' . $i;
				$$pvar = NULL;
				array_push($arr_field, $field->name);
				$arr_bind_result[$field->name] = &$$pvar;
				$i+=1;
			}
			call_user_func_array(array($stmt, 'bind_result'), $arr_bind_result);
			while ($stmt->fetch())
			{
				array_push($arr_ret, (object)($this->_array_copy($arr_bind_result)));
			}
			$total = ($total = $this->_get_num_rows()) ? $total : $stmt->num_rows;
			$stmt->free_result();
			return new Sql_Result($arr_field, $arr_ret, $total);
		}
		else
		{
			return FALSE; // no result
		}
	}
	public function raw_query($query_str)
	{
		$result = parent::query($query_str);
		if ($result)
		{
			return $result;
		}
		throw new \Exception($this->error, $this->errno);
	}
	public function begin()
	{
		return $this->raw_query('BEGIN');
	}
	public function query($query_str, $param = NULL)
	{
		$result = NULL;
		$query = $this->real_escape_string($query_str);
		$statement = $this->prepare($query);
		if (!$statement)
		{
			throw new \Exception("Invalid query statement !", 2);
		}
		if (($n = $statement->param_count))
		{
			if (count($param) < $n)
			{
				throw new \Exception("This statement expect $n paramters !", 2);
			}
			$i = 1;
			$stmt_param = array_fill(0, $n + 1, 0);
			$stmt_param[0] = '';
			foreach ($param as $key => $value)
			{
				$type = $this->_stmt_get_type($value);
				if (!$type)
				{
					throw new \Exception("Invalid given value $i for statement !", 2);
				}
				$stmt_param[0] .= $type;
				$stmt_param[$i] = &$param[$key];
				$i++;
			}
			call_user_func_array(array($statement, 'bind_param'), $stmt_param);
		}
		if ($statement->execute())
		{
			$result = $this->_get_result_from_stmt($statement);
			if (!($result instanceof Sql_Result))
			{
				$this->stmt_affected_rows = $statement->affected_rows;
				$result = TRUE;
			}
			$statement->close();
			return $result;
		}
		else
		{
			$code = $statement->errno;
			$err = $statement->error;
			throw new \Exception("#$code : $err", $code);
		}
	}
	public function get_affected_rows()
	{
		return $this->stmt_affected_rows;
	} 
}

?>