<?php

/*

Predator CMS Webmasters Index Controller
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

class index extends rkn_render
{
	public $allowed;
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('plugs', 'cache', 'p3_archive'));
		if($this->rknclass->session->is_guest === true)
		{
			if($this->rknclass->get['return_url'] == '')
			{
				$this->rknclass->get['return_url']=$this->rknclass->settings['site_url'] . '/webmasters/index.php';
			}
			exit($this->load_login_form());
		}
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=dashboard");
	}
	private function load_login_form()
	{
		$return=$this->rknclass->get['return_url'];
		echo <<<EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<style>
body 
{
background-image:url(images/login_01.jpg);
background-repeat:repeat-x;     
padding:0;
}

#login {
border: 1px solid #000;
background-color: #e3ecf3;      
margin-top: 200px;       
height: 240px; 
position: absolute; 
left: 50%;
width:437px;
margin-left: -220px;
font-family:Verdana, Arial, Helvetica, sans-serif;
font-size:12px;
}

#text-field
{
	background-image:url(images/login_08.jpg);
	background-repeat:no-repeat;
	border-width:1px;
	border-color:#b0c8e1;
	margin-right:50px;
	margin-left:auto;
}
#spacer
{
	height:7px;
}
a.links:link
{
	color: #2f4861;
	font-weight:bold;
}
a.links:visited
{
	color: #2f4861;
	font-weight:bold;
}
a.links:active
{
	color: #2f4861;
	font-weight:bold;
}
a.links:hover
{
	color: #0066FF;
	font-weight:bold;
}
</style>
<title>{$this->rknclass->settings['site_name']} :: Webmaster Area Login</title>
<script language="JavaScript">
function submitform()
{
  document.login.submit();
}
</script>
</head>
<body>
<form action="{$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=login&amp;act=process&amp;return_url=$return" method="post" name="login">
	<div id="login" align="center">
		<img src="images/login_04.jpg" border="0" alt="Predator CMS Logo"/>
        <div id="input-box">Username: <input name="username" type="text" id="text-field"/></div>
        <div id="spacer"></div>
        <div id="input-box">Password: <input name="password" type="password" id="text-field"/></div>
        <div id="spacer"></div>
        <div align="center"><img src="images/login_now.jpg" alt="Login" onclick="javascript: submitform()"/><br />Remember me?<input type="checkbox" name="remember_me" /></div>
        <div id="spacer"></div>
        <div id="links" align="center"><a class="links" href="{$this->rknclass->utils->build_url('forgot_password', 'idx')}">Forgot Password?</a> | <a class="links" href="{$this->rknclass->settings['site_url']}/index.php?ctr=register">Not registered?</a></div>
	</div>
</form>
</html>		
EOD;
	}
}

?>