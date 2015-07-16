<?php

/**
 * Predator CMS 1.1.0
 * @copyright 2008 Ranakin Web Development
 */
 
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class ad_banner
{
	public $rknclass;
	
	public function init()
	{
		$this->rknclass->load_objects(array('mail'));
		$this->rknclass->tpl->preload(array('header', 'footer', 'register'));
	}
	
	public function idx()
	{
		if(!ctype_digit((string)$this->rknclass->get['banner_id']) || !ctype_digit((string)$this->rknclass->get['sponsor_id']))
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/index.php"));
		}
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_ads SET ad_clicks=ad_clicks+1 WHERE ad_id='{$this->rknclass->get['banner_id']}' LIMIT 1");
		
		$this->rknclass->db->query("SELECT banner_link_url FROM " . TBLPRE . "sponsors_banner_links WHERE sponsor_site_id='{$this->rknclass->get['sponsor_id']}' ORDER BY RAND() LIMIT 1");
		
		if($this->rknclass->db->num_rows() < 1)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/index.php"));
		}
		else
		{
			$url = $this->rknclass->db->result();
			$this->rknclass->tracker->track_productivity('2');
			header("Location: $url");
		}
	}
}
?>