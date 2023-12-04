<?php
/**
 * SharIF Judge online judge
 * @file Logs_model.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Models;
use CodeIgniter\Model;

class LogsModel extends Model
{

	public function __construct()
	{
		parent::__construct();
	}


	// ------------------------------------------------------------------------


	/**
	 * Mencatat Logs (Tabel shj_logins)
	 *
	 *
	 *
	 */
	public function insert_to_logs($username, $ip_adrress)
	{
		$query = $this->db->table('logins')->get()->getResultArray();

		//menghapus timestamp user yang lebih dari 24 jam
		foreach ($query as $row)
		{
			$temptime=strtotime('+24 hour', strtotime($row['timestamp']));
			if ($temptime < shj_now()) {
				# delete
				$builder = $this->db->table('logins');
				$builder->where('timestamp', $row['timestamp']);
				$builder->delete();
			}
		}

		$result = $this->db->query("SELECT * FROM shj_logins WHERE username='$username' AND ip_address!='$ip_adrress' ORDER BY timestamp DESC")->getRow();
		if ($result === NULL) {
			$logins = array(
				'username' => $username,
				'ip_address' => $ip_adrress
			);
			$this->db->table('logins')->insert($logins);
		}
		else {
			$get_last_login_id = $result->login_id;
			$logins = array(
				'username' => $username,
				'ip_address' => $ip_adrress,
				'last_24h_login_id' => $get_last_login_id
			);
			$this->db->table('logins')->insert($logins);
		}
	}


  // ------------------------------------------------------------------------


	/**
	 * Get All Logs
	 *
	 * Returns an array of all logs
	 *
	 * @return mixed
	 */
	public function get_all_logs()
	{
		return $this->db->table('logins')->orderBy('login_id', 'desc')->get()->getResultArray();
	}


	// ------------------------------------------------------------------------
}
