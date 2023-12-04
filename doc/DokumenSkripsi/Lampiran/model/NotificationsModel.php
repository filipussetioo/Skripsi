<?php
/**
 * SharIF Judge online judge
 * @file Notifications_model.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */
namespace App\Models;
use CodeIgniter\Model;

class NotificationsModel extends Model
{

	public function __construct()
	{
		parent::__construct();
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns all notifications
	 */
	public function get_all_notifications()
	{
		return $this->db->table('notifications')
			->orderBy('id', 'desc')
			->get()
			->getResultArray();
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns 10 latest notifications
	 */
	public function get_latest_notifications()
	{
		$result = $this->db->table('notifications')
			->limit(10)
			->orderBy('id', 'desc')
			->get()
			->getResultArray();
		foreach($result as &$item)
		{
			$item['text'] = substr(trim(strip_tags($item['text'])), 0, 300);
			$item['text'] = str_replace('&zwnj;', "\xE2\x80\x8c", $item['text']);
			$item['text'] = html_entity_decode($item['text']);
			$item['text'] = preg_replace('/\r?\n|\n?\r/', ' ', $item['text']);
		}
		return $result;
	}


	// ------------------------------------------------------------------------


	/**
	 * Add a new notification
	 */
	public function add_notification($title, $text)
	{
		$now = shj_now_str();
		$this->db->table('notifications')->insert(
			array(
				'title' => $title,
				'text' => $text,
				'time' => $now
			)
		);
	}


	// ------------------------------------------------------------------------


	/**
	 * Update (edit) a notification
	 */
	public function update_notification($id, $title, $text)
	{
		$this->db->table('notifications')
			->where('id', $id)
			->update(
				array(
					'title' => $title,
					'text' => $text
				)
			);
	}


	// ------------------------------------------------------------------------


	/**
     * Delete a notification
	 */
	public function delete_notification($id)
	{
		$this->db->table('notifications')->delete(array('id' => $id));
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns a notification
	 */
	public function get_notification($notif_id)
	{
		$query = $this->db->table('notifications')->getWhere(array('id' => $notif_id));
		if ($query->getNumRows() != 1)
			return FALSE;
		return $query->getRowArray();
	}


	// ------------------------------------------------------------------------


	/**
	 * Returns true if there is a notification after $time
	 * @todo optimize: check the ">" condition in sql query
	 */
	public function have_new_notification($time)
	{
		$notifs = $this->db->table('notifications')->select('time')->get()->getResultArray();
		foreach ($notifs as $notif) {
			if (strtotime($notif['time']) > $time)
				return TRUE;
		}
		return FALSE;
	}

}