<?php
/**
 * SharIF Judge online judge
 * @file Submit.php
 * @author: Filipus Setio Nugroho <filipussetio@gmail.com>
 */

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\QueueModel;
use App\Models\SettingsModel;
use App\Models\SubmitModel;
use App\Models\User;

class Submit extends BaseController
{

	private $data; //data sent to view
	private $assignment_root;
	private $problems;
	private $problem;//submitted problem id
	private $filetype; //type of submitted file
	private $ext; //uploaded file extension
	private $file_name; //uploaded file name without extension
	private $coefficient;

	// ------------------------------------------------------------------------
	protected $queue_model;
	protected $settings_model;
	protected $assignment_model;
	protected $submit_model;
	protected $user;
	protected $validation;

	public function __construct()
	{
		$this->queue_model = new QueueModel();
		$this->settings_model = new SettingsModel();
		$this->assignment_model = new AssignmentModel();
		$this->user = new User();
		$this->validation = \Config\Services::validation();
		$this->assignment_root = $this->settings_model->get_setting('assignments_root');
		$this->problems = $this->assignment_model->all_problems($this->user->selected_assignment['id']);
		
		helper('shj_helper');
		$extra_time = $this->user->selected_assignment['extra_time'];
		$delay = shj_now()-strtotime($this->user->selected_assignment['finish_time']);;
		ob_start();
		if ( isset($this->user->selected_assignment['late_rule']) ? eval($this->user->selected_assignment['late_rule']) === FALSE : '')
			$coefficient = "error";
		if (!isset($coefficient))
			$coefficient = "error";
		ob_end_clean();
		$this->coefficient = $coefficient;
	}


	// ------------------------------------------------------------------------


	public function _language_to_type($language)
	{
		$language = strtolower ($language);
		switch ($language) {
			case 'c': return 'c';
			case 'c++': return 'cpp';
			case 'python 2': return 'py2';
			case 'python 3': return 'py3';
			case 'java': return 'java';
			case 'zip': return 'zip';
			case 'pdf': return 'pdf';
			case 'txt': return 'txt';
			default: return FALSE;
		}
	}


	// ------------------------------------------------------------------------


		public function _language_to_ext($language)
		{
			$language = strtolower ($language);
			switch ($language) {
				case 'c': return 'c';
				case 'c++': return 'cpp';
				case 'python 2': return 'py';
				case 'python 3': return 'py';
				case 'java': return 'java';
				case 'zip': return 'zip';
				case 'pdf': return 'pdf';
				case 'txt': return 'txt';
				default: return FALSE;
			}
		}


	// ------------------------------------------------------------------------


	public function _match($type, $extension)
	{
		switch ($type) {
			case 'c': return ($extension==='c'?TRUE:FALSE);
			case 'cpp': return ($extension==='cpp'?TRUE:FALSE);
			case 'py2': return ($extension==='py'?TRUE:FALSE);
			case 'py3': return ($extension==='py'?TRUE:FALSE);
			case 'java': return ($extension==='java'?TRUE:FALSE);
			case 'zip': return ($extension==='zip'?TRUE:FALSE);
			case 'pdf': return ($extension==='pdf'?TRUE:FALSE);
			case 'txt': return ($extension==='txt'?TRUE:FALSE);
		}
	}


	// ------------------------------------------------------------------------


	public function index()
	{
		$this->validation->setRule('problem', 'problem', 'required|integer|greater_than[0]', array('greater_than' => 'Select a %s.'));
		$this->validation->setRule('language', 'language', 'required|_check_language', array('_check_language' => 'Select a valid %s.'));
		if($this->request->is('post')){
			if ($this->validation->withRequest($this->request)->run())
			{
				if ($this->_upload())
					return redirect()->to('submissions/all');
				else
					throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Error Uploading File: '.$this->validation->listErrors());
			}
		}

		$this->data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'problems' => $this->problems,
			'in_queue' => FALSE,
			'coefficient' => $this->coefficient,
			'upload_state' => '',
			'problems_js' => '',
			'error' => '',
			'selected' => 'submit',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'validationError' => $this->validation
		);
		foreach ($this->problems as $problem)
		{
			$languages = explode(',', $problem['allowed_languages']);
			$items='';
			foreach ($languages as $language)
			{
				$items = $items."'".trim($language)."',";
			}
			$items = substr($items,0,strlen($items)-1);
			$this->data['problems_js'] .= "shj.p[{$problem['id']}]=[{$items}]; ";
		}
		if ($this->user->selected_assignment['id'] == 0)
			$this->data['error']='Please select an assignment first.';
		elseif ($this->user->level == 0 && ! $this->user->selected_assignment['open'])
			// if assignment is closed, non-student users (admin, instructors) still can submit
			$this->data['error'] = 'Selected assignment is closed.';
		elseif (shj_now() < strtotime($this->user->selected_assignment['start_time']))
			$this->data['error'] = 'Selected assignment has not started.';
		elseif (shj_now() > strtotime($this->user->selected_assignment['finish_time'])+$this->user->selected_assignment['extra_time']) // deadline = finish_time + extra_time
			$this->data['error'] = 'Selected assignment has finished.';
		elseif ( ! $this->assignment_model->is_participant($this->user->selected_assignment['participants'],$this->user->username) )
			$this->data['error'] = 'You are not registered for submitting.';
		else
			$this->data['error'] = 'none';

		return view('pages/submit', $this->data);
	}


	// ------------------------------------------------------------------------


	/**
	 * Saves submitted code and adds it to queue for judging
	 */
	private function _upload()
	{
		$now = shj_now();
		foreach($this->problems as $item)
			if ($item['id'] == $this->request->getPost('problem'))
			{
				$this->problem = $item;
				break;
			}
		$this->filetype = $this->_language_to_type(strtolower(trim($this->request->getPost('language'))));
		$this->ext = substr(strrchr($_FILES['userfile']['name'],'.'),1); // uploaded file extension
		$this->file_name = basename($_FILES['userfile']['name'], ".{$this->ext}"); // uploaded file name without extension
		if ( $this->queue_model->in_queue($this->user->username,$this->user->selected_assignment['id'], $this->problem['id']) )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You have already submitted for this problem. Your last submission is still in queue.');
		if ($this->user->level==0 && !$this->user->selected_assignment['open'])
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Selected assignment has been closed.');
		if ($now < strtotime($this->user->selected_assignment['start_time']))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Selected assignment has not started.');
		if ($now > strtotime($this->user->selected_assignment['finish_time'])+$this->user->selected_assignment['extra_time'])
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Selected assignment has finished.');
		if ( ! $this->assignment_model->is_participant($this->user->selected_assignment['participants'],$this->user->username) )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You are not registered for submitting.');
		$filetypes = explode(",",$this->problem['allowed_languages']);
		foreach ($filetypes as &$filetype)
		{
			$filetype = $this->_language_to_type(strtolower(trim($filetype)));
		}
		if ($_FILES['userfile']['error'] == 4)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('No file chosen.');
		if ( ! in_array($this->filetype, $filetypes))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('This file type is not allowed for this problem.');
		if ( ! $this->_match($this->filetype, $this->ext) )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('This file type does not match your selected language.');
		if ( ! preg_match('/^[a-zA-Z0-9_\-()]+$/', $this->file_name) )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invalid characters in file name.');

		$user_dir = rtrim($this->assignment_root, '/').'/assignment_'.$this->user->selected_assignment['id'].'/p'.$this->problem['id'].'/'.$this->user->username;
		if ( !file_exists($user_dir))
			mkdir($user_dir, 0700);

		$config['max_size']	= $this->settings_model->get_setting('file_size_limit');
		$config['file_name'] = $this->file_name."-".($this->user->selected_assignment['total_submits']+1).".".$this->ext;
		$config['max_file_name'] = 20;
		$this->validation->setRule('userfile', 'userfile', 'max_size[userfile,'.$config['max_size'].']',['max_size' => 'The file you are attempting to upload is larger than the permitted size.']);
		if ($this->validation->withRequest($this->request)->run())
		{	
			$results = $this->request->getFile('userfile');
			$results->move($user_dir,$config['file_name']);
			$result = preg_replace("/\.[^.]+$/", "", $results->getName());
			$this->submit_model = new SubmitModel();

			$submit_info = array(
				'submit_id' => $this->assignment_model->increase_total_submits($this->user->selected_assignment['id']),
				'username' => $this->user->username,
				'assignment' => $this->user->selected_assignment['id'],
				'problem' => $this->problem['id'],
				'file_name' => $result,
				'main_file_name' => $this->file_name,
				'file_type' => $this->filetype,
				'coefficient' => $this->coefficient,
				'pre_score' => 0,
				'time' => shj_now_str(),
			);
			if ($this->problem['is_upload_only'] == 0)
			{
				$this->queue_model->add_to_queue($submit_info);
				process_the_queue();
			}
			else
			{
				$this->submit_model->add_upload_only($submit_info);
			}

			return TRUE;
		}

		return FALSE;
	}


	// ------------------------------------------------------------------------

	/**
	 * Load code from editor file
	 */
	public function load($problem_id){
		$user_dir = rtrim($this->assignment_root, '/').'/assignment_'.$this->user->selected_assignment['id'].'/p'.$problem_id.'/'.$this->user->username;
		$file_path = $user_dir.'/'.EDITOR_FILE_NAME.'.'.EDITOR_FILE_EXT;
		$input_path = $user_dir.'/'.EDITOR_IN_NAME.'.'.EDITOR_FILE_EXT;
		$output_path = $user_dir.'/'.EDITOR_OUT_NAME.'.'.EDITOR_FILE_EXT;
		
		if(!write_file($input_path, ' ')){}
		if(!write_file($output_path, ' ')){}
		if (!file_exists($file_path)){
			$response = json_encode(array('content'=>'', 'message'=>'No saved file'));
		}
		else{
			$file_content = file_get_contents($file_path);
			if ($file_content === FALSE){
				$response = json_encode(array('content'=>'', 'message'=>'Unable to load'));
			}
			else{
				addslashes($file_content);
				$response = json_encode(array('content'=>$file_content, 'message'=>'Loaded'));
			}
		}
		echo $response;
	}


	// ------------------------------------------------------------------------

	/**
	 * Save code to editor file and submit/execute if needed
	 */
	public function save($type = FALSE){
		$data = $_POST['code_editor'];
		$problem_id = $_POST['problem_id'];
		$language = $_POST['language'];

		$user_dir = rtrim($this->assignment_root, '/').'/assignment_'.$this->user->selected_assignment['id'].'/p'.$problem_id.'/'.$this->user->username;
		if (!file_exists($user_dir)){
			mkdir($user_dir, 0700);
		}
		$file_path = $user_dir.'/'.EDITOR_FILE_NAME.'.'.EDITOR_FILE_EXT;
		$input_path = $user_dir.'/'.EDITOR_IN_NAME.'.'.EDITOR_FILE_EXT;

		if (!write_file($file_path, $data)){
			$response = json_encode(array('status'=>FALSE,'message'=>'Unable to save'));
			echo $response;
		}
		else{
			$response = json_encode(array('status'=>TRUE, 'message'=>'Saved'));
			if($type === FALSE){
				echo $response;
			}
			else{
				$now = shj_now();
				if ( $this->queue_model->in_queue($this->user->username,$this->user->selected_assignment['id'], $problem_id)){
					$response = json_encode(array('status'=>FALSE, 'message'=>'You have already submitted for this problem. Your last submission is still in queue.'));
					echo $response;
				}
				else if ($this->user->level==0 && !$this->user->selected_assignment['open']){
					$response = json_encode(array('status'=>FALSE, 'message'=>'Selected assignment has been closed.'));
					echo $response;
				}
				else if ($now < strtotime($this->user->selected_assignment['start_time'])){
					$response = json_encode(array('status'=>FALSE, 'message'=>'Selected assignment has not started.'));
					echo $response;
				}
				else if ($now > strtotime($this->user->selected_assignment['finish_time'])+$this->user->selected_assignment['extra_time']){
					$response = json_encode(array('status'=>FALSE, 'message'=>'Selected assignment has finished.'));
					echo $response;
				}
				else if ( ! $this->assignment_model->is_participant($this->user->selected_assignment['participants'],$this->user->username)){
					$response = json_encode(array('status'=>FALSE, 'message'=>'You are not registered for submitting.'));
					echo $response;
				}
				else{
					if($type === 'submit'){
						$this->_submit($data, $problem_id, $language, $user_dir);
					}
					else if($type === 'execute'){
						$editor_input =  $_POST['editor_input'];
						if (!write_file($input_path, $editor_input)){
							$response = json_encode(array('status'=>FALSE, 'message'=>'Unable to write input file'));
							echo $response;
						}
						else{
							$this->_execute($data, $problem_id, $language, $user_dir);
						}
					}
				}
			}
		}


	}


	// ------------------------------------------------------------------------

	/**
	 * Add code to queue for judging
	 */
	private function _submit($data, $problem_id, $language, $user_dir){
		$file_type = $this->_language_to_type(strtolower(trim($language)));
		$file_ext = $this->_language_to_ext(strtolower(trim($language)));
		$file_name = EDITOR_FILE_NAME;
		$file_fname = $file_name.'-'.($this->user->selected_assignment['total_submits']+1);
		$file_path = $user_dir.'/'.$file_fname.'.'.$file_ext;

		foreach($this->problems as $item)
			if ($item['id'] == $problem_id)
			{
				$this->problem = $item;
				break;
			}

		if (!write_file($file_path, $data)){
			$response = json_encode(array('status'=>FALSE, 'message'=>'Unable to submit'));
		}
		else{
			$this->submit_model = new SubmitModel();

			$submit_info = array(
				'submit_id' => $this->assignment_model->increase_total_submits($this->user->selected_assignment['id']),
				'username' => $this->user->username,
				'assignment' => $this->user->selected_assignment['id'],
				'problem' => $problem_id,
				'file_name' => $file_fname,
				'main_file_name' => $file_name,
				'file_type' => $file_type,
				'coefficient' => $this->coefficient,
				'pre_score' => 0,
				'time' => shj_now_str(),
			);
			if ($this->problem['is_upload_only'] == 0)
			{
				$this->queue_model->add_to_queue($submit_info);
				process_the_queue();
			}
			else
			{
				$this->submit_model->add_upload_only($submit_info);
			}

			$response = json_encode(array('status'=>TRUE, 'message'=>"Submitted"));
		}
		echo $response;
	}


	// ------------------------------------------------------------------------

	/**
	 * Add code to queue for execution only
	 */
	private function _execute($data, $problem_id, $language, $user_dir){
		$file_type = $this->_language_to_type(strtolower(trim($language)));
		$file_ext = $this->_language_to_ext(strtolower(trim($language)));
		$file_name = EDITOR_FILE_NAME;
		$file_fname = $file_name.'-'.EDITOR_SUBMIT_ID;
		$file_path = $user_dir.'/'.$file_fname.'.'.$file_ext;
		$output_path = $user_dir.'/'.EDITOR_OUT_NAME.'.'.EDITOR_FILE_EXT;

		if (!write_file($file_path, $data)){
			$response = json_encode(array('status'=>FALSE, 'message'=>'Unable to execute', 'debug'=>$file_path));
		}
		else{
			$submit_info = array(
				'submit_id' => EDITOR_SUBMIT_ID,
				'username' => $this->user->username,
				'assignment' => $this->user->selected_assignment['id'],
				'problem' => $problem_id,
				'file_name' => $file_fname,
				'main_file_name' => $file_name,
				'file_type' => $file_type,
				'coefficient' => $this->coefficient,
				'pre_score' => 0,
				'time' => shj_now_str(),
			);

			if($this->queue_model->add_to_queue_exec($submit_info)){
				if (!write_file($output_path, 'Queueing...')){
					$response = json_encode(array('status'=>FALSE, 'message'=>'Unable to write output file'));
				}
				else{
					process_the_queue();
					$response = json_encode(array('status'=>TRUE, 'message'=>'Executing'));
				}
			}
			else{
				$response = json_encode(array('status'=>FALSE, 'message'=>'Still in queue'));
			}
		}
		echo $response;
	}
	

	// ------------------------------------------------------------------------

	/**
	 * Load output file as execution result
	 */
	public function get_output($problem_id){
		$user_dir = rtrim($this->assignment_root, '/').'/assignment_'.$this->user->selected_assignment['id'].'/p'.$problem_id.'/'.$this->user->username;
		$file_path = $user_dir.'/'.EDITOR_OUT_NAME.'.'.EDITOR_FILE_EXT;

		if (!file_exists($file_path)){
			$response = json_encode(array('status'=>FALSE, 'content'=>''));
		}
		else{
			
			$file_content = file_get_contents($file_path);
			if ($file_content === FALSE){
				$response = json_encode(array('status'=>FALSE, 'content'=>''));
			}
			else{
				$complete_status = strpos($file_content, 'Total Execution Time');
				if($complete_status === FALSE){
					$response = json_encode(array('status'=>FALSE, 'content'=>$file_content));
				}
				else{
					$response = json_encode(array('status'=>TRUE, 'content'=>$file_content));
				}
			}
		}
		echo $response;
	}
}
