<?php
/**
 * SharIF Judge online judge
 * @file Queueprocess.php
 * @author: Filipus Setio Nugroho <filipussetio@gmail.com>
 */

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\QueueModel;
use App\Models\SettingsModel;
use App\Models\SubmitModel;

class Queueprocess extends BaseController
{
	protected $queue_model;
	protected $submit_model;
	protected $settings_model;
	protected $assignment_model;
	protected $db;
	protected $config;

	public function __construct()
	{
		// This controller should not be called from a browser
		$this->queue_model = new QueueModel();
		$this->settings_model = new SettingsModel();
		$this->assignment_model = new AssignmentModel();
		$this->submit_model = new SubmitModel();
		$this->db = db_connect();
	}




	// ------------------------------------------------------------------------

	public function test()
	{
		$op1 = $this->settings_model->get_setting('enable_log');
		echo ($op1);
	}


	/**
	 * This is the main function for processing the queue
	 * This function judges queue items one by one
	 */
	public function run()
	{

		// Set correct base_url
		// Because we are in cli mode, base_url is not available, and we get
		// it from an environment variable that we have set in shj_helper.php
		$this->config = config('App');
		$this->config->baseURL = getenv('SHJ_BASE_URL');


		$queue_item = $this->queue_model->get_first_item();
		if ($queue_item === NULL) {
			$this->settings_model->set_setting('queue_is_working', '0');
			exit;
		}
		if ($this->settings_model->get_setting('queue_is_working')){
			exit;
		}

		$this->settings_model->set_setting('queue_is_working', '1');


		do { // loop over queue items

			$submit_id = $queue_item['submit_id'];
			$username = $queue_item['username'];
			$assignment = $queue_item['assignment'];
			$assignment_info = $this->assignment_model->assignment_info($assignment);
			$problem = $this->assignment_model->problem_info($assignment, $queue_item['problem']);
			$type = $queue_item['type'];  // $type can be 'judge', 'rejudge', or 'exec'

			$submission = $this->submit_model->get_submission($username, $assignment, $problem['id'], $submit_id);

			$file_type = $submission['file_type'];
			$file_extension = filetype_to_extension($file_type);
			$raw_filename = $submission['file_name'];
			$main_filename = $submission['main_file_name'];

			$assignments_dir = rtrim($this->settings_model->get_setting('assignments_root'), '/');
			$tester_path = rtrim($this->settings_model->get_setting('tester_path'), '/');
			$problemdir = $assignments_dir."/assignment_$assignment/p".$problem['id'];
			$userdir = "$problemdir/$username";
			//$the_file = "$userdir/$raw_filename.$file_extension";

			$op1 = $this->settings_model->get_setting('enable_log');
			$op2 = $this->settings_model->get_setting('enable_easysandbox');
			$op3 = 0;
			if ($file_type === 'c')
				$op3 = $this->settings_model->get_setting('enable_c_shield');
			elseif ($file_type === 'cpp')
				$op3 = $this->settings_model->get_setting('enable_cpp_shield');
			$op4 = 0;
			if ($file_type === 'py2')
				$op4 = $this->settings_model->get_setting('enable_py2_shield');
			elseif ($file_type === 'py3')
				$op4 = $this->settings_model->get_setting('enable_py3_shield');
			$op5 = $this->settings_model->get_setting('enable_java_policy');
			$op6 = $assignment_info['javaexceptions'];
			if($type === 'exec') {
				$exec_only = TRUE;
				$op7 = 1;
			}
			else {
				$op7 = 0;
			}
				
			if ($file_type === 'c' OR $file_type === 'cpp')
				$time_limit = $problem['c_time_limit']/1000;
			elseif ($file_type === 'java')
				$time_limit = $problem['java_time_limit']/1000;
			elseif ($file_extension === 'py')
				$time_limit = $problem['python_time_limit']/1000;
			$time_limit = round($time_limit, 3);
			$time_limit_int = floor($time_limit) + 1;

			$memory_limit = $problem['memory_limit'];
			$diff_cmd = $problem['diff_cmd'];
			$diff_arg = $problem['diff_arg'];
			$output_size_limit = $this->settings_model->get_setting('output_size_limit') * 1024;

			$cmd = "cd $tester_path;\n./tester.sh $problemdir ".escapeshellarg($username).' '.escapeshellarg($main_filename).' '.escapeshellarg($raw_filename)." $file_type $time_limit $time_limit_int $memory_limit $output_size_limit $diff_cmd $diff_arg $op1 $op2 $op3 $op4 $op5 $op6 $op7";

			file_put_contents($userdir.'/log', $cmd);

			///////////////////////////////////////
			// Running tester (judging the code) //
			///////////////////////////////////////
			// putenv('LANG=en_US.UTF-8');
			$output = trim(shell_exec($cmd));

			// Deleting the jail folder, if still exists
			shell_exec("cd $tester_path; rm -rf jail*");

			// Saving judge result
			if ( is_numeric($output) || $output === 'Compilation Error' || $output === 'Syntax Error' )
			{
				shell_exec("mv $userdir/result.html $userdir/result-{$submit_id}.html");
				shell_exec("mv $userdir/log $userdir/log-{$submit_id}");
			}

			if (is_numeric($output)) {
				$submission['pre_score'] = $output;
				$submission['status'] = 'SCORE';
			}
			else {
				$submission['pre_score'] = 0;
				$submission['status'] = $output;
			}

			//reconnect to database incase we have run test for a long time.
			$this->db->reconnect();
			// Save the result
			$this->queue_model->save_judge_result_in_db($submission, $type);

			// Remove the judged item from queue
			$this->queue_model->remove_item($username, $assignment, $problem['id'], $submit_id);

			// Get next item from queue
			$queue_item = $this->queue_model->get_first_item();

		}while($queue_item !== NULL && $this->settings_model->get_setting('queue_is_working'));

		$this->settings_model->set_setting('queue_is_working', '0');
		
	}

}
