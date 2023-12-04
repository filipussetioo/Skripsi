<?php
/**
 * SharIF Judge online judge
 * @file Install.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Database\RawSql;
use Exception;

class Install extends BaseController
{

	protected $db;
	protected $dbforge;
	protected $user_model;
	protected $validation;
	protected $validationError;

	public function __construct()
	{
		$this->db = db_connect();
		$this->dbforge = \Config\Database::forge();
		$this->user_model = new UserModel;
		$this->validation = \Config\Services::validation();
	}


	// ------------------------------------------------------------------------

	public function index()
	{

		try{
			if ($this->db->tableExists('sessions'))
				throw new \Exception('SharIF Judge is already installed.');
		} catch (\Exception $e){
			exit($e->getMessage());
		}
		
		$this->validation->setRule('username', 'username', 'required|min_length[3]|max_length[20]|alpha_numeric');
		$this->validation->setRule('email', 'email', 'required|max_length[40]|valid_email');
		$this->validation->setRule('password', 'password', 'required|min_length[6]|max_length[200]');
		$this->validation->setRule('password_again', 'password confirmation', 'required|matches[password]');

		$data['installed'] = FALSE;
		$data['title'] = 'Installation';
		$data['validationError'] = $this->validation;
		$data['key_changed'] = FALSE;
		
		if($this->request->is('post')){
			if ($this->validation->withRequest($this->request)->run()) {
				$DATETIME = 'DATETIME';
				if ($this->db->DBDriver === 'postgre')
					$DATETIME = 'TIMESTAMP';


				// Use InnoDB engine for MySql database
				if ($this->db->DBDriver === 'mysql' || $this->db->DBDriver === 'mysqli')
					$this->db->query('SET storage_engine=InnoDB;');

				// Creating Tables:
				// sessions, submissions, assignments, notifications, problems, queue, scoreboard, settings, users


				// create table 'sessions'
				$fields = [
					'id'    => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
					'ip_address'    => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
					'timestamp'    => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP'), 'null' => false],
					'data'     => ['type' => 'blob', 'null' => false],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('id', TRUE); // PRIMARY KEY
				try{
					if ( ! $this->dbforge->createTable('sessions', TRUE))
					throw new \Exception("Error creating database table ".$this->db->prefixTable('sessions'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// create table 'submissions'
				$fields = [
					'submit_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
					'username'      => ['type' => 'VARCHAR', 'constraint' => 20],
					'assignment'    => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'problem'       => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'is_final'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
					'time'          => ['type' => $DATETIME],
					'status'        => ['type' => 'VARCHAR', 'constraint' => 100],
					'pre_score'     => ['type' => 'INT', 'constraint' => 11],
					'coefficient'   => ['type' => 'VARCHAR', 'constraint' => 6],
					'file_name'     => ['type' => 'VARCHAR', 'constraint' => 30],
					'main_file_name'=> ['type' => 'VARCHAR', 'constraint' => 30],
					'file_type'     => ['type' => 'VARCHAR', 'constraint' => 6],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey(['assignment', 'submit_id']);
				try{
					if ( ! $this->dbforge->createTable('submissions', TRUE))
					throw new \Exception("Error creating database table ".$this->db->prefixTable('submissions'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// create table 'assignments'
				$fields = [
					'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
					'name'          => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => ''],
					'problems'      => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'total_submits' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
					'open'          => ['type' => 'TINYINT', 'constraint' => 1],
					'scoreboard'    => ['type' => 'TINYINT', 'constraint' => 1],
					'javaexceptions'=> ['type' => 'TINYINT', 'constraint' => 1],
					'description'   => ['type' => 'TEXT'],
					'start_time'    => ['type' => $DATETIME],
					'finish_time'   => ['type' => $DATETIME],
					'extra_time'    => ['type' => 'INT', 'constraint' => 11],
					'late_rule'     => ['type' => 'TEXT'],
					'participants'  => ['type' => 'TEXT'],
					'moss_update'   => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Never'],
					'archived_assignment'          => ['type' => 'TINYINT', 'constraint' => 1],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('id', TRUE); // PRIMARY KEY
				try{
					if ( ! $this->dbforge->createTable('assignments', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('assignments'));
				}catch(Exception $e){
					exit($e->getMessage());
				}

				// create table 'notifications'
				$fields = [
					'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
					'title'         => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
					'text'          => ['type' => 'TEXT'],
					'time'          => ['type' => $DATETIME],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('id', TRUE); // PRIMARY KEY
				try{
					if ( ! $this->dbforge->createTable('notifications', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('notifications'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// create table 'problems'
				$fields = [
					'assignment'        => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'id'                => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'name'              => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => ''],
					'score'             => ['type' => 'INT', 'constraint' => 11],
					'is_upload_only'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '0'],
					'c_time_limit'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 500],
					'python_time_limit' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 1500],
					'java_time_limit'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 2000],
					'memory_limit'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 50000],
					'allowed_languages' => ['type' => 'TEXT'],
					'diff_cmd'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'diff'],
					'diff_arg'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '-bB'],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey(['assignment', 'id']);
				try{
					if ( ! $this->dbforge->createTable('problems', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('problems'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// create table 'queue'
				$fields = [
					'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
					'submit_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
					'username'          => ['type' => 'VARCHAR', 'constraint' => 20],
					'assignment'        => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'problem'           => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'type'              => ['type' => 'VARCHAR', 'constraint' => 8],
				];
				$this->dbforge->addKey('id', TRUE); // PRIMARY KEY
				$this->dbforge->addField($fields);
				try{
					if ( ! $this->dbforge->createTable('queue', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('queue'));
				}catch(Exception $e){
					exit($e->getMessage());
				}

				//Add UNIQUE (submit_id, username, assignment, problem) constraint
				$this->db->query(
					"ALTER TABLE {$this->db->prefixTable('queue')}
					ADD CONSTRAINT {$this->db->prefixTable('suap_unique')} UNIQUE (submit_id, username, assignment, problem);"
				);



				// create table 'scoreboard'
				$fields = [
					'assignment'        => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE],
					'scoreboard'        => ['type' => 'TEXT'],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('assignment');
				try{
					if ( ! $this->dbforge->createTable('scoreboard', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('scoreboard'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// create table 'settings'
				$fields = [
					'shj_key'        => ['type' => 'VARCHAR', 'constraint' => 50],
					'shj_value'      => ['type' => 'TEXT'],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('shj_key');
				try{
					if ( ! $this->dbforge->createTable('settings', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('settings'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// insert default settings to table 'settings'
				$result = [
					['shj_key' => 'timezone',               'shj_value' => 'Asia/Jakarta'],
					['shj_key' => 'tester_path',            'shj_value' => dirname(__FILE__, 3) . "/restricted/tester"],
					['shj_key' => 'assignments_root',       'shj_value' => dirname(__FILE__, 3) . "/restricted/assignments"],
					['shj_key' => 'file_size_limit',        'shj_value' => '50'],
					['shj_key' => 'output_size_limit',      'shj_value' => '1024'],
					['shj_key' => 'queue_is_working',       'shj_value' => '0'],
					['shj_key' => 'default_late_rule',      'shj_value' => "/* \n * Put coefficient (from 100) in variable \$coefficient.\n * You can use variables \$extra_time and \$delay.\n * \$extra_time is the total extra time given to users\n * (in seconds) and \$delay is number of seconds passed\n * from finish time (can be negative).\n *  In this example, \$extra_time is 172800 (2 days):\n */\n\nif (\$delay<=0)\n  // no delay\n  \$coefficient = 100;\n\nelseif (\$delay<=3600)\n  // delay less than 1 hour\n  \$coefficient = ceil(100-((30*\$delay)/3600));\n\nelseif (\$delay<=86400)\n  // delay more than 1 hour and less than 1 day\n  \$coefficient = 70;\n\nelseif ((\$delay-86400)<=3600)\n  // delay less than 1 hour in second day\n  \$coefficient = ceil(70-((20*(\$delay-86400))/3600));\n\nelseif ((\$delay-86400)<=86400)\n  // delay more than 1 hour in second day\n  \$coefficient = 50;\n\nelseif (\$delay > \$extra_time)\n  // too late\n  \$coefficient = 0;"],
					['shj_key' => 'enable_easysandbox',     'shj_value' => '1'],
					['shj_key' => 'enable_c_shield',        'shj_value' => '1'],
					['shj_key' => 'enable_cpp_shield',      'shj_value' => '1'],
					['shj_key' => 'enable_py2_shield',      'shj_value' => '1'],
					['shj_key' => 'enable_py3_shield',      'shj_value' => '1'],
					['shj_key' => 'enable_java_policy',     'shj_value' => '1'],
					['shj_key' => 'enable_log',             'shj_value' => '1'],
					['shj_key' => 'submit_penalty',         'shj_value' => '300'],
					['shj_key' => 'enable_registration',    'shj_value' => '0'],
					['shj_key' => 'registration_code',      'shj_value' => '0'],
					['shj_key' => 'mail_from',              'shj_value' => 'no-reply+shj@labftis.net'],
					['shj_key' => 'mail_from_name',         'shj_value' => 'Judge from FTIS Administrator'],
					['shj_key' => 'reset_password_mail',    'shj_value' => "<p>\nSomeone requested a password reset for your SharIF Judge account at {SITE_URL}.\n</p>\n<p>\nTo change your password, visit this link:\n</p>\n<p>\n<a href=\"{RESET_LINK}\">Reset Password</a>\n</p>\n<p>\nThe link is valid for {VALID_TIME}. If you don't want to change your password, just ignore this email.\n</p>"],
					['shj_key' => 'add_user_mail',          'shj_value' => "<p>\nHello! You are registered in SharIF Judge at {SITE_URL} as {ROLE}.\n</p>\n<p>\nYour username: {USERNAME}\n</p>\n<p>\nYour password: {PASSWORD}\n</p>\n<p>\nYou can log in at <a href=\"{LOGIN_URL}\">{LOGIN_URL}</a>\n</p>"],
					['shj_key' => 'moss_userid',            'shj_value' => ''],
					['shj_key' => 'results_per_page_all',   'shj_value' => '40'],
					['shj_key' => 'results_per_page_final', 'shj_value' => '80'],
					['shj_key' => 'week_start',             'shj_value' => '0'],
					['shj_key' => 'lock_student_display_name',      'shj_value' => '0'],
				];
				$builder = $this->db->table('settings');
				try{
					if ( ! $builder->insertBatch($result))
						throw new \Exception("Error adding data to table ".$this->db->prefixTable('settings'));
				}catch(Exception $e){
					exit($e->getMessage());
				}


				// create table 'users'
				$fields = [
					'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
					'username'            => ['type' => 'VARCHAR', 'constraint' => 20],
					'password'            => ['type' => 'VARCHAR', 'constraint' => 100],
					'display_name'        => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => ''],
					'email'               => ['type' => 'VARCHAR', 'constraint' => 40],
					'role'                => ['type' => 'VARCHAR', 'constraint' => 20],
					'passchange_key'      => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => ''],
					'passchange_time'     => ['type' => $DATETIME, 'null' => TRUE],
					'first_login_time'    => ['type' => $DATETIME, 'null' => TRUE],
					'last_login_time'     => ['type' => $DATETIME, 'null' => TRUE],
					'selected_assignment' => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => TRUE, 'default' => 0],
					'dashboard_widget_positions'   => ['type' => 'VARCHAR', 'constraint' => 500, 'default' => ''],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('id', TRUE); // PRIMARY KEY
				$this->dbforge->addKey('username'); // @todo is this needed?
				try{
					if ( ! $this->dbforge->createTable('users', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('users'));
				}catch(Exception $e){
					exit($e->getMessage());
				}
			
				// create table 'logins'
				$fields = [
					'login_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
					'username'         		=> ['type' => 'VARCHAR', 'constraint' => 20],
					'ip_address'          => ['type' => 'VARCHAR', 'constraint' => 15],
					'timestamp'          	=> ['type' => 'TIMESTAMP'],
					'last_24h_login_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
				];
				$this->dbforge->addField($fields);
				$this->dbforge->addKey('login_id', TRUE); // PRIMARY KEY
				try{
					if ( ! $this->dbforge->createTable('logins', TRUE))
						throw new \Exception("Error creating database table ".$this->db->prefixTable('logins'));
				}catch(Exception $e){
					exit($e->getMessage());
				}

				// add admin user
				$this->user_model->add_user(
					$this->request->getPost('username'),
					$this->request->getPost('email'),
					'Admin',
					$this->request->getPost('password'),
					'admin',
				);
				$data['installed'] = TRUE;
			}
		
			// Using a random string as encryption key
			$config_path = rtrim(APPPATH,'/').'/Config/Encryption.php';
			$config_content = file_get_contents($config_path);
			$random_key = random_string('alnum', 32);
			$res = @file_put_contents($config_path, str_replace(config('Encryption')->key, $random_key, $config_content));
			if ($res === FALSE)
				$data['key_changed'] = FALSE;
			else
				$data['key_changed'] = TRUE;
		}
			$data['enc_key'] = config('Encryption')->key;
			$data['random_key'] = random_string('alnum', 32);

		return view('pages/admin/install', $data);

	}
}
