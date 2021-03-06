<?php

namespace App\Model\Admin;

require_once $_SERVER['DOCUMENT_ROOT'] . '/application/model/admin/table/Question_Table.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/application/model/admin/paper/View_Question.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/System.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/libraries/View.php';

use Model\Admin\Question_Table;
use Model\Admin\View_Question;
use System\Core\Misc;
use System\Library\View;

class Model_Question
{
	private $user_id = NULL;
	private $category_id = NULL;
	private $exam_id = NULL;
	public function __construct($u_id, $c_id, $e_id)
	{
		$this->user_id = $u_id;
		$this->category_id = $c_id;
		$this->exam_id = $e_id;
	}
	public function view($option = FALSE)
	{
		$view = new View_Question($this->user_id, $this->category_id, $this->exam_id);
		$data = array(
			'title' => 'Xem các câu hỏi',
			'content' => $view->get(FALSE)
		);
		if ($option)
		{
			$data['ans_btn'] = <<<EOF
			<div class="form-group">
				<a href="javascript:void(0)" id="view-answer" class="btn btn-primary btn-xs" data-toggle="popover" data-content="Bấm vào đây để xem đáp án" data-click="show"><span class="glyphicon glyphicon-comment"></span>&nbsp;Xem đáp án </a>
			</div>
EOF;
		}
		return new View('application/view/admin/question/content.php', $data);
	}
	private function _add_button()
	{
		$url = array(
			'link' => '?' . http_build_query(array_merge($_GET, array('action' => 'add', 'type' => 'link'))),
			'mchoice' => '?' . http_build_query(array_merge($_GET, array('action' => 'add', 'type' => 'multiple-choice'))),
			'fill' => '?' . http_build_query(array_merge($_GET, array('action' => 'add', 'type' => 'fill'))),
		);
		$btn = <<<EOF
		<div class="form-group">
			<div class="dropdown">
				<button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" style="color: #fff"> Thêm câu hỏi mới &nbsp;<span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="$url[link]" class="add-choice"> Ghép nối </a></li>
					<li><a href="$url[mchoice]" class="add-link"> Chọn đáp án </a></li>
					<li><a href="$url[fill]" class="add-fill"> Điền khuyết </a></li>
				</ul>
			</div>
		</div>
EOF;
		return $btn;
	}
	public function manage()
	{
		$table = new Question_Table($this->user_id, $this->category_id, $this->exam_id);
		$data = array(
			'title' => 'Quản lý các câu hỏi',
			'add' => $this->_add_button(),
			'content' => $table->get(),
			'msg' => Misc::get_msg()
		);
		return new View('application/view/admin/question/content.php', $data);
	}
	public function add($type)
	{
		$form = '';
		switch ($type)
		{
			case 'link':
			{
				$data = array(
					'title' => 'Thêm câu ghép nối',
					'action' => 'add-question', 'type' => 'link'
				);
				$form = new View('application/view/admin/question/add_form/link.php', $data);
				$form = $form->get();
				break;
			}
			case 'multiple-choice':
			{
				$data = array(
					'title' => 'Thêm câu chọn đáp án',
					'action' => 'add-question', 'type' => 'multiple-choice'
				);
				$form = new View('application/view/admin/question/add_form/multiple_choice.php', $data);
				$form = $form->get();
				break;
			}
			case 'fill':
			{
				$data = array(
					'title' => 'Thêm câu điền khuyết',
					'action' => 'add-question', 'type' => 'fill'
				);
				$form = new View('application/view/admin/question/add_form/fill.php', $data);
				$form = $form->get();
				break;
			}
		} 
		$data = array(
			'title' => 'Thêm câu hỏi mới',
			'content' => $form
		);
		return new View('application/view/admin/question/content.php', $data);
	}
}

?>