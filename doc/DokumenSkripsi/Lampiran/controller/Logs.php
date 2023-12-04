<?php
/**
 * SharIF Judge online judge
 * @file Logs.php
 * @author Stillmen Vallian <stillmen.v@gmail.com>
 */
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\LogsModel;
use App\Models\User;

class Logs extends BaseController
{
	protected $session;
	protected $user;
	protected $logs_model;
	protected $assignment_model;

	public function __construct()
	{
		$this->session = session();
		$this->logs_model = new LogsModel();
		$this->assignment_model = new AssignmentModel();
		$this->user = new User();
		if ( $this->user->level <= 2) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
	}




	// ------------------------------------------------------------------------




	public function index()
	{

		$data = array(
			'logs' => $this->logs_model->get_all_logs(),
			'selected' => 'logs',
			'user' => $this->user,
			'all_assignments' => $this->assignment_model->all_assignments(),
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		return view('pages/admin/logs', $data);
	}




	// ------------------------------------------------------------------------

}
