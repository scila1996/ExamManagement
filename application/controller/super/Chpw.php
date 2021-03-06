<?php

namespace App\Controller\Super;

require_once $_SERVER['DOCUMENT_ROOT'] . '/application/controller/super/Super.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/System.php';

use Controller\Super\Super;
use System\Core\Misc;

class Chpw extends Super
{
	protected function on_post()
	{
		$action = $this->request_post('btn-action');
		if ($action)
		{
			switch ($action)
			{
				case 'change':
				{
					$this->DML->update_ADMIN(
						$this->request_post('o_user'),
						$this->request_post('o_pass'),
						$this->request_post('n_user'),
						$this->request_post('n_pass')
					);
					break;
				}
			}
			Misc::redirect();
		}
	}
	protected function main()
	{
		$this->menu['chpw']['active'] = 'active';
		$this->load_view('application/view/super/chpw.php', array(
			'msg' => Misc::get_msg()
		));
	}
}

?>