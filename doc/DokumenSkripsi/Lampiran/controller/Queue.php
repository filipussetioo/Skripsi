<?php
/**
 * SharIF Judge online judge
 * @file Queue.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\QueueModel;
use App\Models\SettingsModel;
use App\Models\User;

class Queue extends BaseController
{
	protected $queue_model;
	protected $settings_model;
	protected $assignment_model;
	protected $submit_model;
	protected $user;

	public function __construct()
	{
		$this->queue_model = new QueueModel();
		$this->settings_model = new SettingsModel();
		$this->assignment_model = new AssignmentModel();
		$this->user = new User();
	}


	// ------------------------------------------------------------------------


	public function index()
	{

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'queue' => $this->queue_model->get_queue(),
			'working' => $this->settings_model->get_setting('queue_is_working'),
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'selected' => ''
		);

		return view('pages/admin/queue', $data);
	}


	// ------------------------------------------------------------------------


	public function pause()
	{
		if ( ! $this->request->isAJAX() ){
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}else{
			$this->settings_model->set_setting('queue_is_working','0');
			echo 'success';
		}
	}


	// ------------------------------------------------------------------------


	public function resume()
	{
		if ( ! $this->request->isAJAX() ){
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}else{
			process_the_queue();
			echo 'success';
		}
	}


	// ------------------------------------------------------------------------


	public function empty_queue()
	{
		if ( ! $this->request->isAJAX() ){
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}else{
			$this->queue_model->empty_queue();
			echo 'success';
		}
	}
}