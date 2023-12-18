<?php

namespace Config;

use App\Models\SettingsModel;
use App\Models\User;
use App\Models\UserModel;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        MyRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];
    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------
}  

class MyRules
{   
    public function password_check($str): bool
    {
        if (strlen($str) == 0 OR (strlen($str) >= 6 && strlen($str) <= 200))
			return TRUE;
		return FALSE;
    }

    public function password_again_check($str) :bool
    {
        $request = \Config\Services::request();
        if ($request->getPost('password') !== $request->getPost('password_again'))
			return FALSE;
		return TRUE;
    }

    public function role_check($role) 
    {
        $user = new User();
        if ($user->level <= 2)
			return ($role == '');

		// Admins can change everybody's user role:
		$roles = array('admin', 'head_instructor', 'instructor', 'student');
		return in_array($role, $roles);
    }

    /**
	 * Checks whether a user with this email exists
	 */
	public function email_check($email, $edit_username):bool
	{
		$edit_username = explode(',', $edit_username);
        $user_model = new UserModel();
		if ($user_model->have_email($email, $edit_username[1]))
			return FALSE;
		return TRUE;
	}

    /**
	 * checks whether the entered registration code is correct or not
	 *
	 */
	public function _registration_code($code){
        $settings_model = new SettingsModel();
		$rc = $settings_model->get_setting('registration_code');
		if ($rc == '0')
			return TRUE;
		if ($rc == $code)
			return TRUE;
		return FALSE;
	}

    /**
	 * Required
	 *
	 * @param	string
	 * @return	bool
	 */
	public function required($str)
	{
		return is_array($str) ? (bool) count($str) : ($str !== '');
	}


	// -------------------------------------------------------------------------


	/**
	 * Is Lowercase
	 *
	 * @param $str
	 * @return bool
	 */
	public function lowercase($str)
	{
		return (strtolower($str) === $str);
	}

    	// ------------------------------------------------------------------------


	public function _check_language($str)
	{
		if ($str=='0')
			return FALSE;
		if (in_array( strtolower($str),array('c', 'c++', 'python 2', 'python 3', 'java', 'zip', 'pdf', 'txt')))
			return TRUE;
		return FALSE;
	}


	// ------------------------------------------------------------------------
	// Used in Submissions.php

	public function _check_type($type)
	{
		return ($type === 'code' || $type === 'result' || $type === 'log');
	}
}
