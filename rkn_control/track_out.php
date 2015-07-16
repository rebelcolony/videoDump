<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}
class track_out
{
	public function init()
	{
		//not required
	}
	
	public function idx()
	{
		if(empty($this->rknclass->get['trade_url']))
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		
		else
		{
			$this->rknclass->tracker->track_out($this->rknclass->utils->rkn_url_parser($this->rknclass->get['trade_url']));
			header("Location: {$this->rknclass->get['trade_url']}");
		}
	}
}
?>