<?php

namespace Application\Controller\Admin;

use System\Core\Controller;
use System\Libraries\View;
use System\Libraries\Auth;
use System\Libraries\Exception\Auth_NotValidate;
use System\Libraries\Request;
use System\Database\DB;
use Application\Model\Misc;

class Login extends Controller
{
	public function index()
	{
		Auth::set_Key('admin');
		Auth::redirect_Success(Request::current_path() . '/admin/category');
		Auth::validate();
		if (Request::post('submit'))
		{
			Auth::set_Function(function() {
				$query = DB::query()->select()->from('user')->where(array(
					'user' => Request::post('user'),
					'pass = SHA1(?)' => [Request::post('pass')],
					'course_id' => Request::post('course')
				));
				$result = $query->execute();
				if ($result->num_rows())
				{
					return $result->current();
				}
				return FALSE;
			});
			try
			{
				Auth::attempt();
				Auth::validate();
			}
			catch (Auth_NotValidate $e)
			{
				Misc::put_msg('danger', $e->getMessage(), FALSE);
			}
		}
		View::add('admin/login.php', array(
			'course_data' => DB::get('course'),
			'msg' => Misc::get_msg()
		));
	}
}
