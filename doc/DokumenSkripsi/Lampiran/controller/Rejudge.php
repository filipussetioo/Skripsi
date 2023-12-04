<?php
/**
 * SharIF Judge online judge
 * @file Rejudge.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers; 
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\QueueModel;
use App\Models\User;

class Rejudge extends BaseController
{

	protected $problems;
	protected $assignment_model;
	protected $user;
	protected $validation;
	protected $queue_model;

	public function __construct()
	{
		$this->assignment_model = new AssignmentModel();
		$this->user = new User();
		$this->validation = \Config\Services::validation();
		$this->problems = $this->assignment_model->all_problems($this->user->selected_assignment['id']);
	}


	// ------------------------------------------------------------------------


	public function index()
	{

		$this->validation->setRule('problem_id', 'problem id', 'required|integer');

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'problems' => $this->problems,
			'msg' => array(),
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'selected' => 'assignments'
		);

		if ($this->validation->withRequest($this->request)->run())
		{
			$problem_id = $this->request->getPost('problem_id');
			$this->queue_model = new QueueModel();
			$this->queue_model->rejudge($this->user->selected_assignment['id'], $problem_id);
			process_the_queue();
			$data['msg'] = array('Rejudge in progress');
		}

		return view('pages/admin/rejudge', $data);
	}


	// ------------------------------------------------------------------------


	public function rejudge_single()
	{
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->validation->setRule('submit_id', 'submit id', 'required|integer');
		$this->validation->setRule('username', 'username', 'required|alpha_numeric');
		$this->validation->setRule('assignment', 'assignment', 'required|integer');
		$this->validation->setRule('problem', 'problem', 'required|integer');

		if ($this->validation->withRequest($this->request)->run())
		{
			$this->queue_model = new QueueModel();
			$this->queue_model->rejudge_single(
				array(
					'submit_id' => $this->request->getPost('submit_id'),
					'username' => $this->request->getPost('username'),
					'assignment' => $this->request->getPost('assignment'),
					'problem' => $this->request->getPost('problem'),
				)
			);
			process_the_queue();
			$json_result = array('done' => 1);
		}
		else
			$json_result = array('done' => 0, 'message' => 'Input Error');

		$this->response->setHeader('Content-Type: application/json;', 'charset=utf-8');
		echo json_encode($json_result);
	}

}