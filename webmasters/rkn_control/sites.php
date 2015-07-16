<?php

/*

Predator CMS Webmasters Sites Controller
Copyright 2007 Ranakin Web Development
This file should not be redistributed

@ Since: 1.0.0
@ Last Updated: N/A
@ Author: Ian Cubitt 
@ Email: inspiretheweb@googlemail.com

*/

if(!defined('IN_RKN_PREDATOR'))
{
	echo "<strong>Access Denied</strong>";
	exit;
}

class sites extends rkn_render
{
	public $allowed;
	public $rknclass;

	//BEGIN INIT FUNCTION
	public function init()
	{
		if($this->rknclass->session->is_guest === true)
		{
			if($this->rknclass->get['ajax'] == '1')
			{
				exit('<strong><font color="red">Session Expired!</strong> - Please login again<br /><br /><strong>TIP:</strong> If you have just spent a while filing out a form, open a new browser window/tab, login and then go back to this window/tab and hit submit</font>');
			}
			else
			{
				exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
			}
		}
		
		$this->rknclass->load_objects(array('global_tpl', 'form'));
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&act=add_site"));
	}
	
	public function add_site()
	{
		$this->rknclass->page_title='Add a new site';
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->form->new_form('Add a new website');
		$this->rknclass->form->set_action('index.php?ctr=sites&amp;act=process_new_site');
		$this->rknclass->form->add_input('site_name', 'input', 'Website Title', 'Enter the name/title of the website. This is displayed to other webmasters, so please enter something appropriate. This value must be unique');
		$this->rknclass->form->add_input('site_url', 'input', 'Website url', 'Enter the url of the website. This <u>must</u> start with <strong>http://</strong>', 'http://');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function process_new_site()
	{
		if($this->rknclass->post['site_name'] == '' OR $this->rknclass->post['site_url'] == '')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		$url=$this->rknclass->utils->rkn_url_parser($this->rknclass->post['site_url']);
		
		if($url === false)
		{
			exit($this->rknclass->form->ajax_error('Invalid url: urls must start with http://!'));
		}
		
		if($this->rknclass->utils->valid_url_structure($this->rknclass->post['site_url']) !== true)
		{
			exit($this->rknclass->form->ajax_error('Invalid url: urls must start with http://!'));
		}
		
		$this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "banned_sites WHERE url='{$url}'");
		if($this->rknclass->db->num_rows() > 0 AND $this->rknclass->db->result() > 0)
		{
		    exit($this->rknclass->form->ajax_error('This site is banned from submitting!'));
		}
		
		$this->rknclass->db->query("SELECT url,owner FROM " . TBLPRE . "sites WHERE url='$url' OR name='{$this->rknclass->post['site_name']}'");
		
		$assign_owner = false;
		if($this->rknclass->db->num_rows()>0)
		{
			$row = $this->rknclass->db->fetch_array();
			
			if($row['owner'] > 0)
			{
				exit($this->rknclass->form->ajax_error('This site has already been added by a user to our trade system!<br />Please check to ensure your site name is also unique!'));
			}
			else
			{
				$assign_owner = true;
			}
		}
		
		if($assign_owner === false)
		{
			$query=$this->rknclass->db->build_query(array('insert' => 'sites',
		                                              'set' => array('url' => $url, 
													                 'name' => $this->rknclass->post['site_name'],
											                         'owner' => $this->rknclass->user['user_id'],
																	 'approved' => $this->rknclass->settings['trade_default_status'],
																	 'joined' => time())));
		}
		else
		{
			$query=$this->rknclass->db->build_query(array('update' => 'sites',
		                                              'set' => array('name' => $this->rknclass->post['site_name'],
											                         'owner' => $this->rknclass->user['user_id'],
																	 'approved' => $this->rknclass->settings['trade_default_status'],
																	 'joined' => time()),
		                                              'where' => array('url' => $url),
													  'limit' => '1'));			
		}
		$this->rknclass->db->query($query);
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites=total_sites+1 WHERE user_id='". $this->rknclass->user['user_id'] . "'");
		$this->rknclass->form->ajax_success('Successfully added site!');
	}
	
	public function edit_site()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		$this->rknclass->db->query("SELECT name,owner,url FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid Site - This site does not exist in our trade system. If you followed a valid link, please report this error to administration'));
		}
		$row=$this->rknclass->db->fetch_array();
		if($row['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot manage this site as you are not its owner.'));
		}
		$this->rknclass->page_title='Edit site ' . $row['name'] . '';
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->form->new_form('Edit site ' . $row['name'] . '');
		$this->rknclass->form->set_action('index.php?ctr=sites&amp;act=update_site&amp;id=' . $this->rknclass->get['id'] . '');
		$this->rknclass->form->add_input('site_name', 'input', 'Website Title', 'Enter the name/title of the website. This is displayed to other webmasters, so please enter something appropriate. This value must be unique', $row['name']);
		$this->rknclass->form->add_input('site_url', 'input-readonly', 'Website url', 'Enter the url of the website. This <u>must</u> start with <strong>http://</strong><br /><br /><strong><font color="#FF0000">You cannot change this value!</font></strong>', 'http://' . $row['url']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function update_site()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		
		$this->rknclass->db->query("SELECT name,owner,url FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->form->ajax_error('Invalid Site - This site does not exist in our trade system. If you followed a valid link, please report this error to administration'));
		}
		$row=$this->rknclass->db->fetch_array();
		
		if($row['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->form->ajax_error('You cannot manage this site as you are not its owner.'));
		}
		
		if($this->rknclass->post['site_name']=='')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		if($this->rknclass->post['site_name']==$row['name'])
		{
			exit($this->rknclass->form->ajax_error('Site name is the same as before'));
		}
		
		$this->rknclass->db->query("SELECT url FROM " . TBLPRE . "sites WHERE name='{$this->rknclass->post['site_name']}'");
		
		if($this->rknclass->db->num_rows()>0)
		{
			exit($this->rknclass->form->ajax_error('There\'s another site in the system with the same name. Please pick another.'));
		}	
			
		$query=$this->rknclass->db->build_query(array('update' => 'sites',
		                                              'set' => array('name' => $this->rknclass->post['site_name']),
													  'where' => array('site_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		$this->rknclass->form->ajax_success('Successfully updated site!');
	}
	
	
	/*============================
	Method below is for deleting
	sites. We require the user to
	re-enter their password as
	it would seriously suck if
	something bad happened by
	accident!
	=============================*/
	
	
	public function del_site()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		$this->rknclass->db->query("SELECT owner,name FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid Site - This site does not exist in our trade system. If you followed a valid link, please report this error to administration'));
		}
		$row=$this->rknclass->db->fetch_array();
		if($row['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot delete this site as you are not its owner.'));
		}
		$this->rknclass->page_title='Delete site ' . $row['name'] . '';
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->form->new_form('Confirm deletion of site ' . $row['name'] . '');
		$this->rknclass->form->set_action('index.php?ctr=sites&amp;act=process_site_removal&amp;id=' . $this->rknclass->get['id'] . '');
		$this->rknclass->form->add_input('password', 'password', 'Password', 'Please enter your password to continue. <br /><br /><strong>Remember, this action cannot be undone!</strong>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function process_site_removal()
	{
		if($this->rknclass->get['id']=='')
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		
		$this->rknclass->db->query("SELECT owner FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->form->ajax_error('Invalid Site - This site does not exist in our trade system. If you followed a valid link, please report this error to administration'));
		}
		$row=$this->rknclass->db->fetch_array();
		
		if($row['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->form->ajax_error('You cannot manage this site as you are not its owner.'));
		}
		
		if(empty($this->rknclass->post['password']))
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		if($this->rknclass->utils->pass_hash($this->rknclass->post['password'], $this->rknclass->user['salt'])!==$this->rknclass->user['password'])
		{
			exit($this->rknclass->form->ajax_error('Incorrect password'));
		}
		
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "feeds WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites=total_sites-1 WHERE user_id='{$this->rknclass->user['user_id']}' LIMIT 1");
		$this->rknclass->form->ajax_success('Successfully removed site!');	
	}
}
?>