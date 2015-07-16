<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class feeds
{
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('feeds'));
		header("Content-type: application/rss+xml");
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		echo $this->rknclass->feeds->generate_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND posted<" . time() . " AND type > 1 ORDER BY plug_id DESC LIMIT 25");
	}

	public function rss()
	{
		echo $this->rknclass->feeds->generate_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND posted<" . time() . " AND type > 1 ORDER BY plug_id DESC LIMIT 25");
	}
	
	public function atom()
	{
		echo $this->rknclass->feeds->generate_atom_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND posted<" . time() . " AND type > 1 ORDER BY plug_id DESC LIMIT 25");
	}

	public function rss_videos()
	{
		echo $this->rknclass->feeds->generate_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND (type='2' OR type='3') AND posted<" . time() . " ORDER BY plug_id DESC LIMIT 25");
	}
	
	public function atom_videos()
	{
		echo $this->rknclass->feeds->generate_atom_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND (type='2' OR type='3') AND posted<" . time() . " ORDER BY plug_id DESC LIMIT 25");
	}
	
	public function rss_blogs()
	{
		echo $this->rknclass->feeds->generate_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND type='5' AND posted<" . time() . " ORDER BY plug_id DESC LIMIT 25");
	}
	
	public function atom_blogs()
	{
		echo $this->rknclass->feeds->generate_atom_from_query("SELECT * FROM " . TBLPRE . "plugs WHERE approved='1' AND type='5' AND posted<" . time() . " ORDER BY plug_id DESC LIMIT 25");
	}
}

?>