<?php
/*======================================
Predator CMS 1.x
Copyright 2008 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class index
{
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('plugs'));
	}
	//END INIT FUNCTION
		
	public function idx()
	{
		$types = unserialize($this->rknclass->settings['listing_types']);
		
		$first = null;
		
		$type_list = null;
		
		foreach($types as $key => $value)
		{
			if($first === null)
			{
				$type_list .= "$value";
				$first      = true;
			}
			else
			{
				$type_list .= ",$value";
			}
		}
		
		$this->rknclass->page_title='Home';
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs', 'pagination'));
		
		$this->rknclass->tpl->auto_parser('header');
		
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');	
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND posted<" . $this->rknclass->utils->content_schedule() . " AND type IN($type_list) ORDER BY posted DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		
		$this->rknclass->tpl->auto_parser('footer');
	}
}
?>