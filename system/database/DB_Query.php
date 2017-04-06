<?php

namespace System\Database;

class DB_Query
{
	private $_query = 'SELECT @@VERSION as version'; // default query
	private $_join = array();
	private $_where = array();
	private $_order_by = array();
	private $_group_by = array();
	private $_set = array();
	private $_limit = -1;
	private $_offset = 0;
	private $_param = array();
	public function __construct($query_str = '', $parameter = array())
	{
		if ($query_str) $this->_query = $query_str;
		if ($parameter) $this->_param = $parameter;
	}
	private function _get_condition($data)
	{
		$get = function($column, $operator, $param){
			$ret = '1';
			$operator = strtoupper($operator);
			switch ($operator)
			{
			case 'BETWEEN':
				$ret = "$column BETWEEN ? AND ?";
				break;
			case 'IN':
				$in = implode(', ', array_fill(0, count($param), '?'));
				$ret = "$column IN ($in)";
				break;
			default:
				$ret = "$column $operator ?";
			}
			return $ret;
		};
		$list = array();
		switch (func_num_args())
		{
		case 1:
			foreach (func_get_arg(0) as $k => $w)
			{
				$c = $k;
				$o = '=';
				$p = $w;
				if (is_array($w))
				{
					$c = $w[0];
					switch (count($w))
					{
					case 2:
						$p = $w[1];
						break;
					case 3:
						$o = $w[1];
						$p = $w[2];
						break;
					}
				}
				array_push($list, array($c, $o, $p));
			}
			break;
		case 2:
			array_push($list, array(func_get_arg(0), '=', func_get_arg(1)));
			break;
		case 3:
			array_push($list, array(func_get_arg(0), func_get_arg(1), func_get_arg(2)));
			break;
		}
		$where_return = array('expression' => array(), 'param' => array());
		foreach ($list as $where)
		{
			array_push($where_return['expression'], $get($where[0], $where[1], $where[2]));
			if (is_array($where[2]))
			{
				$where_return['param'] = array_merge($where_return['param'], $where[2]);
			}
			else
			{
				array_push($where_return['param'], $where[2]);
			}
		}
		return $where_return;
	}
	private function _where($condition, $argument)
	{
		$where_get = call_user_func_array(array($this, '_get_condition'), $argument);
		foreach ($where_get['expression'] as $w)
		{
			$k = empty($this->_where) ? '' : "$condition ";
			array_push($this->_where, "{$k}{$w}");
		}
		$this->_param = array_merge($this->_param, $where_get['param']);
		return $this;
	}
	private function _join($type, $argument)
	{
		$type = strtoupper($type);
		$table = $argument[0];
		$alias = '';
		$condition = $argument[1];
		switch (count($argument))
		{
		case 3:
			$alias = $argument[1];
			$condition = $argument[2];
		}
		$table = $this->_table_alias($table, $alias);
		array_push($this->_join, "$type JOIN $table ON $condition");
		return $this;
	}
	private function _table_alias($table, $alias)
	{
		return $alias ? "$table AS $alias" : $table;
	}
	public function select($columns = '*')
	{
		$c = is_array($columns) ? implode(', ', $columns) : $columns;
		$this->_query = "SELECT $c ";
		return $this;
	}
	public function from($table, $alias = '')
	{
		$this->_query .= "FROM {$this->_table_alias($table, $alias)} ";
		return $this;
	}
	public function where($data)
	{
		return $this->_where('AND', func_get_args());
	}
	public function whereIsNull($column)
	{
		return $this->_where('AND', array($column, 'IS', NULL));
	}
	public function whereNotNull($column)
	{
		return $this->_where('AND', array($column, 'IS NOT', NULL));
	}
	public function or_where($data)
	{
		return $this->_where('OR', func_get_args());
	}
	public function or_whereIsNull($column)
	{
		return $this->_where('OR', array($column, 'IS', NULL));
	}
	public function or_whereNotNull($column)
	{
		return $this->_where('OR', array($column, 'IS NOT', NULL));
	}
	public function order_by($column, $sort = 'DESC')
	{
		if (is_string($column))
		{
			$column = array($column => $sort);
		}
		$this->_order_by = array_merge($this->_order_by, $column);
		return $this;
	}
	public function group_by($column)
	{
		if (is_string($column))
		{
			$column = array($column);
		}
		$this->_group_by = array_merge($this->_group_by, $column);
		return $this;
	}
	public function insert($table, array $data)
	{
		$cols = implode(', ', array_keys($data));
		$vals = implode(', ', array_fill(0, count($data), '?'));
		$this->_query = "INSERT INTO $table ($cols) VALUES ($vals) ";
		$this->_param = array_values($data);
		return $this;
	}
	public function update($table, $alias = '')
	{
		$this->_query = "UPDATE {$this->_table_alias($table, $alias)} ";
		return $this;
	}
	public function delete($table = '')
	{
		if ($table)
		{
			$this->_query = "DELETE $table ";
		}
		else
		{
			$this->_query = 'DELETE ';
		}
		return $this;
	}
	public function set(array $data)
	{
		$this->_set = array_map(function($column){
			return "$column = ?";
		}, array_keys($data));
		$this->_param = array_values($data);
		return $this;
	}
	/** @return self */
	public function join()
	{
		return call_user_func_array(array($this, 'innerJoin'), func_get_args());
	}
	public function innerJoin($table)
	{
		return $this->_join('INNER', func_get_args());
	}
	public function leftJoin($table)
	{
		return $this->_join('INNER', func_get_args());
	}
	public function rightJoin($table)
	{
		return $this->_join('INNER', func_get_args());
	}
	public function limit($n, $offset = 0)
	{
		$this->_limit = $n;
		return $this->offset($offset);
	}
	public function offset($n)
	{
		$this->_offset = $n;
		return $this;
	}
	public function get_Query()
	{
		$query = $this->_query;
		if (!empty($this->_join))
		{
			$join = implode(' ', $this->_join);
			$query .= "$join ";
		}
		if (!empty($this->_set))
		{
			$set = implode(', ', $this->_set);
			$query .= "SET $set ";
		}
		if (!empty($this->_where))
		{
			$where = implode(' ', $this->_where);
			$query .= "WHERE $where ";
		}
		if (!empty($this->_group_by))
		{
			$group = implode(', ', $this->_group_by);
			$query .= "GROUP BY $group ";
		}
		if (!empty($this->_order_by))
		{
			$order = implode(', ', array_map(function($column, $sort){
				if (in_array(strtoupper($sort), array('ASC', 'DESC')))
				{
					return "$column $sort";
				}
				return $sort;
			}, array_keys($this->_order_by), $this->_order_by));
			$query .= "ORDER BY $order ";
		}
		if ($this->_limit > 0 and $this->_offset >= 0)
		{
			switch (DB::$db_driver)
			{
			case 'mysql':
				$query = preg_replace('/(^\s*SELECT(?=\s))/i', '${1} SQL_CALC_FOUND_ROWS', $query);
			default:
				$query .= "LIMIT $this->_limit OFFSET $this->_offset ";
			}
		}
		return $query;
	}
	public function get_Param()
	{
		return $this->_param;
	}
	public function execute()
	{
		return DB::get_connect()->query($this->get_Query(), $this->get_Param());
	}
}

?>