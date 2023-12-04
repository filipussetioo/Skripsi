<?php
/**
 * SharIF Judge online judge
 * @file Submit_model.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Models;
use CodeIgniter\Model;

class SubmitModel extends Model {

	protected $settings_model;

	public function __construct()
	{
		parent::__construct();
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns table row for a specific submission
	 */
	public function get_submission($username, $assignment, $problem, $submit_id)
	{
		$query = $this->db->table('submissions')->getWhere(
			array(
				'username'=>$username,
				'assignment'=>$assignment,
				'problem'=>$problem,
				'submit_id'=>$submit_id
			)
		);
		if($query->getNumRows()!=1){
			return FALSE;
		}else{
			return $query->getRowArray();
		}
	}


	// ------------------------------------------------------------------------


	public function get_final_submissions($assignment_id, $user_level, $username, $page_number = NULL, $filter_user = NULL, $filter_problem = NULL)
	{
		$this->settings_model = new SettingsModel();
		$arr['assignment'] = $assignment_id;
		$arr['submit_id !='] = EDITOR_SUBMIT_ID;
		$arr['is_final'] = 1;
		if ($user_level === 0)// students can only get final submissions of themselves
			$arr['username']=$username;
		elseif ($filter_user !== NULL)
			$arr['username'] = $filter_user;
		if ($filter_problem !== NULL)
			$arr['problem'] = $filter_problem;
		if ($page_number === NULL)
			return $this->db->table('submissions')->orderBy('username asc, problem asc')->getWhere($arr)->getResultArray();
		else
		{
			$per_page = $this->settings_model->get_setting('results_per_page_final');
			if ($per_page == 0)
				return $this->db->table('submissions')->orderBy('username asc, problem asc')->getWhere($arr)->getResultArray();
			else
				return $this->db->table('submissions')->orderBy('username asc, problem asc')->limit($per_page,($page_number-1)*$per_page)->getWhere($arr)->getResultArray();
		}

	}


	// ------------------------------------------------------------------------


	public function get_all_submissions($assignment_id, $user_level, $username, $page_number = NULL, $filter_user = NULL, $filter_problem = NULL)
	{
		$this->settings_model = new SettingsModel();
		$arr['assignment']=$assignment_id;
		$arr['submit_id !='] = EDITOR_SUBMIT_ID;
		if ($user_level === 0)
			$arr['username']=$username;
		elseif ($filter_user !== NULL)
			$arr['username'] = $filter_user;
		if ($filter_problem !== NULL)
			$arr['problem'] = $filter_problem;
		if ($page_number === NULL)
			return $this->db->table('submissions')->orderBy('submit_id','desc')->getWhere($arr)->getResultArray();
		else
		{
			$per_page = $this->settings_model->get_setting('results_per_page_all');
			if ($per_page == 0)
				return $this->db->table('submissions')->orderBy('submit_id','desc')->getWhere($arr)->getResultArray();
			else
				return $this->db->table('submissions')->orderBy('submit_id','desc')->limit($per_page,($page_number-1)*$per_page)->getWhere($arr)->getResultArray();
		}
	}


	// ------------------------------------------------------------------------


	public function count_final_submissions($assignment_id, $user_level, $username, $filter_user = NULL, $filter_problem = NULL)
	{
		$arr['assignment'] = $assignment_id;
		$arr['submit_id !='] = EDITOR_SUBMIT_ID;
		$arr['is_final'] = 1;
		if ($user_level === 0)
			$arr['username']=$username;
		elseif ($filter_user !== NULL)
			$arr['username'] = $filter_user;
		if ($filter_problem !== NULL)
			$arr['problem'] = $filter_problem;
		return $this->db->table('submissions')->where($arr)->countAllResults();
	}


	// ------------------------------------------------------------------------


	public function count_all_submissions($assignment_id, $user_level, $username, $filter_user = NULL, $filter_problem = NULL)
	{
		$arr['assignment']=$assignment_id;
		$arr['submit_id !='] = EDITOR_SUBMIT_ID;
		if ($user_level === 0)
			$arr['username']=$username;
		elseif ($filter_user !== NULL)
			$arr['username'] = $filter_user;
		if ($filter_problem !== NULL)
			$arr['problem'] = $filter_problem;
		return $this->db->table('submissions')->where($arr)->countAllResults();
	}


	// ------------------------------------------------------------------------


	public function set_final_submission($username, $assignment, $problem, $submit_id)
	{

		$this->db->table('submissions')->where(array(
			'is_final' => 1,
			'username' => $username,
			'assignment' => $assignment,
			'problem' => $problem,
		))->update(array('is_final'=>0));

		$this->db->table('submissions')->where(array(
			'username' => $username,
			'assignment' => $assignment,
			'problem' => $problem,
			'submit_id' => $submit_id,
		))->update(array('is_final'=>1));

		return TRUE;
	}


	// ------------------------------------------------------------------------


	/**
	 * add the result of an "upload only" submit to the database
	 */
	public function add_upload_only($submit_info)
	{

		$this->db->table('submissions')->where(array(
			'is_final' => 1,
			'username' => $submit_info['username'],
			'assignment' => $submit_info['assignment'],
			'problem' => $submit_info['problem'],
		))->update(array('is_final'=>0));

		$submit_info['is_final'] = 1;
		$submit_info['status'] = 'Uploaded';

		$this->db->table('submissions')->insert($submit_info);

	}


}
