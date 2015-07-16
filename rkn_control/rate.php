<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}
class rate
{
	public function init()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}	
	}
	
	public function idx()
	{
		$this->rknclass->db->query("SELECT rating FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->message('Error', 'Invalid content id! If you followed a valid link, please alert site administration'));
		}
		
		$rating=$this->rknclass->db->result();
		
		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "ratings WHERE plug_id='". $this->rknclass->get['id'] . "' AND ip='" . $_SERVER['REMOTE_ADDR'] . "' LIMIT 1");
		
		if($this->rknclass->db->result()>0)
		{
			exit('<font color="#FF0000">' . $rating . '</font>');
		}
		
		if($this->rknclass->get['rating_type'] === 'yes')
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET rating=rating+1 WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
			$rating=$rating+1;
		}
		
		else
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET rating=rating-1 WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
			$rating=$rating-1;
		}
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "ratings set plug_id='" . $this->rknclass->get['id'] . "', ip='" . $_SERVER['REMOTE_ADDR'] . "', time='" . time() . "'");

		echo $rating;
	}
}
?>