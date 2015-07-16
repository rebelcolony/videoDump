<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class filter
{
	public $allowed;
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('plugs', 'cache', 'p3_archive'));
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		//There is no reason for this method to be accessed
		exit(header("Location: {$this->rknclass->settings['site_url']}"));
	}
	
	public function tag()
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
		
		if(strlen($this->rknclass->get['tag_name']) < 3)
		{
			if($this->rknclass->debug === true)
			{
				exit($this->rknclass->throw_debug_message('Process aborted due to tag length of under 3 characters'));
			}
			exit(header($this->rknclass->settings['site_url']));
		}
		
		define('PAGER_URL', $this->rknclass->settings['site_url'] . '/tag/' . $this->rknclass->get['tag_name'] . '/');
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "tags SET tag_views=tag_views+1 WHERE tag_word='{$this->rknclass->get['tag_name']}' LIMIT 1");
		$this->rknclass->page_title='Home';
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs', 'pagination'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND posted<" . time() . " AND tags LIKE '%{$this->rknclass->get['tag_name']}%' AND type IN($type_list)ORDER BY plug_id DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		$this->rknclass->tpl->auto_parser('footer');	
	}
	
	public function category()
	{
		define('PAGER_URL', $this->rknclass->settings['site_url'] . '/cat/' . $this->rknclass->get['cat_name'] . '/');
		
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
				
		if($this->rknclass->get['cat_name'] == '' || $this->rknclass->get['cat_name'] === false)
		{
			if($this->rknclass->debug === true)
			{
				exit($this->rknclass->throw_debug_message('Category url is invalid'));
			}
			exit(header($this->rknclass->settings['site_url']));
		}
		
		$category=$this->rknclass->utils->url_original($this->rknclass->get['cat_name']);
		
		$this->rknclass->page_title=$category;
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs', 'pagination'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND posted<" . time() . " AND category='$category' AND type IN($type_list) ORDER BY plug_id DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		$this->rknclass->tpl->auto_parser('footer');	
	}
	
	public function videos()
	{
		$this->rknclass->page_title='Video Content';
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs', 'pagination'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND (type='2' OR type='3') AND posted<" . time() . " ORDER BY posted DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		$this->rknclass->tpl->auto_parser('footer');	
	}
	
	public function blogs()
	{
		$this->rknclass->page_title='Article Listings';
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs','pagination'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND type='5' AND posted<" . time() . " ORDER BY posted DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		$this->rknclass->tpl->auto_parser('footer');	
	}
}

?>