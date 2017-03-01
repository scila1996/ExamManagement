<?php

namespace App\System\Library;

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/System.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/libraries/Pagination.php';

use App\System\System;
use App\System\Library\Pagination;

class Table
{
	protected $class = 'table table-striped table-hover'; // Use Bootstrap table
	protected $arr_title = array(); // for custom title
	protected $sql_query = NULL;
	
	private $_page = 1;
	private $_page_size = 10;
	private $_use_db = TRUE;
	private $_total = 0;
	
	public function __construct($USE_DATABASE = TRUE)
	{
		if (!$USE_DATABASE)
		{
			$this->_use_db = FALSE;
		}
		if (($p = System::input_get('page')) and is_numeric($p))
		{
			$this->_page = intval($p);
		}
		if (($s = System::input_get('psize')) and is_numeric($s))
		{
			$this->_page_size = intval($s);
		}
	}
	protected function row($data, $index) // custom your row item
	{
		$html = '<tr>';
		foreach ($data as $k => $v)
		{
			$html .= "<td> $v </td>";
		}
		$html .= '</tr>';
		return $html;
	}
	protected function no_Data()
	{
		return '<tr class="info"><td colspan="25"><b> Không có dữ liệu </b></td></tr>';
	}
	final public function page_Size($size)
	{
		$this->_page_size = $size;
	}
	final public function get()
	{
		$head = '';
		$body = '';
		if ($this->arr_title)
		{
			foreach ($this->arr_title as $str)
			{
				$head .= "<th> $str </th>";
			}
		}
		if ($this->_use_db)
		{
			try
			{
				$n_start = ($this->_page - 1) * $this->_page_size;
				if ($this->_page_size > 0)
				{
					$this->sql_query->limit($this->_page_size, $n_start);
				}
				$result = $this->sql_query->execute();
				$this->_total = $result->num_rows();
				$page_max = 1;
				if ($this->_page_size <= 0)
				{
					$this->_page_size = $this->_total;
				}
				$page_max = ceil($this->_total / $this->_page_size);
				if ($this->_total)
				{
					if ($n_start >= 0 and $n_start < $this->_total)
					{
						// fetch columns title if not set
						if (!$this->arr_title)
						{
							foreach ($result->get_field() as $title)
							{
								$head .= "<th> $title </th>";
							}
						}
						// fetch rows
						$i = $n_start;
						foreach ($result->get_data() as $row)
						{
							$body .= $this->row($row, ++$i);
						}
					}
					else
					{
						throw new \Exception("Page number '$_GET[page]' is invalid !", 2);
					}
				}
				else
				{
					$body .= $this->no_Data();
				}
			}
			catch (\Exception $ex)
			{
				return System::get_exception_msg($ex);
			}
		}
		// Return html
		$html = <<<EOF
		<table class="$this->class">
			<thead>
				$head
			</thead>
			<tbody>
				$body
			</tbody>
		</table>
EOF;
		$pager = new Pagination($this->_total, $this->_page_size, $this->_page);
		try
		{
			$html .= '<div class="text-left">';
			$html .= $pager->get();
			$html .= '</div>';
		}
		catch (\Exception $e)
		{
			$html .= System::get_exception_msg($e);
		}
		return $html;
	}
}

?>