<?php
/**
 * SharIF Judge online judge
 * @file Submissions.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SettingsModel;
use App\Models\SubmitModel;
use App\Models\User;
use App\Models\UserModel;
use App\Libraries\Shj_pagination;
use App\Models\ScoreboardModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Submissions extends BaseController
{

	private $problems;

	private $filter_user;
	private $filter_problem;
	private $page_number;

	protected $assignment_model;
	protected $submit_model;
	protected $user;
	protected $validation;
	protected $settings_model;
	protected $user_model;
	protected $scoreboard_model;
	protected $uri;

	// ------------------------------------------------------------------------


	public function __construct()
	{
		$this->user = new User();
		$this->assignment_model = new AssignmentModel();
		$this->submit_model = new SubmitModel();
		$this->validation = \Config\Services::validation();
		$this->settings_model = new SettingsModel();
		$this->user_model = new UserModel();
		$this->problems = $this->assignment_model->all_problems($this->user->selected_assignment['id']);
		$this->uri = service('uri');
		$temp_inp = $this->uri->getSegments();
		$this->filter_user = $this->filter_problem = NULL;
		$this->page_number = 1;
		$input=[];
		foreach($temp_inp as $key => $value){
			if($key != (count($temp_inp)-1)){
				$input[$value] = $temp_inp[$key+1];	
			}else{
				break;
			}
		}
		if (array_key_exists('user', $input) && $input['user'])
			if ($this->user->level > 0) // students are not able to filter submissions by user
				$this->filter_user = ctype_alnum($input['user'])?$input['user']:NULL;
		if (array_key_exists('problem', $input) && $input['problem'])
			$this->filter_problem = is_numeric($input['problem'])?$input['problem']:NULL;
		if (array_key_exists('page', $input) && $input['page'])
			$this->page_number = is_numeric($input['page'])?$input['page']:1;

	}




	// ------------------------------------------------------------------------


	/**
	 * Uses PHPExcel library to generate excel file of submissions
	 */
	private function _download_excel($view)
	{
		if ( ! in_array($view, array('all', 'final')))
			exit;

		$now = shj_now_str(); // current time

		// Load PHPExcel library
		$phpspreedsheet = new Spreadsheet();

		// Set document properties
		$phpspreedsheet->getProperties()->setCreator('SharIF Judge')
			->setLastModifiedBy('SharIF Judge')
			->setTitle('SharIF Judge Users')
			->setSubject('SharIF Judge Users')
			->setDescription('List of SharIF Judge users ('.$now.')');

		// Name of the file sent to browser
		$output_filename = 'judge_'.$view.'_submissions';

		// Set active sheet
		$phpspreedsheet->setActiveSheetIndex(0);
		$sheet = $phpspreedsheet->getActiveSheet();

		// Add current assignment, time, username filter, and problem filter to document
		$sheet->fromArray(array('Assignment:',$this->user->selected_assignment['name']), null, 'A1', true);
		$sheet->fromArray(array('Time:',$now), null, 'A2', true);
		$sheet->fromArray(array('Username Filter:', $this->filter_user?$this->filter_user:'No filter'), null, 'A3', true);
		$sheet->fromArray(array('Problem Filter:', $this->filter_problem?$this->filter_problem:'No filter'), null, 'A4', true);

		// Prepare header
		if ($this->user->level === 0)
			$header=array('Final','Problem','Submit Time','Score','Delay (HH:MM)','Coefficient','Final Score','Language','Status');
		else{
			$header=array('Final','Submit ID','Username','Name','Problem','Submit Time','Score','Delay (HH:MM)','Coefficient','Final Score','Language','Status');
			if ($view === 'final'){
				array_unshift($header, "#2");
				array_unshift($header, "#1");
			}
		}

		// Add header to document
		$sheet->fromArray($header, null, 'A6', true);
		$highest_column = $sheet->getHighestColumn();

		// Set custom style for header
		$sheet->getStyle('A6:'.$highest_column.'6')->applyFromArray(
			array(
				'fill' => array(
					'fillType' => Fill::FILL_SOLID,
					'color' => array('rgb' => '173C45')
				),
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => 'FFFFFF'),
					//'size'  => 14
				)
			)
		);

		// Prepare data (in $rows array)
		if ($view === 'final')
			$items = $this->submit_model->get_final_submissions($this->user->selected_assignment['id'], $this->user->level, $this->user->username, NULL, $this->filter_user, $this->filter_problem);
		else
			$items = $this->submit_model->get_all_submissions($this->user->selected_assignment['id'], $this->user->level, $this->user->username, NULL, $this->filter_user, $this->filter_problem);

		$names = $this->user_model->get_names();

		$finish = strtotime($this->user->selected_assignment['finish_time']);
		$i=0; $j=0; $un='';
		$rows = array();
		foreach ($items as $item){
			$i++;
			if ($item['username'] != $un)
				$j++;
			$un = $item['username'];

			$pi = $this->problems[$item['problem']];

			$pre_score = ceil($item['pre_score']*$pi['score']/10000);

			$checked='';
			if ($item['is_final'])
				$checked='*';

			$delay = strtotime($item['time'])-$finish;
			if ($item['coefficient'] === 'error')
				$final_score = 0;
			else
				$final_score = ceil($pre_score*$item['coefficient']/100);


			if ($this->user->level === 0)
				$row = array(
					$checked,
					$item['problem'].' ('.$pi['name'].')',
					$item['time'],
					$pre_score,
					($delay<=0?'No Delay':time_hhmm($delay)),
					$item['coefficient'],
					$final_score,
					filetype_to_language($item['file_type']),
					$item['status'],
				);
			else {
				$row = array(
					$checked,
					$item['submit_id'],
					$item['username'],
					$names[$item['username']],
					$item['problem'].' ('.$pi['name'].')',
					$item['time'],
					$pre_score,
					($delay<=0?'No Delay':time_hhmm($delay)),
					$item['coefficient'],
					$final_score,
					filetype_to_language($item['file_type']),
					$item['status'],
				);
				if ($view === 'final'){
					array_unshift($row,$j);
					array_unshift($row,$i);
				}
			}
			array_push($rows, $row);
		}

		// Add rows to document
		$sheet->fromArray($rows, null, 'A7', true);
		// Add alternative colors to rows
		for ($i=7; $i<count($rows)+7; $i++){
			$sheet->getStyle('A'.$i.':'.$highest_column.$i)->applyFromArray(
				array(
					'fill' => array(
						'fillType' => Fill::FILL_SOLID,
						'color' => array('rgb' => (($i%2)?'F0F0F0':'FAFAFA'))
					)
				)
			);
		}

		// Set text align to center
		$sheet->getStyle( $sheet->calculateWorksheetDimension() )
			->getAlignment()
			->setHorizontal(Alignment::HORIZONTAL_CENTER);

		// Making columns autosize
		for ($i=2;$i<count($header);$i++)
			$sheet->getColumnDimension(chr(65+$i))->setAutoSize(true);

		// Set Border
		$sheet->getStyle('A7:'.$highest_column.$sheet->getHighestRow())->applyFromArray(
			array(
				'borders' => array(
					'outline' => array(
						'borderStyle' => Border::BORDER_THIN,
						'color' => array('rgb' => '444444'),
					),
				)
			)
		);

		// Send the file to browser

		$ext = 'xlsx';
		if ( ! class_exists('ZipArchive') ) // If class ZipArchive does not exist, export to excel5 instead of excel 2007
			$ext = 'xls';

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$output_filename.'.'.$ext.'"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($phpspreedsheet, ucfirst($ext));
		$objWriter->save('php://output');
	}




	// ------------------------------------------------------------------------




	public function final_excel()
	{
		$this->_download_excel('final');
	}



	public function all_excel()
	{
		$this->_download_excel('all');
	}




	// ------------------------------------------------------------------------




	public function the_final()
	{

		if ( ! is_numeric($this->page_number))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		if ($this->page_number<1)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$config = array(
			'base_url' => site_url('submissions/final'.($this->filter_user?'/user/'.$this->filter_user:'').($this->filter_problem?'/problem/'.$this->filter_problem:'')),
			'cur_page' => $this->page_number,
			'total_rows' => $this->submit_model->count_final_submissions($this->user->selected_assignment['id'], $this->user->level, $this->user->username, $this->filter_user, $this->filter_problem),
			'per_page' => $this->settings_model->get_setting('results_per_page_final'),
			'num_links' => 5,
			'full_ul_class' => 'shj_pagination',
			'cur_li_class' => 'current_page'
		);

		if ($config['per_page'] == 0)
			$config['per_page'] = $config['total_rows'];
		$shj_pagination = new Shj_pagination($config);

		$submissions = $this->submit_model->get_final_submissions($this->user->selected_assignment['id'], $this->user->level, $this->user->username, $this->page_number, $this->filter_user, $this->filter_problem);

		$names = $this->user_model->get_names();

		foreach ($submissions as &$item)
		{
			$item['name'] = $names[$item['username']];
			$item['fullmark'] = ($item['pre_score'] == 10000);
			$item['pre_score'] = ceil($item['pre_score']*$this->problems[$item['problem']]['score']/10000);
			$item['delay'] = strtotime($item['time'])-strtotime($this->user->selected_assignment['finish_time']);
			$item['language'] = filetype_to_language($item['file_type']);
			if ($item['coefficient'] === 'error')
				$item['final_score'] = 0;
			else
				$item['final_score'] = ceil($item['pre_score']*$item['coefficient']/100);
		}


		$data = array(
			'view' => 'final',
			'all_assignments' => $this->assignment_model->all_assignments(),
			'problems' => $this->problems,
			'submissions' => $submissions,
			'excel_link' => site_url('submissions/final_excel'.($this->filter_user?'/user/'.$this->filter_user:'').($this->filter_problem?'/problem/'.$this->filter_problem:'')),
			'filter_user' => $this->filter_user,
			'filter_problem' => $this->filter_problem,
			'pagination' => $shj_pagination->create_links(),
			'page_number' => $this->page_number,
			'per_page' => $config['per_page'],
			'selected' => "final_submissions",
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		return view('pages/submissions', $data);
	}




	// ------------------------------------------------------------------------




	public function all()
	{

		if ( ! is_numeric($this->page_number))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		if ($this->page_number < 1)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		$config = array(
			'base_url' => site_url('submissions/all'.($this->filter_user?'/user/'.$this->filter_user:'').($this->filter_problem?'/problem/'.$this->filter_problem:'')),
			'cur_page' => $this->page_number,
			'total_rows' => $this->submit_model->count_all_submissions($this->user->selected_assignment['id'], $this->user->level, $this->user->username, $this->filter_user, $this->filter_problem),
			'per_page' => $this->settings_model->get_setting('results_per_page_all'),
			'num_links' => 5,
			'full_ul_class' => 'shj_pagination',
			'cur_li_class' => 'current_page'
		);
		if ($config['per_page']==0)
			$config['per_page'] = $config['total_rows'];

		$shj_pagination = new Shj_pagination($config);
		$submissions = $this->submit_model->get_all_submissions($this->user->selected_assignment['id'], $this->user->level, $this->user->username, $this->page_number, $this->filter_user, $this->filter_problem);
		$names = $this->user_model->get_names();

		foreach ($submissions as &$item)
		{
			$item['name'] = $names[$item['username']];
			$item['fullmark'] = ($item['pre_score'] == 10000);
			$item['pre_score'] = ceil($item['pre_score']*$this->problems[$item['problem']]['score']/10000);
			$item['delay'] = strtotime($item['time'])-strtotime($this->user->selected_assignment['finish_time']);
			$item['language'] = filetype_to_language($item['file_type']);
			if ($item['coefficient'] === 'error')
				$item['final_score'] = 0;
			else
				$item['final_score'] = ceil($item['pre_score']*$item['coefficient']/100);
		}

		$data = array(
			'view' => 'all',
			'all_assignments' => $this->assignment_model->all_assignments(),
			'problems' => $this->problems,
			'submissions' => $submissions,
			'excel_link' => site_url('submissions/all_excel'.($this->filter_user?'/user/'.$this->filter_user:'').($this->filter_problem?'/problem/'.$this->filter_problem:'')),
			'filter_user' => $this->filter_user,
			'filter_problem' => $this->filter_problem,
			'pagination' => $shj_pagination->create_links(),
			'selected' => "all_submissions",
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
		);

		return view('pages/submissions', $data);
	}




	// ------------------------------------------------------------------------




	/**
	 * Used by ajax request (for selecting final submission)
	 */
	public function select()
	{
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		// Students cannot change their final submission after finish_time + extra_time
		if ($this->user->level === 0)
			if ( shj_now() > strtotime($this->user->selected_assignment['finish_time'])+$this->user->selected_assignment['extra_time'])
			{
				$json_result = array(
					'done' => 0,
					'message' => 'This assignment is finished. You cannot change your final submissions.'
				);
				$this->response->setHeader('Content-Type: application/json;', 'charset=utf-8');
				echo json_encode($json_result);
				return;
			}

		$this->validation->setRule('submit_id', 'Submit ID', 'integer|greater_than[0]');
		$this->validation->setRule('problem', 'problem', 'integer|greater_than[0]');
		$this->validation->setRule('username', 'Username', 'required|min_length[3]|max_length[20]|alpha_numeric');

		if ($this->validation->withRequest($this->request)->run())
		{
			$username = $this->request->getPost('username');
			if ($this->user->level === 0)
				$username = $this->user->username;

			$res = $this->submit_model->set_final_submission(
				$username,
				$this->user->selected_assignment['id'],
				$this->request->getPost('problem'),
				$this->request->getPost('submit_id')
			);

			if ($res) {
				// each time a user changes final submission, we should update scoreboard of that assignment
				$this->scoreboard_model = new ScoreboardModel();
				$this->scoreboard_model->update_scoreboard($this->user->selected_assignment['id']);
				$json_result = array('done' => 1);
			}
			else
				$json_result = array('done' => 0, 'message' => 'Selecting Final Submission Failed');
		}
		else
			$json_result = array('done' => 0, 'message' => 'Input Error');

		$this->response->setHeader('Content-Type: application/json;', 'charset=utf-8');
		echo json_encode($json_result);
	}


	/**
	 * For "view code" or "view result" or "view log"
	 */
	public function view_code()
	{
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$this->validation->setRule('type','type','_check_type');
		$this->validation->setRule('username','username','required|min_length[3]|max_length[20]|alpha_numeric');
		$this->validation->setRule('assignment','assignment','integer|greater_than[0]');
		$this->validation->setRule('problem','problem','integer|greater_than[0]');
		$this->validation->setRule('submit_id','submit_id','integer|greater_than[0]');

		if($this->validation->withRequest($this->request)->run())
		{
			$submission = $this->submit_model->get_submission(
				$this->request->getPost('username'),
				$this->request->getPost('assignment'),
				$this->request->getPost('problem'),
				$this->request->getPost('submit_id')
			);
			if ($submission === FALSE)
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

			$type = $this->request->getPost('type'); // $type is 'code', 'result', or 'log'

			if ($this->user->level === 0 && $type === 'log')
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

			if ($this->user->level === 0 && $this->user->username != $submission['username'])
				exit('Don\'t try to see submitted codes :)');

			if ($type === 'result')
				$file_path = rtrim($this->settings_model->get_setting('assignments_root'),'/').
					"/assignment_{$submission['assignment']}/p{$submission['problem']}/{$submission['username']}/result-{$submission['submit_id']}.html";
			elseif ($type === 'code')
				$file_path = rtrim($this->settings_model->get_setting('assignments_root'),'/').
					"/assignment_{$submission['assignment']}/p{$submission['problem']}/{$submission['username']}/{$submission['file_name']}.".filetype_to_extension($submission['file_type']);
			elseif ($type === 'log')
				$file_path = rtrim($this->settings_model->get_setting('assignments_root'),'/').
					"/assignment_{$submission['assignment']}/p{$submission['problem']}/{$submission['username']}/log-{$submission['submit_id']}";
			else
				$file_path = '/nowhere'; // This line is never reached!

			$result = array(
				'file_name' => $submission['main_file_name'].'.'.filetype_to_extension($submission['file_type']),
				'text' => file_exists($file_path)?file_get_contents($file_path):'File Not Found'
			);

			if ($type === 'code') {
				$result['lang'] = $submission['file_type'];
				if ($result['lang'] == 'py2' || $result['lang'] == 'py3')
					$result['lang'] = 'python';
			}

			$this->response->setContentType('application/json')->setBody(json_encode($result));
			$this->response->send();

		}
		else
			exit('Are you trying to see other users\' codes? :)');
	}




	// ------------------------------------------------------------------------




	public function download_file()
	{
		$username = $this->uri->getSegment(3);
		$assignment = $this->uri->getSegment(4);
		$problem = $this->uri->getSegment(5);
		$submit_id = $this->uri->getSegment(6);

		$submission = $this->submit_model->get_submission(
			$username,
			$assignment,
			$problem,
			$submit_id
		);
		if ($submission === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

		if ($this->user->level === 0 && $this->user->username != $submission['username'])
			exit('Don\'t try to see submitted codes :)');

		$file_path = rtrim($this->settings_model->get_setting('assignments_root'),'/').
		"/assignment_{$submission['assignment']}/p{$submission['problem']}/{$submission['username']}/{$submission['file_name']}.".filetype_to_extension($submission['file_type']);

		return $this->response->download(
			"{$submission['file_name']}.".filetype_to_extension($submission['file_type']),
			file_get_contents($file_path)
		);

	}


}
