<?php
define('RKN__admintab', 'security');
class security extends rkn_render
{
	//BEGIN INIT FUNCTION	
	public function init()
	{
		if(!defined('IN_RKN_PREDATOR'))
		{
			echo "<strong>Access Denied</strong>";
			exit;
		}
		
		$this->rknclass->load_objects(array('global_tpl', 'form'));
		
		if($this->rknclass->session->is_guest === false AND $this->rknclass->user['group']['is_admin'] !== '1')
		{
			exit($this->rknclass->global_tpl->admin_error('You must be an admin to access this page'));
		}
		
		if($this->rknclass->session->is_guest === true)
		{
			if($this->rknclass->get['return_url'] == '')
			{
				$this->rknclass->get['return_url']=$this->rknclass->settings['site_url'] . '/' . RKN__adminpath . '/index.php';
			}
				exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
		}
		
	    if($this->rknclass->user['group']['is_restricted'] == '1')
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to access this area!'));
		}
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		$this->comments_settings();
	}
	
	public function comments_settings()
	{
		$this->rknclass->page_title='Manage Comments Security Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		
		if($row['comments_captcha'] == '1')
		{
			$comments_captcha='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$comments_captcha='<option value="0">No</option><option value="1">Yes</option>';
		}
		
		if($row['comments_flood_control'] == '1')
		{
			$flood_control='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$flood_control='<option value="0">No</option><option value="1">Yes</option>';
		}
		
		$this->rknclass->form->new_form('Manage Comments Security');
		$this->rknclass->form->set_action('index.php?ctr=security&amp;act=update_comments');
		$this->rknclass->form->add_input('comments_captcha', 'dropdown', 'Enable Captcha', 'If set to no, the captcha system will be disabled regardless of the individual group permission settings', $comments_captcha);
		$this->rknclass->form->add_input('comments_flood_control', 'dropdown', 'Flood Control', 'Select whether or not you wish to use flood control for the comments system', $flood_control);;
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function update_comments()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
		$fields=array('comments_captcha', 'comments_flood_control');
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		$this->rknclass->cache->admin_update_settings($fields);
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}
	
	public function login_settings()
	{
		$this->rknclass->page_title='Manage Login Security Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		
		if($row['login_strikes'] == '1')
		{
			$login_strikes='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$login_strikes='<option value="0">No</option><option value="1">Yes</option>';
		}
		
		$this->rknclass->form->new_form('Manage Login Security');
		$this->rknclass->form->set_action('index.php?ctr=security&amp;act=update_login');
		$this->rknclass->form->add_input('login_strikes', 'dropdown', 'Enable Login Strikes', 'If set to yes, an account will be locked for 15 minutes when x amount of failed login attempts are performed on the account', $login_strikes);
		$this->rknclass->form->add_input('login_strikes_allowed', 'input', 'Login Strikes Count', 'If you are using the login strikes system above, enter the amount of failed login attempts that are allowed before locking an account', $row['login_strikes_allowed']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function update_login()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
		$fields=array('login_strikes', 'login_strikes_allowed');
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		$this->rknclass->cache->admin_update_settings($fields);
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}
	
	public function registration_settings()
	{
		$this->rknclass->page_title='Manage Registration Security Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		
		if($row['email_validation'] == '1')
		{
			$email_validation='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$email_validation='<option value="0">No</option><option value="1">Yes</option>';
		}
		
		$this->rknclass->form->new_form('Manage Registration System Security');
		$this->rknclass->form->set_action('index.php?ctr=security&amp;act=update_register');
		$this->rknclass->form->add_input('email_validation', 'dropdown', 'Require Email Validation', 'If set to yes, new accounts will be sent an email containing a verification link, to ensure their email address is valid', $email_validation);
		$this->rknclass->form->add_input('banned_usernames', 'textarea', 'Banned Usernames', 'Enter a list of usernames you don\'t want users to be able to register using.<br /><br />If, for example, you entered the word <em>admin</em>, any username containg that word would be blocked, so the username <em>administrator</em> would also be unacceptable.<br /><br /><strong>Seperate by new lines.</strong>', $row['banned_usernames']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function update_register()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		$fields=array('email_validation', 'banned_usernames');
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		$token=strtok($this->rknclass->post['banned_usernames'], "\n");
		while($token !== false)
  		{
			if(strlen($token)<3)
			{
				exit($this->rknclass->form->ajax_error($token . ' is an invalid username! Banned usernames must be at least 3 characters long'));
			}
  			$token=strtok("\n");
  		}
		$this->rknclass->cache->admin_update_settings($fields);
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}
}
?>