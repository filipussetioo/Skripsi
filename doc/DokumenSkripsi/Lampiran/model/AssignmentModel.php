<?php
/**
 * SharIF Judge online judge
 * @file Assignment_model.php
 * @author: Filipus Setio Nugroho <filipussetio@gmail.com>
 */
namespace App\Models;

use App\Libraries\Parsedown;
use CodeIgniter\Model;
use App\Models\SettingsModel;
use App\Models\ScoreboardModel;

class AssignmentModel extends Model
{
	protected $settings_model;
	protected $scoreboard_model;
	protected $request;
	protected $parsedown;

	public function __construct()
	{
		parent::__construct();
		$this->settings_model = new SettingsModel();
		$this->request = \Config\Services::request(); 
	}

	/**
	 * Add New Assignment to DB / Edit Existing Assignment
	 *
	 * @param $id
	 * @param bool $edit
	 * @return bool
	 */
	public function add_assignment($id, $edit = FALSE)
	{	
		$this->scoreboard_model = new ScoreboardModel();
		// Start Database Transaction
		$this->db->transStart();

		$extra_items = explode('*', $this->request->getPost('extra_time'));
		$extra_time = 1;
		foreach($extra_items as $extra_item)
		{
			$extra_time *= $extra_item;
		}

		$archived_assignment = $archived_assignment = $this->request->getPost('archived_assignment')!==NULL ? 1 : 0;

		$assignment = array(
			'id' => $id,
			'name' => $this->request->getPost('assignment_name'),
			'problems' => $this->request->getPost('number_of_problems'),
			'total_submits' => 0,
			'open' => ($this->request->getPost('open')===NULL?0:1),
			'scoreboard' => ($this->request->getPost('scoreboard')===NULL?0:1),
			'javaexceptions' => ($this->request->getPost('javaexceptions')===NULL?0:1),
			'description' => '', /* todo */
			'start_time' => date('Y-m-d H:i:s', strtotime($this->request->getPost('start_time'))),
			'finish_time' => date('Y-m-d H:i:s', strtotime($this->request->getPost('finish_time'))),
			'extra_time' => $extra_time*60,
			'late_rule' => $this->request->getPost('late_rule'),
			'participants' => $this->request->getPost('participants'),
			'archived_assignment' => $archived_assignment
		);
		if($edit)
		{
			$before = $this->db->table('assignments')->getWhere(['id'=>$id])->getRowArray();
			unset($assignment['total_submits']);
			$this->db->table('assignments')->where('id', $id)->update($assignment);
			// each time we edit an assignment, we should update coefficient of all submissions of that assignment
			if ($assignment['extra_time']!=$before['extra_time'] OR $assignment['start_time']!=$before['start_time'] OR $assignment['finish_time']!=$before['finish_time'] OR $assignment['late_rule']!=$before['late_rule'])
				$this->_update_coefficients($id, $assignment['extra_time'], $assignment['finish_time'], $assignment['late_rule']);
		}
		else
			$this->db->table('assignments')->insert($assignment);

		/* **** Adding problems to "problems" table **** */

		//First remove all previous problems
		$this->db->table('problems')->delete(['assignment'=>$id]);

		//Now add new problems:
		$names = $this->request->getPost('name');
		$scores = $this->request->getPost('score');
		$c_tl = $this->request->getPost('c_time_limit');
		$py_tl = $this->request->getPost('python_time_limit');
		$java_tl = $this->request->getPost('java_time_limit');
		$ml = $this->request->getPost('memory_limit');
		$ft = $this->request->getPost('languages');
		$dc = $this->request->getPost('diff_cmd');
		$da = $this->request->getPost('diff_arg');
		$uo = $this->request->getPost('is_upload_only');
		if ($uo === NULL)
			$uo = array();
		for ($i=1; $i<=$this->request->getPost('number_of_problems'); $i++)
		{
			$items = explode(',', $ft[$i-1]);
			$ft[$i-1] = '';
			foreach ($items as $item){
				$item = trim($item);
				$item2 = strtolower($item);
				$item = ucfirst($item2);
				if ($item2 === 'python2')
					$item = 'Python 2';
				elseif ($item2 === 'python3')
					$item = 'Python 3';
				elseif ($item2 === 'pdf')
					$item = 'PDF';
				$item2 = strtolower($item);
				if ( ! in_array($item2, array('c','c++','python 2','python 3','java','zip','pdf','txt')))
					continue;
				// If the problem is not Upload-Only, its language should be one of {C,C++,Python 2, Python 3,Java}
				if ( ! in_array($i, $uo) && ! in_array($item2, array('c','c++','python 2','python 3','java')) )
					continue;
				$ft[$i-1] .= $item.",";
			}
			$ft[$i-1] = substr($ft[$i-1],0,strlen($ft[$i-1])-1); // remove last ','
			$problem = array(
				'assignment' => $id,
				'id' => $i,
				'name' => $names[$i-1],
				'score' => $scores[$i-1],
				'is_upload_only' => in_array($i,$uo)?1:0,
				'c_time_limit' => $c_tl[$i-1],
				'python_time_limit' => $py_tl[$i-1],
				'java_time_limit' => $java_tl[$i-1],
				'memory_limit' => $ml[$i-1],
				'allowed_languages' => $ft[$i-1],
				'diff_cmd' => $dc[$i-1],
				'diff_arg' => $da[$i-1],
			);
			$this->db->table('problems')->insert($problem);
		}

		if ($edit)
		{
			// We must update scoreboard of the assignment
			$this->scoreboard_model->update_scoreboard($id);
		}

		// Complete Database Transaction
		$this->db->transComplete();

		return $this->db->transStatus();
	}



	// ------------------------------------------------------------------------



	/**
	 * Delete An Assignment
	 *
	 * @param $assignment_id
	 */
	public function delete_assignment($assignment_id)
	{
		$this->db->transStart();

		// Phase 1: Delete this assignment and its submissions from database
		$this->db->table('assignments')->delete(['id'=>$assignment_id]);
		$this->db->table('problems')->delete(['assignment'=>$assignment_id]);
		$this->db->table('submissions')->delete(['assignment'=>$assignment_id]);

		$this->db->transComplete();

		if ($this->db->transStatus())
		{
			// Phase 2: Delete assignment's folder (all test cases and submitted codes)
			$cmd = 'rm -rf '.rtrim($this->settings_model->get_setting('assignments_root'), '/').'/assignment_'.$assignment_id;
			shell_exec($cmd);
		}
	}



	// ------------------------------------------------------------------------



	/**
	 * All Assignments
	 *
	 * Returns a list of all assignments and their information
	 *
	 * @return mixed
	 */
	public function all_assignments()
	{
		$result = $this->db->table('assignments')->orderBy('id')->get()->getResultArray();
		$assignments = [];
		foreach ($result as $item)
		{
			$assignments[$item['id']] = $item;
		}
		return $assignments;
	}



	// ------------------------------------------------------------------------



	/**
	 * New Assignment ID
	 *
	 * Finds the smallest integer that can be uses as id for a new assignment
	 *
	 * @return int
	 */
	public function new_assignment_id()
	{
		$max = ($this->db->table('assignments')->selectMax('id', 'max_id')->get()->getRow()->max_id) + 1;

		$assignments_root = rtrim($this->settings_model->get_setting('assignments_root'), '/');
		while (file_exists($assignments_root.'/assignment_'.$max)){
			$max++;
		}

		return $max;
	}



	// ------------------------------------------------------------------------



	/**
	 * All Problems of an Assignment
	 *
	 * Returns an array containing all problems of given assignment
	 *
	 * @param $assignment_id
	 * @return mixed
	 */
	public function all_problems($assignment_id)
	{
		$result = $this->db->table('problems')->orderBy('id')->getWhere(['assignment'=>$assignment_id])->getResultArray();
		$problems = array();
		foreach ($result as $row)
			$problems[$row['id']] = $row;
		return $problems;
	}



	// ------------------------------------------------------------------------



	/**
	 * Problem Info
	 *
	 * Returns database row for given problem (from given assignment)
	 *
	 * @param $assignment_id
	 * @param $problem_id
	 * @return mixed
	 */
	public function problem_info($assignment_id, $problem_id)
	{
		return $this->db->table('problems')->getWhere(['assignment'=>$assignment_id, 'id'=>$problem_id])->getRowArray();
	}



	// ------------------------------------------------------------------------



	/**
	 * Assignment Info
	 *
	 * Returns database row for given assignment
	 *
	 * @param $assignment_id
	 * @return array
	 */
	public function assignment_info($assignment_id)
	{
		$query = $this->db->table('assignments')->getWhere(['id'=>$assignment_id]);
		if ($query->getNumRows() != 1){
			return array(
				'id' => 0,
				'name' => 'Not Selected',
				'finish_time' => 0,
				'extra_time' => 0,
				'problems' => 0
			);
		}else{
		return $query->getRowArray();
		}
	}



	// ------------------------------------------------------------------------



	/**
	 * Is Participant
	 *
	 * Returns TRUE if $username if one of the $participants
	 * Examples for participants: "ALL" or "user1, user2,user3"
	 *
	 * @param $participants
	 * @param $username
	 * @return bool
	 */
	public function is_participant($participants, $username)
	{
		$participants = explode(',', $participants);
		foreach ($participants as &$participant){
			$participant = trim($participant);
		}
		if(in_array('ALL', $participants))
			return TRUE;
		if(in_array($username, $participants))
			return TRUE;
		return FALSE;
	}



	// ------------------------------------------------------------------------



	/**
	 * Increase Total Submits
	 *
	 * Increases number of total submits for given assignment by one
	 *
	 * @param $assignment_id
	 * @return mixed
	 */
	public function increase_total_submits($assignment_id)
	{
		// Get total submits
		$total = $this->db->table('assignments')->select('total_submits')->getWhere(['id'=>$assignment_id])->getRow()->total_submits;
		// Save total+1 in DB
		$this->db->table('assignments')->where('id', $assignment_id)->update(['total_submits'=>($total+1)]);

		// Return new total
		return ($total+1);
	}



	// ------------------------------------------------------------------------



	/**
	 * Set Moss Time
	 *
	 * Updates "Moss Update Time" for given assignment
	 *
	 * @param $assignment_id
	 */
	public function set_moss_time($assignment_id)
	{
		$now = shj_now_str();
		$this->db->table('assignments')->where('id', $assignment_id)->update(['moss_update'=>$now]);
	}



	// ------------------------------------------------------------------------



	/**
	 * Get Moss Time
	 *
	 * Returns "Moss Update Time" for given assignment
	 *
	 * @param $assignment_id
	 * @return string
	 */
	public function get_moss_time($assignment_id)
	{
		$query = $this->db->table('assignments')->select('moss_update')->getWhere(['id'=>$assignment_id]);
		if($query->getNumRows() != 1) return 'Never';
		return $query->getRow()->moss_update;
	}



	// ------------------------------------------------------------------------


	/**
	 * Save Problem Description
	 *
	 * Saves (Adds/Updates) problem description (html or markdown)
	 *
	 * @param $assignment_id
	 * @param $problem_id
	 * @param $text
	 * @param $type
	 */
	public function save_problem_description($assignment_id, $problem_id, $text, $type)
	{
		$assignments_root = rtrim($this->settings_model->get_setting('assignments_root'), '/');

		if ($type === 'html')
		{
			// Remove the markdown code
			if(file_exists("$assignments_root/assignment_{$assignment_id}/p{$problem_id}/desc.md")){
				unlink("$assignments_root/assignment_{$assignment_id}/p{$problem_id}/desc.md");
			}
			// Save the html code
			file_put_contents("$assignments_root/assignment_{$assignment_id}/p{$problem_id}/desc.html", $text);
		}
		elseif ($type === 'md')
		{
			// We parse markdown using Parsedown library
			$this->parsedown = new Parsedown;
			// Save the markdown code
			file_put_contents("$assignments_root/assignment_{$assignment_id}/p{$problem_id}/desc.md", $text);
			// Convert markdown to html and save the html
			file_put_contents("$assignments_root/assignment_{$assignment_id}/p{$problem_id}/desc.html", $this->parsedown->parse($text));
		}

	}


	// ------------------------------------------------------------------------



	/**
	 * Update Coefficients
	 *
	 * Each time we edit an assignment (Update start time, finish time, extra time, or
	 * coefficients rule), we should update coefficients of all submissions of that assignment
	 *
	 * This function is called from add_assignment($id, TRUE)
	 *
	 * @param $assignment_id
	 * @param $extra_time
	 * @param $finish_time
	 * @param $new_late_rule
	 */
	private function _update_coefficients($assignment_id, $extra_time, $finish_time, $new_late_rule)
	{
		$submissions = $this->db->table('submissions')->getWhere(['assignment'=>$assignment_id])->getResultArray();

		$finish_time = strtotime($finish_time);

		foreach ($submissions as $i => $item) {
			$delay = strtotime($item['time'])-$finish_time;
			ob_start();
			if ( eval($new_late_rule) === FALSE )
				$coefficient = "error";
			if (!isset($coefficient))
				$coefficient = "error";
			ob_end_clean();
			$submissions[$i]['coefficient'] = $coefficient;
		}
		// For better performance, we update each 1000 rows in one SQL query
		$size = count($submissions);
		for ($i=0; $i<=($size-1)/1000; $i++) {
			if ($this->db->dbdriver === 'postgre')
				$query = 'UPDATE '.$this->db->prefixTable('submissions')." AS t SET coefficient = c.coeff FROM (values \n";
			else
				$query = 'UPDATE '.$this->db->prefixTable('submissions')." SET coefficient = CASE\n";

			for ($j=1000*$i; $j<1000*($i+1) && $j<$size; $j++){
				$item = $submissions[$j];
				if ($this->db->dbdriver === 'postgre'){
					$query.="($assignment_id, {$item['problem']}, '{$item['username']}', {$item['submit_id']}, '{$item['coefficient']}')";
					if ($j+1<1000*($i+1) && $j+1<$size )
						$query.=",\n";
				}
				else
					$query.="WHEN assignment='$assignment_id' AND problem='{$item['problem']}' AND username='{$item['username']}' AND submit_id='{$item['submit_id']}' THEN {$item['coefficient']}\n";
			}

			if ($this->db->dbdriver === 'postgre')
				$query.=") AS c(assignment, problem, username, submit_id, coeff)\n"
				."WHERE t.assignment=c.assignment AND t.problem=c.problem AND t.username=c.username AND t.submit_id=c.submit_id;";
			else
				$query.="ELSE coefficient \n END \n WHERE assignment='$assignment_id';";
			$this->db->query($query);
		}
	}


}
