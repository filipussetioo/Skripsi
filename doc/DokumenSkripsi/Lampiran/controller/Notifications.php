<?php
/**
 * SharIF Judge online judge
 * @file Notifications.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\NotificationsModel;
use App\Models\User;

class Notifications extends BaseController
{

	private $notif_edit;
	protected $session;
	protected $validation;
	protected $notifications_model;
	protected $assignment_model;
	protected $user;

	// ------------------------------------------------------------------------


	public function __construct()
	{
		$this->session = session();
		$this->validation = \Config\Services::validation();
		$this->notifications_model = new NotificationsModel();
		$this->assignment_model = new AssignmentModel();
		$this->notif_edit = FALSE;
		$this->user = new User;
	}


	// ------------------------------------------------------------------------


	public function index()
	{
		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'notifications' => $this->notifications_model->get_all_notifications(),
			'user' => $this->user,
			'selected' => 'notifications',
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		return view('pages/notifications', $data);

	}


	// ------------------------------------------------------------------------


	public function add()
	{
		if ( $this->user->level <=1){ // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->validation->setRule('title', 'title', 'trim');
		$this->validation->setRule('text', 'text', 'trim'); /* todo: xss clean */

		if ($this->request->is('post')){
			if($this->validation->withRequest($this->request)->run()){
				if ($this->request->getPost('id') === NULL)
					$this->notifications_model->add_notification($this->request->getPost('title'), $this->request->getPost('text'));
				else
					$this->notifications_model->update_notification($this->request->getPost('id'), $this->request->getPost('title'), $this->request->getPost('text'));
				return redirect('notifications');
			}
		}

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'notif_edit' => $this->notif_edit,
			'user' => $this->user,
			'selected' => 'notifications',
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		if ($this->notif_edit !== FALSE){
			$data['title'] = 'Edit Notification';
		}

		return view('pages/admin/add_notification',$data);
	}


	// ------------------------------------------------------------------------


	public function edit($notif_id = FALSE)
	{
		if ($this->user->level <= 1) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ($notif_id === FALSE || ! is_numeric($notif_id))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->notif_edit = $this->notifications_model->get_notification($notif_id);

		return $this->add();
	}


	// ------------------------------------------------------------------------


	public function delete()
	{
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ($this->user->level <= 1) // permission denied
			$json_result = array('done' => 0, 'message' => 'Permission Denied');
		elseif ($this->request->getPost('id') === NULL)
			$json_result = array('done' => 0, 'message' => 'Input Error');
		else
		{
			$this->notifications_model->delete_notification($this->request->getPost('id'));
			$json_result = array('done' => 1);
		}

		$this->response->setContentType('application/json', 'charset=utf-8');
		echo json_encode($json_result);
	}


	// ------------------------------------------------------------------------


	public function check()
	{
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$time  = $this->request->getPost('time');
		if ($time === NULL)
			exit('error');
		if ($this->notifications_model->have_new_notification(strtotime("$time")))
			exit('new_notification');
		exit('no_new_notification');
	}

}