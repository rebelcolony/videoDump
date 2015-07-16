<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class forgot_password
{
	public $allowed;
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('mail'));
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		$this->rknclass->page_title='Password Reset';
		$this->rknclass->tpl->preload(array('header', 'footer', 'forgot_password'));
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('action', $this->rknclass->settings['site_url'] . '/index.php?ctr=forgot_password&amp;act=request', 'forgot_password', 'forgot_password');
		$this->rknclass->tpl->parse('username', '<input type="text" name="username" class="form-input" />', 'forgot_password');
		$this->rknclass->tpl->process('forgot_password');
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	public function request()
	{
		if($this->rknclass->post['username'] == '')
		{
			exit($this->rknclass->message('Error', 'One or more fields were left blank!'));
		}
		
		$this->rknclass->db->query("SELECT email FROM " . TBLPRE . "users WHERE username='{$this->rknclass->post['username']}' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->message('Error', 'User not found!'));
		}
		
		$email_address=$this->rknclass->db->result();
		
		$reset_key=$this->rknclass->utils->rand_chars(25);
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET reset_key='$reset_key' WHERE username='{$this->rknclass->post['username']}' LIMIT 1");
		
		
		/*========================
		Sends the user an email
		with a link containing
		the previously generated
		password reset key which
		will use the process_key
		method to generate a new
		password.
		==========================*/
		
		
		$this->rknclass->mail->send($email_address,
									$this->rknclass->settings['admin_email'],
									'Forgot password request on ' . $this->rknclass->settings['site_name'],
									"Hey {$this->rknclass->post['username']},
									
Please visit the link below to get a new password for your account:
									
{$this->rknclass->settings['site_url']}/index.php?ctr=forgot_password&act=process_key&key=$reset_key
									
Thanks!");
		$this->rknclass->message('Success', 'An email containing details on how to reset your password has been sent!');
		exit;
	}
	
	public function process_key()
	{
		if($this->rknclass->get['key'] == '' || strlen($this->rknclass->get['key'])!==25)
		{
			exit($this->rknclass->message('Error', 'Invalid password reset key. If you followed a valid link, please alert the site administrator'));
		}
		
		$this->rknclass->db->query("SELECT username,firstname FROM " . TBLPRE . "users WHERE reset_key='{$this->rknclass->get['key']}'");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->message('Error', 'Invalid password reset key. If you followed a valid link, please alert the site administrator'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		$salt=$this->rknclass->utils->rand_chars(5);
		$pass=$this->rknclass->utils->rand_chars(7);
		
		$new=$this->rknclass->utils->pass_hash($pass, $salt);
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET reset_key=NULL, password='$new', salt='$salt' WHERE username='{$row['username']}' LIMIT 1");
		
		if($row['firstname'] == '')
		{
			$name=$row['username'];
		}
		
		else
		{
			$name=$row['firstname'];
		}
		
		$this->rknclass->message('Success', "Welcome back <strong>$name</strong>, your password has been reset successfully!<br />Your new password is <strong>$pass</strong>");
	}
}

?>