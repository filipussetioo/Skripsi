<?php
/**
 * SharIF Judge online judge
 * @file Halloffame.php
 * @author Stillmen Vallian <stillmen.v@gmail.com>
 */
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\HofModel;
use App\Models\User;

class Halloffame extends BaseController
{
	protected $hof_model;
	protected $user;
	protected $assignment_model;

	public function __construct()
	{
		$this->hof_model = new HofModel();
		$this->user = new User();
		$this->assignment_model = new AssignmentModel();
	}




	// ------------------------------------------------------------------------




	public function index()
	{
		$data = array(
			'hofs' => $this->hof_model->get_all_final_submission(),
			'user' => $this->user,
			'selected' => 'halloffame',
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'all_assignments' => $this->assignment_model->all_assignments(),
		);
		return view ('pages/halloffame', $data);
	}




	// ------------------------------------------------------------------------



	/**
	 * Controller for shows the details of the score
	 * Called by ajax request
	 */
	public function hof_details()
	{
		if ( ! $this->request->isAjax() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();	
		$username = $this->request->getVar('username');

		$json_result = $this->hof_model->get_all_user_assignments($username);

		$this->response->setHeader('Content-Type','application/json; charset=utf-8');
		echo json_encode($json_result);
	}
}
