<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}
class login
{
	public function init()
	{
		$this->rknclass->load_objects(array('login'));
		$this->rknclass->tpl->preload(array('header', 'footer', 'login'));
	}
	
	public function idx()
	{
		$this->rknclass->page_title='Login';
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('action', $this->rknclass->settings['site_url'] . '/index.php?ctr=login&act=process', 'login');
		$this->rknclass->tpl->parse('username', '<input type="text" name="username" class="form-input" />', 'login');
		$this->rknclass->tpl->parse('password', '<input type="password" name="password" class="form-input" />', 'login');
		$this->rknclass->tpl->process('login');
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	public function process()
	{
		$this->rknclass->get['return_url'] = $this->rknclass->settings['site_url'];
		$this->rknclass->login->process();
		header("Location: {$this->rknclass->settings['site_url']}/index.php");

	}
	
	public function logout()
	{
		if($this->rknclass->session->is_guest === true)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
			
		}
		$this->rknclass->tpl->preload(array('header', 'footer', 'message'));
		@unlink(RKN__fullpath  . 'cache/sessions/sess-' . $this->rknclass->session->data['sess_id'] . '.php');
		
		if(isset($_COOKIE['rkn_remember_me']))
		{
			@setcookie('rkn_remember_me', 'false', (time() - 3600), '/');
		}
		
		$this->rknclass->message('Success', 'Successfully logged out!');
	}
}

?>