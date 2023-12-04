<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LogsModel;
use App\Models\SettingsModel;

/**
 * SharIF Judge online judge
 * @file Login.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */


class Login extends BaseController
{
	protected $settings_model;
	protected $validation;
	protected $session;
	protected $user_model;
	protected $logs_model;

	public function __construct()
	{
		$this->settings_model = new SettingsModel();
		$this->user_model = new UserModel();
		$this->logs_model = new LogsModel();
		$this->validation =  \Config\Services::validation();
		$this->session = session();
	}


	// ------------------------------------------------------------------------


	/**
	 * checks whether the entered registration code is correct or not
	 *
	 */
	public function _registration_code($code){
		$rc = $this->settings_model->get_setting('registration_code');
		if ($rc == '0')
			return TRUE;
		if ($rc == $code)
			return TRUE;
		return FALSE;
	}


	// ------------------------------------------------------------------------


	/**
	 * Login
	 */
	public function index()
	{
		if ($this->session->get('logged_in')) // if logged in
			return redirect()->to('/');

		$this->validation->setRules([
			'username' => ['label' => 'Username', 'rules' => 'required|min_length[3]|max_length[20]|alpha_numeric',['required' => 'username.required']],
			'password' => ['label' => 'Password', 'rules' => 'required|min_length[6]|max_length[200]']
		]);

		$data = [
			'error' => FALSE,
			'registration_enabled' => $this->settings_model->get_setting('enable_registration'),
			'title' => 'Login',
			'validationError' => $this->validation
		];
		if($this->request->is('post')){
			if($this->validation->withRequest($this->request)->run()){
				$username = $this->request->getPost('username');
				$password = $this->request->getPost('password');
				if($this->user_model->validate_user($username, $password)){
					$ip_adrress = $this->request->getIpaddress();
					// setting the session and redirecting to dashboard:
					$login_data = array(
						'username'  => $username,
						'logged_in' => TRUE
					);
					$this->session->set($login_data);
					$this->user_model->update_login_time($username);
					$this->logs_model->insert_to_logs($username,$ip_adrress);
					return redirect()->to('/');
				}
				else{
					// for displaying error message in 'pages/authentication/login' view
					$data['error'] = TRUE;
					return view('pages/authentication/login', $data);
				}
			}
		}else{
			return view('pages/authentication/login', $data);
		}
	}


	// ------------------------------------------------------------------------


	public function register()
	{
		if ($this->session->get('logged_in')) // if logged in
			return redirect()->to('dashboard');
		if ( ! $this->settings_model->get_setting('enable_registration'))
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Registration is Closed');
		$this->validation->setRule('registration_code', 'registration code', '_registration_code', array('_registration_code' => 'Invalid registration code'));
		$this->validation->setRule('username', 'username', 'required|min_length[3]|max_length[20]|alpha_numeric|lowercase[username]|is_unique[users.username]', array('is_unique' => 'This username already exists.','alpha_numeric'));
		$this->validation->setRule('email', 'email address', 'required|max_length[40]|valid_email|lowercase[username]|is_unique[users.email]', array('is_unique' => 'This email address already exists.'));
		$this->validation->setRule('password', 'password', 'required|min_length[6]|max_length[200]');
		$this->validation->setRule('password_again', 'password confirmation', 'required|matches[password]');
		$data = array(
			'registration_code_required' => $this->settings_model->get_setting('registration_code')=='0'?FALSE:TRUE,
			'title' => 'Registration',
			'validationError' => $this->validation
		);
		if($this->request->is('post')){
			if ($this->validation->withRequest($this->request)->run()){
				$this->user_model->add_user(
					$this->request->getPost('username'),
					$this->request->getPost('email'),
					$this->request->getPost('displayname'),
					$this->request->getPost('password'),
					'student'
				);
				return view('pages/authentication/register_success',$data);
			}
			else{
				return view('pages/authentication/register', $data);
			}
		}
		else{
			return view('pages/authentication/register', $data);
		}
	}


	// ------------------------------------------------------------------------


	/**
	 * Logs out and redirects to login page
	 */
	public function logout()
	{
		$this->session->destroy();
		return redirect()->to('/login');
	}


	// ------------------------------------------------------------------------


	public function lost()
	{
		if ($this->session->get('logged_in')) // if logged in
			return redirect()->to('dashboard');
		$this->validation->setRule('email', 'email', 'required|max_length[40]|lowercase[email]|valid_email');
		$data = array(
			'sent' => FALSE,
			'title' => 'Reset Password'
		);
		if ($this->validation->withRequest($this->request)->run())
		{ 
			$data['errors'] = $this->validation->getErrors();
			$this->user_model->send_password_reset_mail($this->request->getPost('email'));
			$data['sent'] = TRUE;
		}

		return view('pages/authentication/lost', $data);
	}


	// ------------------------------------------------------------------------


	public function reset($passchange_key = FALSE)
	{
		if ($passchange_key === FALSE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		$result = $this->user_model->passchange_is_valid($passchange_key);
		if ($result !== TRUE)
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($result);
		$this->validation->setRule('password', 'password', 'required|min_length[6]|max_length[200]');
		$this->validation->setRule('password_again', 'password confirmation', 'required|matches[password]');
		$data = array(
			'key' => $passchange_key,
			'result' => $result,
			'reset' => FALSE,
			'validationError' => $this->validation,
			'title' => 'Reset Password'
		);
		if($this->request->is('post')){
			if ($this->validation->withRequest($this->request)->run()){
				$this->user_model->reset_password($passchange_key, $this->request->getPost('password'));
				$data['reset'] = TRUE;
				return view('pages/authentication/reset_password', $data);
			}else{
				return view('pages/authentication/reset_password', $data);
			}
		}else{
			return view('pages/authentication/reset_password', $data);
		}
	}



}
