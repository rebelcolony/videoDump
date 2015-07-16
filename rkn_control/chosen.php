<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class chosen
{
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('plugs', 'cache', 'p3_archive'));
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		define('RKN__controller', 'chosen');
		$this->rknclass->page_title='Chosen Content';
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND chosen='1' AND posted<" . time() . " ORDER BY posted DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		$this->rknclass->tpl->auto_parser('footer');
	}
}

?>