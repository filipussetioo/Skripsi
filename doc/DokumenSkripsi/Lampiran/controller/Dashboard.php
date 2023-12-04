<?php
/**
 * SharIF Judge online judge
 * @file Dashboard.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\NotificationsModel;
use App\Models\SettingsModel;
use App\Models\User;
use App\Models\UserModel;
use Exception;

class Dashboard extends BaseController
{
	protected $db;
	protected $assignment_model;
	protected $notifications_model;
	protected $settings_model;
	protected $user;
	protected $session;
	protected $user_model;
	
	public function __construct()
	{
		$this->session = session();
		$this->assignment_model = new AssignmentModel();
		$this->settings_model = new SettingsModel();
		$this->user = new User();
		$this->user_model = new UserModel();
		$this->notifications_model = new NotificationsModel();
	}


	// ------------------------------------------------------------------------


	public function index()
	{
		$data = array(
			'all_assignments'=>$this->assignment_model->all_assignments(),
			'week_start'=>$this->settings_model->get_setting('week_start'),
			'wp'=>$this->user->get_widget_positions(),
			'notifications' => $this->notifications_model->get_latest_notifications(),
			'selected' => 'dashboard',
			'user' => $this->user,
			'finish_time' => $this->user->selected_assignment['finish_time'],
			'extra_time' => $this->user->selected_assignment['extra_time'],
			
		);

		// detecting errors:
		$data['errors'] = array();
		if($this->user->level === 3){
			$path = $this->settings_model->get_setting('assignments_root');
			if ( ! file_exists($path))
				array_push($data['errors'], 'The path to folder "assignments" is not set correctly. Move this folder somewhere not publicly accessible, and set its full path in Settings.');
			elseif ( ! is_writable($path))
				array_push($data['errors'], 'The folder <code>"'.$path.'"</code> is not writable by PHP. Make it writable. But make sure that this folder is only accessible by you. Codes will be saved in this folder!');

			$path = $this->settings_model->get_setting('tester_path');
			if ( ! file_exists($path))
				array_push($data['errors'], 'The path to folder "tester" is not set correctly. Move this folder somewhere not publicly accessible, and set its full path in Settings.');
			elseif ( ! is_writable($path))
				array_push($data['errors'], 'The folder <code>"'.$path.'"</code> is not writable by PHP. Make it writable. But make sure that this folder is only accessible by you.');
		}
		
		return view('pages/dashboard', $data);
	}


	// ------------------------------------------------------------------------

	/**
	 * Used by ajax request, for saving the user's Dashboard widget positions
	 */
	public function widget_positions()
	{	
		if ( ! $this->request->isAJAX() )
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		if ($this->request->getPost('positions') !== NULL)
			$this->user->save_widget_positions($this->request->getPost('positions'));
	}

}