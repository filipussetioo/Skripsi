<?php
/**
 * SharIF Judge online judge
 * @file Problems.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SettingsModel;
use App\Models\User;

class Problems extends BaseController
{

	private $all_assignments;
	protected $session;
	protected $assignment_model;
	protected $user;
	protected $settings_model;
	protected $validation;


	// ------------------------------------------------------------------------


	public function __construct()
	{
		$this->session = session();
		$this->assignment_model = new AssignmentModel();
		$this->user = new User();
		$this->settings_model = new SettingsModel();
		$this->all_assignments = $this->assignment_model->all_assignments();
		$this->validation = \Config\Services::validation();
	}


	// ------------------------------------------------------------------------


	/**
	 * Displays detail description of given problem
	 *
	 * @param int $assignment_id
	 * @param int $problem_id
	 */
	public function index($assignment_id = NULL, $problem_id = 1)
	{

		// If no assignment is given, use selected assignment
		if ($assignment_id === NULL)
			$assignment_id = $this->user->selected_assignment['id'];
		if ($assignment_id == 0)
			throw new \CodeIgniter\Exceptions\PageNotFoundException('No assignment selected');

		$assignment = $this->assignment_model->assignment_info($assignment_id);

		$data = array(
			'all_assignments' => $this->all_assignments,
			'all_problems' => $this->assignment_model->all_problems($assignment_id),
			'description_assignment' => $assignment,
			'can_submit' => TRUE,
			'selected' => 'problems',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		if ( ! is_numeric($problem_id) || $problem_id < 1 || $problem_id > $data['description_assignment']['problems'])
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$languages = explode(',',$data['all_problems'][$problem_id]['allowed_languages']);

		$assignments_root = rtrim($this->settings_model->get_setting('assignments_root'),'/');
		$problem_dir = "$assignments_root/assignment_{$assignment_id}/p{$problem_id}";
		$data['problem'] = array(
			'id' => $problem_id,
			'description' => '<p>Description not found</p>',
			'allowed_languages' => $languages,
			'has_pdf' => glob("$problem_dir/*.pdf") != FALSE
		);

		$path = "$problem_dir/desc.html";
		if (file_exists($path))
			$data['problem']['description'] = file_get_contents($path);

		if ( $assignment['id'] == 0
			OR ( $this->user->level == 0 && ! $assignment['open'] )
			OR shj_now() < strtotime($assignment['start_time'])
			OR shj_now() > strtotime($assignment['finish_time'])+$assignment['extra_time'] // deadline = finish_time + extra_time
			OR ! $this->assignment_model->is_participant($assignment['participants'], $this->user->username)
		)
			$data['can_submit'] = FALSE;

		return view('pages/problems', $data);
	}


	// ------------------------------------------------------------------------


	/**
	 * Edit problem description as html/markdown
	 *
	 * $type can be 'md', 'html', or 'plain'
	 *
	 * @param string $type
	 * @param int $assignment_id
	 * @param int $problem_id
	 */
	public function edit($type = 'md', $assignment_id = NULL, $problem_id = 1)
	{
		if ($type !== 'html' && $type !== 'md' && $type !== 'plain')
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		if ($this->user->level <= 1)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		switch($type)
		{
			case 'html':
				$ext = 'html'; break;
			case 'md':
				$ext = 'md'; break;
			case 'plain':
				$ext = 'html'; break;
		}

		if ($assignment_id === NULL)
			$assignment_id = $this->user->selected_assignment['id'];
		if ($assignment_id == 0)
			throw new \Exception('No assignment selected.');

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'description_assignment' => $this->assignment_model->assignment_info($assignment_id),
			'selected' => 'problems',
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'user' => $this->user,
		);

		if ( ! is_numeric($problem_id) || $problem_id < 1 || $problem_id > $data['description_assignment']['problems'])
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->validation->setRule('text', 'text' ,'required'); /* todo: xss clean */
		if ($this->validation->withRequest($this->request)->run())
		{
			$this->assignment_model->save_problem_description($assignment_id, $problem_id, $this->request->getPost('text'), $ext);
			return redirect()->to('problems/'.$assignment_id.'/'.$problem_id);
		}

		$data['problem'] = array(
			'id' => $problem_id,
			'description' => ''
		);

		$path = rtrim($this->settings_model->get_setting('assignments_root'),'/')."/assignment_{$assignment_id}/p{$problem_id}/desc.".$ext;
		if (file_exists($path))
			$data['problem']['description'] = file_get_contents($path);


		return view('pages/admin/edit_problem_'.$type, $data);
	}


}