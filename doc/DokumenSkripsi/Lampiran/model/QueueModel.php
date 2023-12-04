<?php
/**
 * SharIF Judge online judge
 * @file Queue_model.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Models;
use CodeIgniter\Model;

class QueueModel extends Model
{
	protected $assignment_model;
	protected $scoreboard_model;

	public function __construct()
	{
		parent::__construct();
		$this->assignment_model = new AssignmentModel();
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns TRUE if one submission with $username, $assignment and $problem
	 * is already in queue (for preventing multiple submission)
	 */
	public function in_queue ($username, $assignment, $problem)
	{
		$query = $this->db->table('queue')->getWhere(array('username'=>$username, 'assignment'=>$assignment, 'problem'=>$problem));
		return ($query->getNumRows() > 0);
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns all the submission queue
	 */
	public function get_queue ()
	{
		return $this->db->table('queue')->get()->getResultArray();
	}


	// ------------------------------------------------------------------------


	/**
	 * Empties the queue
	 */
	public function empty_queue ()
	{
		return $this->db->table('queue')->emptyTable();

		//Delete all dummy submission
		$this->db->table('submissions')->delete(array(
			'submit_id' => 0,
		));
	}


	// ------------------------------------------------------------------------


	public function add_to_queue($submit_info)
	{

		$submit_info['is_final'] = 0;
		$submit_info['status'] = 'PENDING';

		$this->db->table('submissions')->insert($submit_info);

		$this->db->table('queue')->insert(array(
			'submit_id' => $submit_info['submit_id'],
			'username' => $submit_info['username'],
			'assignment' => $submit_info['assignment'],
			'problem' => $submit_info['problem'],
			'type' => 'judge'
		));
	}


	// ------------------------------------------------------------------------


	/**
	 * Adds submissions of a problem to queue for rejudge
	 */
	public function rejudge($assignment_id, $problem_id)
	{
		$problem = $this->assignment_model->problem_info($assignment_id, $problem_id);
		if ($problem['is_upload_only'])
			return;

		// Changing the status of all submissions of selected problem to PENDING

		$this->db->table('submissions')->where(
			array(
				'assignment' => $assignment_id,
				'problem' => $problem_id
			)
		)->update(array('pre_score' => 0, 'status' => 'PENDING'));

		// Adding submissions to queue:

		$submissions = $this->db->table('submissions')
			->select('submit_id, username, assignment, problem')
			->orderBy('submit_id')
			->getWhere(array('assignment'=>$assignment_id, 'problem'=>$problem_id))
			->getResultArray();

		foreach($submissions as $submission)
		{
			$this->db->table('queue')->insert(
				array(
					'submit_id' => $submission['submit_id'],
					'username' => $submission['username'],
					'assignment' => $submission['assignment'],
					'problem' => $submission['problem'],
					'type' => 'rejudge'
				)
			);
		}
		// Now ready for rejudge
	}


	// ------------------------------------------------------------------------


	/**
	 * Adds a single submission to queue for rejudge
	 */
	public function rejudge_single($submission)
	{
		$problem = $this->assignment_model->problem_info($submission['assignment'], $submission['problem']);
		if ($problem['is_upload_only'])
			return;

		// Changing the status of submission to PENDING
		$this->db->table('submissions')->where(array(
			'submit_id' => $submission['submit_id'],
			'username' => $submission['username'],
			'assignment' => $submission['assignment'],
			'problem' => $submission['problem']
		))->update(array('pre_score'=>0, 'status'=>'PENDING'));

		// Adding Submission to Queue
		$this->db->table('queue')->insert(array(
			'submit_id' => $submission['submit_id'],
			'username' => $submission['username'],
			'assignment' => $submission['assignment'],
			'problem' => $submission['problem'],
			'type' => 'rejudge'
		));
		// Now ready for rejudge
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns the first item of the queue
	 */
	public function get_first_item()
	{
		$query = $this->db->table('queue')->orderBy('id')->limit(1)->get();
		if ($query->getNumRows() != 1){
			return NULL;
		}else{
			return $query->getRowArray();
		}
	}


	// ------------------------------------------------------------------------


	/**
	 * Removes an item from the queue
	 */
	public function remove_item($username, $assignment, $problem, $submit_id)
	{
		$this->db->table('queue')->delete(array(
			'submit_id' => $submit_id,
			'username' => $username,
			'assignment' => $assignment,
			'problem' => $problem
		));

		//Delete dummy submission if exec only
		if($submit_id == 0){
			$this->db->table('submissions')->delete(array(
				'submit_id' => $submit_id,
				'username' => $username,
				'assignment' => $assignment,
				'problem' => $problem
			));
		}
	}


	// ------------------------------------------------------------------------


	/**
	 * Saves the result of judge in database
	 * This function is called from Queueprocess controller
	 */
	public function save_judge_result_in_db ($submission, $type)
	{

		$arr = array(
			'status' => $submission['status'],
			'pre_score' => $submission['pre_score'],
		);

		if ($type === 'judge')
		{
			$this->db->table('submissions')->where(array(
				'is_final' => 1,
				'username' => $submission['username'],
				'assignment' => $submission['assignment'],
				'problem' => $submission['problem'],
			))->update(array('is_final'=>0));
			$arr['is_final'] = 1;
		}

		$this->db->table('submissions')->where(array(
			'submit_id' => $submission['submit_id'],
			'username' => $submission['username'],
			'assignment' => $submission['assignment'],
			'problem' => $submission['problem']
		))->update($arr);

		// update scoreboard:
		$this->scoreboard_model = new ScoreboardModel();
		$this->scoreboard_model->update_scoreboard($submission['assignment']);
	}


	// ------------------------------------------------------------------------

	/**
	 * Adds a dummy submission to queue for execution only
	 */
	public function add_to_queue_exec($submit_info)
	{
		$query = $this->db->table('submissions')->getWhere(array(
			'submit_id' => $submit_info['submit_id'], 
			'username' => $submit_info['username'],
			'assignment' => $submit_info['assignment'],
			'problem' => $submit_info['problem']
		));
		if ($query->getNumRows() == 0){
			$submit_info['is_final'] = 0;
			$submit_info['status'] = 'PENDING';
			$this->db->table('submissions')->insert($submit_info);

			$this->db->table('queue')->insert(array(
				'submit_id' => $submit_info['submit_id'],
				'username' => $submit_info['username'],
				'assignment' => $submit_info['assignment'],
				'problem' => $submit_info['problem'],
				'type' => 'exec'
			));
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
}