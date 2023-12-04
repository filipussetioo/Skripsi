<?php
/**
 * SharIF Judge online judge
 * @file Profile.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SettingsModel;
use App\Models\User;
use App\Models\UserModel;

class Profile extends BaseController
{

	private $form_status;
	private $edit_username;
	protected $session;
	protected $user;
	protected $userModel;
	protected $validation;
	protected $assignment_model;
	protected $settings_model;



	// ------------------------------------------------------------------------


	public function __construct()
	{
		$this->session = session();
		$this->user_model = new UserModel();
		$this->user = new User();
		$this->validation = \Config\Services::validation();
		$this->assignment_model = new AssignmentModel();
		$this->settings_model = new SettingsModel();
		
		$this->form_status = '';
	}


	// ------------------------------------------------------------------------


	public function index($user_id = FALSE)
	{
		if ($user_id === FALSE){
			$user_id = $this->user_model->username_to_user_id($this->user->username);
		}
		
		if ( ! is_numeric($user_id)){
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$user = $this->user_model->get_user($user_id);
		if ($user === FALSE){
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->edit_username = $user->username;

		//Non-admins are not able to update others' profile
		if ($this->user->level <= 2 && $this->user->username != $this->edit_username){ // permission denied
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}
		$this->validation->setRule('display_name', 'name', 'max_length[40]');
		$this->validation->setRule('email', 'email address', "required|max_length[40]|valid_email|email_check[email,$this->edit_username]", array('email_check' => 'This {field} already exists.'));
		$this->validation->setRule('password', 'password', 'password_check', array('password_check' => 'The {field} field must be between 6 and 200 characters in length.'));
		$this->validation->setRule('password_again', 'password confirmation', 'password_again_check', array('password_again_check' => 'The {field} field does not match the password field.'));
		$this->validation->setRule('role', 'role', "role_check[$user->role]",['role' => 'role.role_check']);

		if($this->request->is('post')){
			if ($this->validation->withRequest($this->request)->run()){
				$email = $this->request->getPost('email');
				$password = $this->request->getPost('password');
				$role = $this->request->getPost('role');
				$display_name = $this->request->getPost('display_name');
				$this->user_model->update_profile($user_id,$email,$password,$role,$display_name);
				
				$user = $this->user_model->get_user($user_id);
				$this->form_status = 'ok';
			}
			else{
				$data = ['errors' => $this->validation->getErrors()];
			}
		}

		$data = array(
			'all_assignments' => $this->assignment_model->all_assignments(),
			'id' => $user_id,
			'edit_username' => $this->edit_username,
			'email' => $user->email,
			'display_name' => $user->display_name,
			'role' => $user->role,
			'form_status' => $this->form_status,
			'lock_student_display_name' => $this->settings_model->get_setting('lock_student_display_name'),
			'selected' => 'users',
			'level' => $this->user->level,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			'user' => $this->user,
			'validationError' => $this->validation,
		);
		
		return view('pages/profile', $data);
	}
}
