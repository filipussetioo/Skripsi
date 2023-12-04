<?php
/**
 * SharIF Judge online judge
 * @file Moss.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SettingsModel;
use App\Models\SubmitModel;
use App\Models\User;

class Moss extends BaseController
{

	protected $validation;
	protected $assignment_model;
	protected $settings_model;
	protected $user;
	protected $submit_model;

	public function __construct()
	{
		$this->validation = \Config\Services::validation();
		$this->assignment_model = new AssignmentModel();
		$this->settings_model = new SettingsModel();
		$this->user = new User();
	}


	// ------------------------------------------------------------------------


	public function index($assignment_id = FALSE)
	{
		if ($assignment_id === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$this->validation->setRule('detect', 'detect', 'required');
		if ($this->validation->withRequest($this->request)->run())
		{
			if ($this->request->getPost('detect') !== 'detect')
				exit;
			$this->_detect($assignment_id);
		}
		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'moss_userid' => $this->settings_model->get_setting('moss_userid'),
			'moss_assignment' => $this->assignment_model->assignment_info($assignment_id),
			'update_time' => $this->assignment_model->get_moss_time($assignment_id),
			'selected' => 'assignments',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		$data['moss_problems'] = array();
		$assignments_path = rtrim($this->settings_model->get_setting('assignments_root'), '/');
		for($i=1; $i<=$data['moss_assignment']['problems']; $i++){
			$data['moss_problems'][$i] = NULL;
			$path = $assignments_path."/assignment_{$assignment_id}/p{$i}/moss_link.txt";
			if (file_exists($path))
				$data['moss_problems'][$i] = file_get_contents($path);
		}

		return view('pages/admin/moss', $data);
	}


	// ------------------------------------------------------------------------


	public function update($assignment_id = FALSE)
	{
		if ($assignment_id === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$userid = $this->request->getPost('moss_userid');
		$this->settings_model->set_setting('moss_userid', $userid);
		$moss_original = trim( file_get_contents(rtrim($this->settings_model->get_setting('tester_path'), '/').'/moss_original') );
		$moss_path = rtrim($this->settings_model->get_setting('tester_path'), '/').'/moss';
		file_put_contents($moss_path, str_replace('MOSS_USER_ID', $userid, $moss_original));
		shell_exec("chmod +x {$moss_path}");
		return $this->index($assignment_id);
	}


	// ------------------------------------------------------------------------


	private function _detect($assignment_id = FALSE)
	{
		if ($assignment_id === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$this->submit_model = new SubmitModel();
		$assignments_path = rtrim($this->settings_model->get_setting('assignments_root'), '/');
		$tester_path = rtrim($this->settings_model->get_setting('tester_path'), '/');
		shell_exec("chmod +x {$tester_path}/moss");
		$items = $this->submit_model->get_final_submissions($assignment_id, $this->user->level, $this->user->username);
		$groups = array();
		foreach ($items as $item) {
			if (!isset($groups[$item['problem']]))
				$groups[$item['problem']] = array($item);
			else
				array_push($groups[$item['problem']], $item);
		}
		foreach ($groups as $problem_id => $group) {
			$list = '';
			$assignment_path = $assignments_path."/assignment_{$assignment_id}";
			foreach ($group as $item)
				if ($item['file_type'] !== 'zip' && $item['file_type'] !== 'pdf')
					$list .= "p{$problem_id}/{$item['username']}/{$item['file_name']}".'.'.filetype_to_extension($item['file_type']). " ";
			shell_exec("list='$list'; cd $assignment_path; $tester_path/moss \$list | grep http >p{$problem_id}/moss_link.txt;");
		}
		$this->assignment_model->set_moss_time($assignment_id);
	}


}