<?php
/**
 * SharIF Judge online judge
 * @file User_model.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Models;
use CodeIgniter\Model;
use Config\Database;

class User extends Model
{
	public $username;
	public $selected_assignment;
	public $level;
	public $email;
	protected $session;
	protected $db;

	public function __construct()
	{	
		parent::__construct();
		$this->session = session();
		$this->db = Database::connect();
		$this->username = $this->session->get('username');
		if ($this->username === NULL)
			return;

		$user = $this->db->table('users')
			->select('selected_assignment, role, email')
			->where(['username' => $this->username])
			->get()->getRow();

		$this->email = $user->email;
		
		$query = $this->db->table('assignments')->getWhere(['id' => $user->selected_assignment]);
		if ($query->getNumRows() != 1)
			$this->selected_assignment = array(
				'id' => 0,
				'name' => 'Not Selected',
				'finish_time' => '0',
				'extra_time' => '0',
				'problems' => 0
			);
		else
			$this->selected_assignment = $query->getRowArray();

		switch ($user->role)
		{
			case 'admin': $this->level = 3; break;
			case 'head_instructor': $this->level = 2; break;
			case 'instructor': $this->level = 1; break;
			case 'student': $this->level = 0; break;
		}
	}


	// ------------------------------------------------------------------------


	/**
	 * Select Assignment
	 *
	 * Sets selected assignment for $username
	 *
	 * @param $assignment_id
	 */
	public function select_assignment($assignment_id)
	{
		$this->db->table('users')->where('username',$this->username)->update(['selected_assignment'=>$assignment_id]);
	}


	// ------------------------------------------------------------------------


	/**
	 * Save Widget Positions
	 *
	 * Updates position of dashboard widgets in database
	 *
	 * @param $positions
	 */
	public function save_widget_positions($positions)
	{
		$this->db->table('users')
			->where('username', $this->username)
			->update(['dashboard_widget_positions'=>$positions]);
	}


	// ------------------------------------------------------------------------


	/**
	 * Get Widget Positions
	 *
	 * Returns positions of dashboard widgets from database
	 *
	 * @param none
	 * @return mixed
	 */
	public function get_widget_positions()
	{
		$tab = $this->db->table('users')
		->select('dashboard_widget_positions')
		->getWhere(['username' => $this->username])
		->getRow()
		->dashboard_widget_positions;

		return json_decode(
			$tab,
			TRUE
		);
	}

}