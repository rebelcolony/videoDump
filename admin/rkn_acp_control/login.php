<?php
class login extends rkn_render
{
	public function init()
	{
		if($this->rknclass->user['group']['is_admin'] === 1)
		{
			//The user is already logged in as an admin
			exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php"));
		}
		$this->rknclass->load_objects(array('login', 'global_tpl'));
	}
	
	public function idx()
	{
		exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php"));
	}
	
	public function process()
	{
		$this->rknclass->login->process();
	}
	
	public function logout()
	{
		if($this->rknclass->session->is_guest === true)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		
		@unlink(RKN__fullpath  . 'cache/sessions/sess-' . $this->rknclass->session->data['sess_id'] . '.php');
		
		if(isset($_COOKIE['rkn_remember_me']))
		{
			@setcookie('rkn_remember_me', 'false', (time() - 3600), '/');
		}
		
		$this->rknclass->global_tpl->exec_redirect('Successfully logged out!', '?ctr=index');
	}
}

?>