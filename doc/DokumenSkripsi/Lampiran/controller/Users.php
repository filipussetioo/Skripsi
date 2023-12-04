<?php
/**
 * SharIF Judge online judge
 * @file Users.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\User;
use App\Models\UserModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Users extends BaseController
{

	protected $session;
	protected $user;
	protected $user_model;
	protected $assignment_model;
	protected $validation;

	public function __construct()
	{
		$this->session = session();
		$this->user = new User();
		$this->user_model = new UserModel();
		$this->assignment_model = new AssignmentModel();
		$this->validation = \Config\Services::validation();
	}
	// ------------------------------------------------------------------------

	public function index()
	{

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'users' => $this->user_model->get_all_users(),
			'selected' => 'users',
			'level' => $this->user->level,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'user' => $this->user,
		);

		return view('pages/admin/users', $data);
	}




	// ------------------------------------------------------------------------




	public function add()
	{
		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'selected' => 'users',
			'level' => $this->user->level,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'user' => $this->user,
		);
		$this->validation->setRule('new_users', 'New Users', 'required');
		if ($this->validation->withRequest($this->request)->run())
		{
			if ( !$this->request->isAJAX() ){
				exit;
			}else{
				list($ok, $error) = $this->user_model->add_users(
					$this->request->getPost('new_users'),
					$this->request->getPost('send_mail'),
					$this->request->getPost('delay')
				);
			return view('pages/admin/add_user_result', array('ok' => $ok, 'error' => $error));
			}
		}
		else
		{
			return view('pages/admin/add_user', $data);
		}
	}




	// ------------------------------------------------------------------------




	/**
	 * Controller for deleting a user
	 * Called by ajax request
	 */
	public function delete()
	{
		if ( ! $this->request->isAJAX() )	
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$user_id = $this->request->getPost('user_id');
		if ( ! is_numeric($user_id) )
			$json_result = array('done' => 0, 'message' => 'Input Error');
		elseif ($this->user_model->delete_user($user_id))
			$json_result = array('done' => 1);
		else
			$json_result = array('done' => 0, 'message' => 'Deleting User Failed');

		$this->response->setHeader('Content-Type','application/json; charset=utf-8');
		echo json_encode($json_result);
	}




	// ------------------------------------------------------------------------




	/**
	 * Controller for deleting a user's submissions
	 * Called by ajax request
	 */
	public function delete_submissions()
	{
		if ( ! $this->request->isAJAX() )	
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('');
		$user_id = $this->request->getPost('user_id');
		if ( ! is_numeric($user_id) )
			$json_result = array('done' => 0, 'message' => 'Input Error');
		elseif ($this->user_model->delete_submissions($user_id))
			$json_result = array('done' => 1);
		else
			$json_result = array('done' => 0, 'message' => 'Deleting Submissions Failed');

		$this->response->setHeader('Content-Type','application/json; charset=utf-8');
		echo json_encode($json_result);
	}




	// ------------------------------------------------------------------------




	/**
	 * Uses PHPExcel library to generate excel file of users list
	 */
	public function list_excel()
	{

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
		$output_filename = 'sharifjudge_users';

		// Set active sheet
		$phpspreedsheet->setActiveSheetIndex(0);
		$sheet = $phpspreedsheet->getActiveSheet();

		// Add current time to document
		$sheet->fromArray(array('Time:',$now), null, 'A1', true);

		// Add header to document
		$header=array('#','User ID','Username','Display Name','Email','Role','First Login','Last Login');
		$sheet->fromArray($header, null, 'A3', true);
		$highest_column = $sheet->getHighestColumn();

		// Set custom style for header
		$sheet->getStyle('A3:'.$highest_column.'3')->applyFromArray(
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

		// Prepare user data (in $rows array)
		$users = $this->user_model->get_all_users();
		$i=0;
		$rows = array();
		foreach ($users as $user){
			array_push($rows, array(
				++$i,
				$user['id'],
				$user['username'],
				$user['display_name'],
				$user['email'],
				$user['role'],
				$user['first_login_time']===NULL?'Never':$user['first_login_time'],
				$user['last_login_time']===NULL?'Never':$user['last_login_time']
			));
		}

		// Add rows to document and set a background color of #7BD1BE
		$sheet->fromArray($rows, null, 'A4', true);
		// Add alternative colors to rows
		for ($i=4; $i<count($rows)+4; $i++){
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
		$sheet->getStyle('A4:'.$highest_column.$sheet->getHighestRow())->applyFromArray(
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

		// If class ZipArchive exists, export to excel2007, otherwise export to excel5
		if ( class_exists('ZipArchive') )
			$ext = 'xlsx';
		else
			$ext = 'xls';

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$output_filename.'.'.$ext.'"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($phpspreedsheet, ucfirst($ext));
		$objWriter->save('php://output');
	}


}
