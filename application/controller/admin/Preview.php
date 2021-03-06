<?php

namespace App\Controller\Admin;

require_once $_SERVER['DOCUMENT_ROOT'] . '/application/controller/admin/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/application/model/admin/paper/View_Question.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/System.php';

use Model\Admin\View_Question;
use System\Core\Misc;

class Preview extends Admin
{
	protected $view = NULL;
	protected function on_get()
	{
		if ($this->request_get('ajax'))
		{
			$this->view = new View_Question(
				$this->request_get('uid'),
				$this->request_get('category_id'),
				$this->request_get('exam_id')
			);
			$this->view->option['show_question_checkbox'] = TRUE;
			$this->load_view('application/view/admin/exam/modal_select_question.php', array(
				'data' => $this->view->get()->html()
			));
		}
		else
		{
			$this->load_view('application/view/admin/template/header.php',  array(
				'title' => 'Xem đề thi - PDF'
			));
			if ($this->request_get('view_answer'))
			{
				$content = '';
				foreach ($this->request_get('eid') as $eid)
				{
					$this->view = new View_Question(
						$this->user->id,
						$this->request_get('category_id'),
						$eid
					);
					$this->view->option['return_only_answer'] = TRUE;
					$content .= $this->view->get()->html();
				}
				$this->load_view('application/view/admin/question/preview.php', array(
					'content' => $content
				));
			}
			else
			{
				$this->view = new View_Question(
					$this->user->id,
					$this->request_get('category_id'),
					$this->request_get('exam_id')
				);
				$this->load_view('application/view/admin/question/preview.php', array(
					'content' => $this->view->get(TRUE)->html()
				));
			}
			$this->load_view('application/view/admin/template/footer.php');
		}
	}
	protected function main()
	{
		// no thing
	}
	protected function output($html)
	{
		echo $html;
	}
}
