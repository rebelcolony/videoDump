<?php
class rkn_update
{
	public $rknclass;

	public function execute()
	{
		$this->rknclass->db->query("UPDATE " . TBLPRE . "settings SET version='1.6.1';");
		echo 'Updates complete.';
	}
}
?>