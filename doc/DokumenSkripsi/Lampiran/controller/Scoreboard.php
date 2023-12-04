<?php
/**
 * SharIF Judge online judge
 * @file Scoreboard.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers; 
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\ScoreboardModel;
use App\Models\User;

class Scoreboard extends BaseController
{
	protected $scoreboard_model;
	protected $assignment_model;
	protected $user;

	public function __construct()
	{
		$this->scoreboard_model = new ScoreboardModel();
		$this->assignment_model = new AssignmentModel();
		$this->user = new User();
	}

	public function index()
	{
		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'scoreboard' => $this->scoreboard_model->get_scoreboard($this->user->selected_assignment['id']),
			'user' => $this->user,
			'selected' => 'scoreboard',
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		return view('pages/scoreboard', $data);
	}


}