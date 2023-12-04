<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Unzip;
use App\Models\AssignmentModel;
use App\Models\SettingsModel;
use App\Models\SubmitModel;
use App\Models\User;
use ZipArchive;

/**
 * SharIF Judge online judge
 * @file Assignments.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

class Assignments extends BaseController
{

	private $messages;
	private $edit_assignment;
	private $edit;
	protected $assignment_model;
	protected $validation;
	protected $user;
	protected $assignment;
	protected $settings_model;
	protected $zip;
	protected $submit_model;
	protected $unzip;
	protected $parsedown;
	

	// ------------------------------------------------------------------------


	public function __construct()
	{
		$this->messages = array();
		$this->edit_assignment = array();
		$this->edit = FALSE;
		$this->assignment_model = new AssignmentModel();
		$this->settings_model = new SettingsModel();
		$this->validation = \Config\Services::validation();
		$this->user = new User();
	}


	// ------------------------------------------------------------------------


	public function index()
	{
		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'messages' => $this->messages,
			'selected' => 'assignments',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		foreach ($data['all_assignments'] as &$item)
		{
			$extra_time = $item['extra_time'];
			$delay = shj_now()-strtotime($item['finish_time']);;
			ob_start();
			if ( eval($item['late_rule']) === FALSE )
				$coefficient = "error";
			if (!isset($coefficient))
				$coefficient = "error";
			ob_end_clean();
			$item['coefficient'] = $coefficient;
			$item['finished'] = ($delay > $extra_time);
		}

		return view('pages/assignments', $data);

	}


	// ------------------------------------------------------------------------


	/**
	 * Used by ajax request (for select assignment from top bar)
	 */
	public function select()
	{
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->validation->setRule('assignment_select', 'Assignment', 'required|integer|greater_than[0]');

		if ($this->validation->withRequest($this->request)->run())
		{
			$this->user->select_assignment($this->request->getPost('assignment_select'));
			$this->assignment = $this->assignment_model->assignment_info($this->request->getPost('assignment_select'));
			$json_result = array(
				'done' => 1,
				'finish_time' => $this->assignment['finish_time'],
				'extra_time' => $this->assignment['extra_time'],
			);
		}
		else{
			$json_result = array('done' => 0, 'message' => 'Input Error');
		}
		$this->response->setHeader('Content-Type:','application/json; charset=utf-8');
		echo json_encode($json_result);
	}



	// ------------------------------------------------------------------------



	/**
	 * Download pdf file of an assignment (or problem) to browser
	 */
	public function pdf($assignment_id, $problem_id = NULL, $no_download = FALSE)
	{
		$finishtime = strtotime($this->assignment_model->assignment_info($assignment_id)['finish_time']);
		$starttime = strtotime($this->assignment_model->assignment_info($assignment_id)['start_time']);
		$extratime = $this->assignment_model->assignment_info($assignment_id)['extra_time'];

		// Find pdf file
		if ($problem_id === NULL || $problem_id === "null")
			$pattern = rtrim($this->settings_model->get_setting('assignments_root'),'/')."/assignment_{$assignment_id}/*.pdf";
		else
			$pattern = rtrim($this->settings_model->get_setting('assignments_root'),'/')."/assignment_{$assignment_id}/p{$problem_id}/*.pdf";
		$pdf_files = glob($pattern);
		if ( ! $pdf_files )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("File not found");
		elseif (!$this->assignment_model->assignment_info($assignment_id)['open'])
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Selected assignment has been closed.');
		elseif	( ! $this->assignment_model->is_participant($this->assignment_model->assignment_info($assignment_id)['participants'],$this->user->username) )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You are not registered for submitting.');
		elseif ( shj_now() > $finishtime + $extratime)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Selected assignment has finished.');
		elseif ( shj_now() < $starttime)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Selected assignment has not started.');


		
		$filename = shj_basename($pdf_files[0]);
		// Download the file to browser
		if($no_download === FALSE){
			return $this->response->download($filename, file_get_contents($pdf_files[0]));
		}
		else{
			$content = file_get_contents($pdf_files[0]);
			header('Content-Type: application/pdf');
			die($content);
		}
	}



	// ------------------------------------------------------------------------



	/**
	 * Compressing and downloading test data and descriptions of an assignment to the browser
	 */
	public function downloadtestsdesc($assignment_id = FALSE)
	{
		if ($assignment_id === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ( $this->user->level <= 1) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$assignment = $this->assignment_model->assignment_info($assignment_id);

		$number_of_problems = $assignment['problems'];

		$root_path = rtrim($this->settings_model->get_setting('assignments_root'),'/').
			"/assignment_{$assignment_id}";
		$zipname = "assignment{$assignment_id}_tests_desc_".date('Y-m-d_H-i', shj_now()).'.zip';
		$this->zip = new \ZipArchive();
		$this->zip->open($zipname, ZipArchive::CREATE);
		for ($i=1 ; $i<=$number_of_problems ; $i++)
		{

			$path = "$root_path/p{$i}/in";
			$options = ['add_path' => "p{$i}/in/", 'remove_all_path' => TRUE];
			$this->zip->addGlob($path.'/*.{txt}', GLOB_BRACE, $options);

			$path = "$root_path/p{$i}/out";
			$options = ['add_path' => "p{$i}/out/", 'remove_all_path' => TRUE];
			$this->zip->addGlob($path.'/*.{txt}', GLOB_BRACE, $options);

			$path = "$root_path/p{$i}/tester.cpp";
			if (file_exists($path))
				$this->zip->addFile($path,"p{$i}/tester.cpp");

			$pdf_files = glob("$root_path/p{$i}/*.pdf");
			if ($pdf_files)
			{
				$path = $pdf_files[0];
				$this->zip->addFile($path,"p{$i}/".shj_basename($path));
			}

			$path = "$root_path/p{$i}/desc.html";
			if (file_exists($path))
				$this->zip->addFile($path,"p{$i}/desc.html");

			$path = "$root_path/p{$i}/desc.md";
			if (file_exists($path))
				$this->zip->addFile($path,"p{$i}/desc.md");
		}

		$pdf_files = glob("$root_path/*.pdf");
		if ($pdf_files)
		{
			$path = $pdf_files[0];
			$this->zip->addFile($path,shj_basename($path));
		}
		$this->zip->close();
		
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename=' . $zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
		// $this->zip->download("assignment{$assignment_id}_tests_desc_".date('Y-m-d_H-i', shj_now()).'.zip');
	}


	// ------------------------------------------------------------------------


	/**
	 * Compressing and downloading final codes of an assignment to the browser
	 */
	public function download_submissions($type = FALSE, $assignment_id = FALSE)
	{
		if ($type !== 'by_user' && $type !== 'by_problem')
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ($assignment_id === FALSE || ! is_numeric($assignment_id))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ( $this->user->level == 0) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->submit_model = new SubmitModel();
		$items = $this->submit_model->get_final_submissions($assignment_id, $this->user->level, $this->user->username);
		$zipname = "assignment{$assignment_id}_submissions_{$type}_".date('Y-m-d_H-i', shj_now()).'.zip';

		$this->zip = new \ZipArchive();
		$this->zip->open($zipname, ZipArchive::CREATE);
		$assignments_root = rtrim($this->settings_model->get_setting('assignments_root'),'/');

		foreach ($items as $item)
		{
			$file_path = $assignments_root.
				"/assignment_{$item['assignment']}/p{$item['problem']}/{$item['username']}/{$item['file_name']}."
				.filetype_to_extension($item['file_type']);
			if ( ! file_exists($file_path))
				continue;
			$file = file_get_contents($file_path);

			if ($type === 'by_user')
				$this->zip->addFile($file_path,"{$item['username']}/p{$item['problem']}.".filetype_to_extension($item['file_type']));
			elseif ($type === 'by_problem')
				$this->zip->addFile($file_path,"problem_{$item['problem']}/{$item['username']}.".filetype_to_extension($item['file_type']));
		}
		$this->zip->close();

		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
	}


	// ------------------------------------------------------------------------


	/**
	 * Delete assignment
	 */
	public function delete($assignment_id = FALSE)
	{
		if ($assignment_id === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ($this->user->level <= 1) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$assignment = $this->assignment_model->assignment_info($assignment_id);

		if ($assignment['id'] === 0)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		if ($this->request->getPost('delete') === 'delete')
		{
			$this->assignment_model->delete_assignment($assignment_id);
			return redirect()->to('assignments');
		}

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'id' => $assignment_id,
			'name' => $assignment['name'],
			'selected' => 'assignments',
			'icon' => 'fa-times',
			'title' => 'Delete Assignments',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			
		);

		return view('pages/admin/delete_assignment', $data);

	}



	// ------------------------------------------------------------------------


	/**
	 * This method gets inputs from user for adding/editing assignment
	 */
	public function add()
	{

		if ($this->user->level <= 1) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();


		if ($this->request->is('post')){
			if ($this->_add()) // add/edit assignment
			{
				//if ( ! $this->edit) // if adding assignment (not editing)
				//{
				//   goto Assignments page
					return $this->index();
				//}
			}
		}

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'messages' => $this->messages,
			'edit' => $this->edit,
			'default_late_rule' => $this->settings_model->get_setting('default_late_rule'),
			'selected' => 'assignments',
			'icon' => 'fa-edit',
			'title' => 'Assignments',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'twig' => $this->twig,
			'validationError' => $this->validation,
		);

		if ($this->edit)
		{
			$data['edit_assignment'] = $this->assignment_model->assignment_info($this->edit_assignment);
			if ($data['edit_assignment']['id'] === 0)
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
			$data['problems'] = $this->assignment_model->all_problems($this->edit_assignment);
		}
		else
		{
			$names = $this->request->getPost('name');
			if ($names === NULL)
				$data['problems'] = array(
					array(
						'id' => 1,
						'name' => 'Problem ',
						'score' => 100,
						'c_time_limit' => 500,
						'python_time_limit' => 1500,
						'java_time_limit' => 2000,
						'memory_limit' => 50000,
						'allowed_languages' => 'C,C++,Python 2,Python 3,Java',
						'diff_cmd' => 'diff',
						'diff_arg' => '-bB',
						'is_upload_only' => 0
					)
				);
			else
			{
				$names = $this->request->getPost('name');
				$scores = $this->request->getPost('score');
				$c_tl = $this->request->getPost('c_time_limit');
				$py_tl = $this->request->getPost('python_time_limit');
				$java_tl = $this->request->getPost('java_time_limit');
				$ml = $this->request->getPost('memory_limit');
				$ft = $this->request->getPost('languages');
				$dc = $this->request->getPost('diff_cmd');
				$da = $this->request->getPost('diff_arg');
				$data['problems'] = array();
				$uo = $this->request->getPost('is_upload_only');
				if ($uo === NULL)
					$uo = array();
				for ($i=0; $i<count($names); $i++){
					array_push($data['problems'], array(
						'id' => $i+1,
						'name' => $names[$i],
						'score' => $scores[$i],
						'c_time_limit' => $c_tl[$i],
						'python_time_limit' => $py_tl[$i],
						'java_time_limit' => $java_tl[$i],
						'memory_limit' => $ml[$i],
						'allowed_languages' => $ft[$i],
						'diff_cmd' => $dc[$i],
						'diff_arg' => $da[$i],
						'is_upload_only' => in_array($i+1,$uo)?1:0,
					));
				}
			}
		}

		return view('pages/admin/add_assignment', $data);
	}


	// ------------------------------------------------------------------------


	/**
	 * Add/Edit assignment
	 */
	private function _add()
	{
		// Check permission
		if ($this->user->level <= 1) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->validation->setRule('assignment_name', 'assignment name', 'required|max_length[50]');
		$this->validation->setRule('start_time', 'start time', 'required');
		$this->validation->setRule('finish_time', 'finish time', 'required');
		$this->validation->setRule('extra_time', 'extra time', 'required');
		$this->validation->setRule('participants', 'participants','string');
		$this->validation->setRule('late_rule', 'coefficient rule', 'required');
		$this->validation->setRule('name.*', 'problem name', 'required|max_length[50]');
		$this->validation->setRule('score.*', 'problem score', 'required|integer');
		$this->validation->setRule('c_time_limit.*', 'C/C++ time limit', 'required|integer');
		$this->validation->setRule('python_time_limit.*', 'python time limit', 'required|integer');
		$this->validation->setRule('java_time_limit.*', 'java time limit', 'required|integer');
		$this->validation->setRule('memory_limit.*', 'memory limit', 'required|integer');
		$this->validation->setRule('languages.*', 'languages', 'required');
		$this->validation->setRule('diff_cmd.*', 'diff command', 'required');
		$this->validation->setRule('diff_arg.*', 'diff argument', 'required');
		// Validate input data
		if ( !$this->validation->withRequest($this->request)->run()){
			return FALSE;
		}

		// Preparing variables

		if ($this->edit)
			$the_id = $this->edit_assignment;
		else
			$the_id = $this->assignment_model->new_assignment_id();

		$assignments_root = rtrim($this->settings_model->get_setting('assignments_root'), '/');
		$assignment_dir = "$assignments_root/assignment_{$the_id}";

		// Adding/Editing assignment in database

		if ( !$this->assignment_model->add_assignment($the_id, $this->edit))
		{
			$this->messages[] = array(
				'type' => 'error',
				'text' => 'Error '.($this->edit?'updating':'adding').' assignment.'
			);
			return FALSE;
		}

		$this->messages[] = array(
			'type' => 'success',
			'text' => 'Assignment '.($this->edit?'updated':'added').' successfully.'
		);

		// Create assignment directory
		if ( !file_exists($assignment_dir) ){
			mkdir($assignment_dir, 0700, true);
		}


		// Upload Tests (zip file)
		$files = glob($assignments_root.'/*.zip'); 
		foreach($files as $file){
			if(is_file($file)){
				// Delete the given file 
				unlink($file); 
			} 
		}
		$zip_uploaded = $this->request->getFile('tests_desc');
		if ( $_FILES['tests_desc']['error'] === UPLOAD_ERR_NO_FILE ){
			$this->messages[] = array(
				'type' => 'notice',
				'text' => "Notice: You did not upload any zip file for tests. If needed, upload by editing assignment."
			);
		}
		elseif ( $zip_uploaded->getExtension() != "zip"){
			$this->messages[] = array(
				'type' => 'error',
				'text' => "Error: Error uploading tests zip file: The filetype you are attempting to upload is not allowed."
			);
		}
		else{
			$zip_uploaded->move($assignments_root);
			$this->messages[] = array(
				'type' => 'success',
				'text' => "Tests (zip file) uploaded successfully."
			);
		}

		// Upload PDF File of Assignment
		$old_pdf_files = glob("$assignment_dir/*.pdf");
		$pdf_uploaded = $this->request->getFile("pdf");
		if ($_FILES['pdf']['error'] === UPLOAD_ERR_NO_FILE){
			$this->messages[] = array(
				'type' => 'notice',
				'text' => "Notice: You did not upload any pdf file for assignment. If needed, upload by editing assignment."
			);
		}
		elseif ( $pdf_uploaded->getExtension() != "pdf"){
			$this->messages[] = array(
				'type' => 'error',
				'text' => "Error: Error uploading pdf file of assignment: The filetype you are attempting to upload is not allowed."
			);
		}
		else{
			$pdf_uploaded->move("$assignment_dir");
			{
				foreach($old_pdf_files as $old_name){
					unlink($old_name);
				}
				$this->messages[] = array(
					'type' => 'success',
					'text' => 'PDF file uploaded successfully.'
				);
			}
		}

		// Extract Tests (zip file)
		if ($zip_uploaded->getClientExtension() == "zip"){ // if zip file is uploaded
			$this->unzip = new ZipArchive();
			// Create a temp directory
			$tmp_dir_name = "shj_tmp_directory";
			$tmp_dir = "$assignments_root/$tmp_dir_name";
			shell_exec("rm -rf $tmp_dir; mkdir $tmp_dir;");

			// Extract new test cases and descriptions in temp directory
			$this->unzip->open("$assignments_root/".$zip_uploaded->getName());
			$extract_result = $this->unzip->extractTo($tmp_dir);

			// Remove the zip file
			unlink("$assignments_root/".$zip_uploaded->getName());
			
			if ($extract_result)
			{
				// Remove previous test cases and descriptions
				shell_exec("cd $assignment_dir;"
					." rm -rf */in; rm -rf */out; rm -f */tester.cpp; rm -f */tester.executable;"
					." rm -f */desc.html; rm -f */desc.md; rm -f */*.pdf;");
				if (glob("$tmp_dir/*.pdf"))
					shell_exec("cd $assignment_dir; rm -f *.pdf");
				// Copy new test cases from temp dir
				shell_exec("cd $assignments_root; cp -R $tmp_dir_name/* assignment_{$the_id};");
				$this->messages[] = array(
					'type' => 'success',
					'text' => 'Tests (zip file) extracted successfully.'
				);
			}
			else
			{
				$this->messages[] = array(
					'type' => 'error',
					'text' => 'Error: Error extracting zip archive.'
				);
				$this->messages[] = array(
					'type' => 'error',
					'text' => " Zip Extraction Error: ".$this->unzip->getStatusString(),
				);
			}
			// Remove temp directory
			shell_exec("rm -rf $tmp_dir");
		}

		// Create problem directories and parsing markdown files

		for ($i=1; $i <= $this->request->getPost('number_of_problems'); $i++)
		{
			if ( ! file_exists("$assignment_dir/p$i"))
				mkdir("$assignment_dir/p$i", 0700);
			elseif (file_exists("$assignment_dir/p$i/desc.md"))
			{
				$html = $this->parsedown->parse(file_get_contents("$assignment_dir/p$i/desc.md"));
				file_put_contents("$assignment_dir/p$i/desc.html", $html);
			}
		}

		return TRUE;
	}


	// ------------------------------------------------------------------------


	public function edit($assignment_id)
	{

		if ($this->user->level <= 1) // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$this->edit_assignment = $assignment_id;
		$this->edit = TRUE;

		// redirect to add function
		return $this->add();
	}
	


	// ------------------------------------------------------------------------



	/**
	 * Check PDF File Availability
	 */
	public function pdfCheck($assignment_id, $problem_id = NULL)
	{
		$finishtime = strtotime($this->assignment_model->assignment_info($assignment_id)['finish_time']);
		$starttime = strtotime($this->assignment_model->assignment_info($assignment_id)['start_time']);
		$extratime = $this->assignment_model->assignment_info($assignment_id)['extra_time'];

		// Find pdf file
		if ($problem_id === NULL || $problem_id === "null")
			$pattern = rtrim($this->settings_model->get_setting('assignments_root'),'/')."/assignment_{$assignment_id}/*.pdf";
		else
			$pattern = rtrim($this->settings_model->get_setting('assignments_root'),'/')."/assignment_{$assignment_id}/p{$problem_id}/*.pdf";
		$pdf_files = glob($pattern);

		if ( ! $pdf_files )
			$response = json_encode(array('status'=>FALSE));
		elseif (!$this->assignment_model->assignment_info($assignment_id)['open'])
			$response = json_encode(array('status'=>FALSE));
		elseif	( ! $this->assignment_model->is_participant($this->assignment_model->assignment_info($assignment_id)['participants'],$this->user->username) )
			$response = json_encode(array('status'=>FALSE));
		elseif ( shj_now() > $finishtime + $extratime)
			$response = json_encode(array('status'=>FALSE));
		elseif ( shj_now() < $starttime)
			$response = json_encode(array('status'=>FALSE));
		else
			$response = json_encode(array('status'=>TRUE));

		echo $response;
	}

}
