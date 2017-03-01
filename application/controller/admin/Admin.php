<?php

namespace App\Controller\Admin;

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/Controller.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/System.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/Route.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/libraries/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/libraries/Navigation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/application/model/admin/DML.php';

use App\System\Controller;
use App\System\System;
use App\System\Route;
use App\System\Library\Auth;
use App\System\Library\Navigation;
use App\Model\Admin\DML;

abstract class Admin extends Controller
{
	protected $user = NULL;
	protected $DML = NULL;
	protected $nav = NULL;
	public function __construct()
	{
		Auth::set_Key('admin');
		Auth::redirect_Auth('/index.php'); // redirect to Login page if have not login
		Auth::validate();
		$this->user = Auth::get();
		$this->DML = new DML();
		$this->nav = new Navigation();
		$this->nav->add('Danh sách danh mục', Route::current_path() . '/index.php');
		$query = array();
		if ($id = $this->request_get('category_id'))
		{
			$query += array('category_id' => $id);
			$this->nav->add('Danh sách đề thi', Route::current_path() . '/exam.php?' . http_build_query($query));
			if ($id = $this->request_get('exam_id'))
			{
				$query += array('exam_id' => $id);
				$this->nav->add('Danh sách câu hỏi', Route::current_path() . '/question.php?' . http_build_query($query));
			}
		}
		parent::__construct();
	}
	protected $menu = array(
		'home' => array(
			'href' => '/admin/home.php',
			'active' => '' // default
		),
		'manage' => array(
			'href' => '/admin/index.php',
			'active' => ''
		),
		'contact' => array(
			'href' => '/admin/contact.php',
			'active' => ''
		),
		'about' => array(
			'href' => '/admin/about.php',
			'active' => ''
		),
		'account' => array(
			'href' => '/admin/account.php',
			'active' => ''
		),
		'logout' => array(
			'href' => '/admin/logout.php',
			'active' => ''
		)
	);
	protected function output($html)
	{
		$header_data = array(
			'title' => 'Ứng dụng quản lý bài kiểm tra'
		);
		$frame_data = array(
			'menu' => $this->menu,
			'nav' => $this->nav->get(),
			'content' => $html,
			'user' => $this->user
		);
		$footer_data = NULL;
		$response = $this->load_view('application/view/admin/template/header.php', $header_data, FALSE);
		$response .= $this->load_view('application/view/admin/template/frame.php', $frame_data, FALSE);
		$response .= $this->load_view('application/view/admin/template/footer.php', $footer_data, FALSE);
		echo $response;
	}
}


?>