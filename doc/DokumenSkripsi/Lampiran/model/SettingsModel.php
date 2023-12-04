<?php
/**
 * SharIF Judge online judge
 * @file Settings_model.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Models;
use CodeIgniter\Model;

/**
 * This model deals with global settings
 */

class SettingsModel extends Model
{


	public function __construct()
	{
		parent::__construct();
	}


	// ------------------------------------------------------------------------


	public function get_setting($key)
	{
		return $this->db->table('settings')->select('shj_value')->getWhere(['shj_key'=>$key])->getRow()->shj_value;
	}


	// ------------------------------------------------------------------------


	public function set_setting($key, $value)
	{
		$this->db->table('settings')->where('shj_key', $key)->update(array('shj_value'=>$value));
	}


	// ------------------------------------------------------------------------


	public function get_all_settings()
	{
		$result = $this->db->table('settings')->get()->getResultArray();
		$settings = array();
		foreach($result as $item)
		{
			$settings[$item['shj_key']] = $item['shj_value'];
		}
		return $settings;
	}


	// ------------------------------------------------------------------------


	public function set_settings($settings)
	{
		foreach ($settings as $key => $value)
		{
			$this->set_setting($key, $value);
		}
	}



}