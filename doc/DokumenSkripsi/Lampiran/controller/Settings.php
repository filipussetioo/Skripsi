<?php
/**
 * SharIF Judge online judge
 * @file Settings.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\User;
use App\Models\SettingsModel;
use Config\Validation;
use DateTimeZone;

class Settings extends BaseController
{

	private $form_status;
	private $errors;
	protected $session;
	protected $user;
	protected $settings_model;
	protected $assignment_model;
	protected $validation;
	protected $validationError;


	// ------------------------------------------------------------------------


	public function __construct()
	{
		$this->session = session();
		$this->user = new User();
		$this->settings_model = new SettingsModel();
		$this->assignment_model = new AssignmentModel();
		$this->validation = \Config\Services::validation();
		$this->form_status = '';
		$this->errors = array();
		$this->validationError = null;
	}


	// ------------------------------------------------------------------------


	public function index()
	{
		$settings = $this->settings_model->get_all_settings();
		$data = array_merge($settings,
			array(
				'all_assignments' => $this->assignment_model->all_assignments(),
				'sandbox_built' => file_exists(rtrim($settings['tester_path'], '/').'/easysandbox/EasySandbox.so'),
				'form_status' => $this->form_status,
				'errors' => $this->errors,
				'selected' => 'settings',
				'level' => $this->user->level,
				'finish_time' => $this->user->selected_assignment['finish_time'],
				'extra_time' => $this->user->selected_assignment['extra_time'],
				'user' => $this->user,
				'validationError' => $this->validation
			)
		);
		ob_start();
		$data ['defc'] = file_get_contents(rtrim($settings['tester_path'], '/').'/shield/defc.h');
		$data ['defcpp'] = file_get_contents(rtrim($settings['tester_path'], '/').'/shield/defcpp.h');
		$data ['shield_py2'] = file_get_contents(rtrim($settings['tester_path'], '/').'/shield/shield_py2.py');
		$data ['shield_py3'] = file_get_contents(rtrim($settings['tester_path'], '/').'/shield/shield_py3.py');
		ob_end_clean();

		return view('pages/admin/settings', $data);
	}


	// ------------------------------------------------------------------------


	public function update()
	{
		$this->validation->setRule('timezone', 'timezone', 'required');
		$this->validation->setRule('file_size_limit', 'File size limit', 'integer|greater_than_equal_to[0]');
		$this->validation->setRule('output_size_limit', 'Output size limit', 'integer|greater_than_equal_to[0]');
		$this->validation->setRule('rpp_all', 'results per page (all submissions)', 'integer|greater_than_equal_to[0]');
		$this->validation->setRule('rpp_final', 'results per page (final submissions)', 'integer|greater_than_equal_to[0]');
		$this->validation->setRule('mail_from', 'email', 'valid_email');
		if($this->validation->withRequest($this->request)->run()){
			ob_start();
			$this->form_status = 'ok';
			$tester_path = rtrim($this->settings_model->get_setting('tester_path'), '/');
			$defc_path = $tester_path.'/shield/defc.h';
			$defcpp_path = $tester_path.'/shield/defcpp.h';
			$shpy2_path = $tester_path.'/shield/shield_py2.py';
			$shpy3_path = $tester_path.'/shield/shield_py3.py';
			if ($this->request->getVar('def_c') !== file_get_contents($defc_path))
				if (file_exists($defc_path) && file_put_contents($defc_path,$this->request->getVar('def_c')) === FALSE)
					array_push($this->errors, 'File defc.h is not writable. Edit it manually.');
			if ($this->request->getVar('def_cpp') !== file_get_contents($defcpp_path))
				if (file_exists($defcpp_path) && file_put_contents($defcpp_path,$this->request->getVar('def_cpp')) === FALSE)
					array_push($this->errors, 'File defcpp.h is not writable. Edit it manually.');
			if ($this->request->getVar('shield_py2') !== file_get_contents($shpy2_path))
				if (file_exists($shpy2_path) && file_put_contents($shpy2_path,$this->request->getVar('shield_py2')) === FALSE)
					array_push($this->errors, 'File shield_py2.py is not writable. Edit it manually.');
			if ($this->request->getVar('shield_py3') !== file_get_contents($shpy3_path))
				if (file_exists($shpy3_path) && file_put_contents($shpy3_path,$this->request->getVar('shield_py3')) === FALSE)
					array_push($this->errors, 'File shield_py3.py is not writable. Edit it manually.');
			ob_end_clean();
			$timezone = $this->request->getVar('timezone');
			// if timezone is invalid, set it to 'Asia/Tehran' :
			if ( ! in_array($timezone, DateTimeZone::listIdentifiers()) )
				$timezone='Asia/Tehran';

			$this->settings_model->set_settings(
				array(
					'timezone' => $timezone,
					'tester_path' => $this->request->getVar('tester_path'),
					'assignments_root' => $this->request->getVar('assignments_root'),
					'file_size_limit' => $this->request->getVar('file_size_limit'),
					'output_size_limit' => $this->request->getVar('output_size_limit'),
					'default_late_rule' => $this->request->getVar('default_late_rule'),
					'enable_easysandbox' => $this->request->getVar('enable_easysandbox')===NULL?0:1,
					'enable_c_shield' => $this->request->getVar('enable_c_shield')===NULL?0:1,
					'enable_cpp_shield' => $this->request->getVar('enable_cpp_shield')===NULL?0:1,
					'enable_py2_shield' => $this->request->getVar('enable_py2_shield')===NULL?0:1,
					'enable_py3_shield' => $this->request->getVar('enable_py3_shield')===NULL?0:1,
					'enable_java_policy' => $this->request->getVar('enable_java_policy')===NULL?0:1,
					'enable_log' => $this->request->getVar('enable_log')===NULL?0:1,
					'enable_registration' => $this->request->getVar('enable_registration')===NULL?0:1,
					'registration_code' => $this->request->getVar('registration_code'),
					'mail_from' => $this->request->getVar('mail_from'),
					'mail_from_name' => $this->request->getVar('mail_from_name'),
					'reset_password_mail' => $this->request->getVar('reset_password_mail'),
					'add_user_mail' => $this->request->getVar('add_user_mail'),
					'results_per_page_all' => $this->request->getVar('rpp_all'),
					'results_per_page_final' => $this->request->getVar('rpp_final'),
					'week_start' => $this->request->getVar('week_start'),
					'lock_student_display_name' => $this->request->getVar('lock_student_display_name')===NULL?0:1,
				)
			);
		}
		else{
			$this->form_status = 'error';
		}
		return $this->index();
	}
}
