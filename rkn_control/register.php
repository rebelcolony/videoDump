<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class register
{
	public $allowed;
	public $rknclass;
	
	public function init()
	{
		$this->rknclass->load_objects(array('mail'));
		$this->rknclass->tpl->preload(array('header', 'footer', 'register'));
	}
	
	public function idx()
	{
		$this->rknclass->page_title='Create Account';
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('action', $this->rknclass->settings['site_url'] . '/index.php?ctr=register&amp;act=process_new_account', 'register');
		$groups='<select name="group" class="form-input-bg">';
		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'groups',
										              'where' => array('is_public' => '1')));
		$this->rknclass->db->query($query);
		while($row=$this->rknclass->db->fetch_array())
		{
			$groups.="\n<option value=\"{$row['group_id']}\">{$row['name']}</option>";
		}
		$groups.="\n</select>";
		
		$this->rknclass->tpl->parse('username', "<input name=\"username\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('password', "<input name=\"password\" type=\"password\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('first name', "<input name=\"firstname\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('surname', "<input name=\"surname\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('email', "<input name=\"email\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('aim', "<input name=\"aim\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('icq', "<input name=\"icq\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('msn', "<input name=\"msn\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('gtalk', "<input name=\"gtalk\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('skype', "<input name=\"skype\" type=\"text\" class=\"form-input\">", 'register');
		$this->rknclass->tpl->parse('groups', $groups, 'register');
		$this->rknclass->tpl->process('register');
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	public function process_new_account()
	{
		if(trim($this->rknclass->post['username']) == '' || trim($this->rknclass->post['password']) == '' || trim($this->rknclass->post['email']) == '')
		{
			exit($this->rknclass->message('Error', 'One or more fields were left blank'));
		}
		
		$this->rknclass->post['email'] = strtolower($this->rknclass->post['email']);
		
		if($this->rknclass->mail->check_email($this->rknclass->post['email']) === false)
		{
			exit($this->rknclass->message('Error', 'Invalid Email'));
		}
		
		/*
		 * Rewritten in 1.1.2
		 * 
		 * Checks the users username and email
		 * to ensure they are unique
		 */
		
		$this->rknclass->db->query("SELECT count(*) FROM " . TBLPRE . "users WHERE email='{$this->rknclass->post['email']}' LIMIT 1");
		
		if(intval($this->rknclass->db->result()) > 0)
		{
			exit($this->rknclass->message('Error', 'Another user has already registered using this email address!'));
		}

		$this->rknclass->db->query("SELECT count(*) FROM " . TBLPRE . "users WHERE username='{$this->rknclass->post['username']}' LIMIT 1");
		
		if(intval($this->rknclass->db->result()) > 0)
		{
			exit($this->rknclass->message('Error', 'Another user has already registered using this username!'));
		}
		
		/*
		 * Added in 1.1.2
		 * 
		 * Basic validation of username
		 * Prevents usernames starting or ending
		 * with whitespace characters
		 */
		
		//Check the username starts with a number or letter
		
		if(!ctype_alnum($this->rknclass->post['username'][0]))
		{
			exit($this->rknclass->message('Error', 'Usernames must start with a letter or number'));
		}
		
		if(!ctype_alnum($this->rknclass->post['username'][strlen($this->rknclass->post['username'])-1]))
		{
			exit($this->rknclass->message('Error', 'Usernames must end with a letter or number'));
		}
		
		if(strlen($this->rknclass->post['username']) < 3)
		{
			exit($this->rknclass->message('Error', 'Usernames must be at least 3 characters long!'));
		}
		
		// Check added in 1.1.1 to set a default group if field isn't supplied
		
		if(empty($this->rknclass->post['group']) || !ctype_digit($this->rknclass->post['group']))
		{
			$this->rknclass->post['group'] = '2';
		}
		else 
		{
			$this->rknclass->db->query("SELECT count(group_id) FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->post['group'] . "' AND is_public='1' LIMIT 1");
			
			if(intval($this->rknclass->db->result())!==1)
			{
				exit($this->rknclass->message('Error', 'Invalid usergroup specified!'));
			}
		}
		
		/*================
		Check the username
		hasn't been banned
		==================*/
		
		$token=strtok($this->rknclass->settings['banned_usernames'], "\n");
		
		while($token !== false)
		{
			if(strpos(trim(strtolower($token)), strtolower($this->rknclass->post['username'])) !== false)
			{
				exit($this->rknclass->message('Error', 'This username contains a word or phrase that the site administrator has banned. Please choose another!'));
			}
			
			$token=strtok("\n");
		}
		

		/*=======================
		Generate 512bit encrypted
		password with random salt
		========================*/
		
		$salt=$this->rknclass->utils->rand_chars(5);
		
		$password=$this->rknclass->utils->pass_hash($this->rknclass->post['password'], $salt);
		
		
		/*==================
		Build and run query
		===================*/
		
		
		$active_key='';
		$send_email=false;
		
		if($this->rknclass->settings['email_validation'] == '1')
		{
			$active_key=$this->rknclass->utils->rand_chars(25);
			$send_email=true;
			$validated=0;
		}
		else
		{
			$validated=1;
		}
		
		
		/*
		 * Added in 1.1.2
		 * Prevents unwanted characters from appearing
		 * in a registering users username
		 */
		
		if(preg_match('@([^A-Za-z0-9| ]+)@', $this->rknclass->post['username']))
		{
			exit($this->rknclass->message('Error', 'Usernames can only contain letters, numbers or spaces'));
		}
		
		$query=$this->rknclass->db->build_query(array('insert' => 'users',
		                                              'set' => array('username'     => $this->rknclass->post['username'],
													                 'password'     => $password,
																	 'salt'         => $salt,
																	 'email'        => $this->rknclass->post['email'],
																	 'firstname'    => $this->rknclass->post['firstname'],
																	 'surname'      => $this->rknclass->post['surname'],
																	 'icq'          => $this->rknclass->post['icq'],
																	 'aim'          => $this->rknclass->post['aim'],
																	 'msn'          => $this->rknclass->post['msn'],
																	 'gtalk'        => $this->rknclass->post['gtalk'],
																	 'validated'    => $validated,
																	 'joined'       => time(),
																	 'active_key'   => $active_key,
																	 'group_id'     => $this->rknclass->post['group'])));
																	 
		$this->rknclass->db->query($query);
		
		if($send_email === true)
		{
			/*========================
			This sends the validation
			email if required by the
			sites administrator(s)
			========================*/
			
			$this->rknclass->mail->send($this->rknclass->post['email'],
			                            $this->rknclass->settings['admin_email'],
										'Please verify your account on ' . $this->rknclass->settings['site_name'],
										"Hey " . ($this->rknclass->post['firstname']!=='' ? $this->rknclass->post['firstname'] : $this->rknclass->post['username']) . ",
thanks for registering on {$this->rknclass->settings['site_name']}!.
										
Click the visit the link below to register:
{$this->rknclass->settings['site_url']}/index.php?ctr=register&act=verify&key=$active_key
										
Thanks!");
										
		}
		$this->rknclass->message('Success', "Thanks for registering!<br />" . ($send_email === true ? "An email has been sent to the address you provided with details on how you can activate your account. You should receive it within 10 minutes, if not instant" : "You may now login!") . "");
	}
	
	public function verify()
	{
		if($this->rknclass->get['key'] == '' || $this->rknclass->get['key'] === false)
		{
			exit($this->rknclass->message('Error', 'Invalid activation key!'));
		}
		
		$this->rknclass->db->query("SELECT user_id,username FROM " . TBLPRE . "users WHERE active_key='{$this->rknclass->get['key']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->message('Error', 'Invalid activation key!'));
		}
		else
		{
			$row=$this->rknclass->db->fetch_array();
			$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET active_key=NULL, validated='1' WHERE user_id='{$row['user_id']}' LIMIT 1");
			$this->rknclass->message('Success', 'Hey ' . $row['username'] . ', your account has been successfully activated');
		}
	}
}
?>