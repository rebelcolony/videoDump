<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class search
{

	public $rknclass;
	
	public function init()
	{
		if($this->rknclass->settings['search_disabled'] == '1')
		{
			exit($this->rknclass->message('Error', 'Search has been disabled by the site administrator'));	
		}
		
		if($this->rknclass->user['group']['can_search'] == '0')
		{
			exit($this->rknclass->message('Error', 'Your usergroup does not have permission to use the search feature!'));
		}
		
		if($this->rknclass->user['group']['search_flood_control'] !== '-1')
		{
			if($this->rknclass->session->data['next_search'] > time())
			{
				exit($this->rknclass->message('Flood Control', 'You must wait another ' . ($this->rknclass->session->data['next_search'] - time()) . ' seconds before performing a new search'));
			}
		}
		$this->rknclass->load_objects(array('plugs'));
	}
	
	public function idx()
	{		
		if(!isset($this->rknclass->get['search_id']) || empty($this->rknclass->get['search_id']))
		{
			if(strlen($this->rknclass->post['search_phrase']) < 3)
			{
				exit($this->rknclass->message('Error', 'Search string must be at least 3 characters long'));
			}
		}
		
		$this->rknclass->cache->update_session($this->rknclass->session->data['sess_id'], array('next_search' => time() + $this->rknclass->user['group']['search_flood_control']));
		
		if(!isset($this->rknclass->get['search_id']) || empty($this->rknclass->get['search_id']))
		{
			$search_id=$this->rknclass->utils->rand_chars(5);
			$this->rknclass->get['search_id']=$search_id;
			
			if(strpos($this->rknclass->post['search_phrase'], '%') !== false)
			{
				$this->rknclass->post['search_phrase']=str_replace('%', '\%', $this->rknclass->post['search_phrase']);
			}
			
			if(strpos($this->rknclass->post['search_phrase'], '_') !== false)
			{
				$this->rknclass->post['search_phrase']=str_replace('_', '\_', $this->rknclass->post['search_phrase']);
			}
			
			$this->rknclass->db->query("INSERT INTO " . TBLPRE . "searches SET search_id='$search_id', phrase='" . $this->rknclass->post['search_phrase'] ."', expires='" . (time()+900) . "'");
			$search_phrase=$this->rknclass->post['search_phrase'];
		}
		else
		{
			if(!ctype_alnum($this->rknclass->get['search_id']))
			{
				exit($this->rknclass->message('Error', 'Invalid search id'));
			}
			
			$search_id=$this->rknclass->get['search_id'];
			$this->rknclass->db->query("SELECT phrase FROM " . TBLPRE . "searches WHERE search_id='$search_id' LIMIT 1");
			if($this->rknclass->db->num_rows()<1)
			{
				exit($this->rknclass->message('Error', 'Invalid search id!'));
			}
			$search_phrase=$this->rknclass->db->result();
		}
		
		define('PAGER_URL', $this->rknclass->settings['site_url'] . '/search/' . $this->rknclass->get['search_id'] . '/');
		$this->rknclass->page_title='Search';
		$this->rknclass->tpl->preload(array('header', 'footer', 'index', 'plugs'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('page title', $this->rknclass->page_title, 'index');
		$this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND posted<" . time() . " AND (title LIKE '%{$search_phrase}%' OR description LIKE '%{$search_phrase}%') ORDER BY plug_id DESC"), 'index');
		$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'index');
		$this->rknclass->tpl->process('index');
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	public function search_form()
	{
		$this->rknclass->page_title='Search';
		$this->rknclass->tpl->preload(array('header', 'footer', 'search'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('action', $this->rknclass->settings['site_url'] . '/index.php?ctr=search', 'search');
		$this->rknclass->tpl->parse('search phrase', "<input name=\"search_phrase\" type=\"text\" class=\"form-input\">", 'search');
		$this->rknclass->tpl->process('search');
		$this->rknclass->tpl->auto_parser('footer');
	}
}
?>