<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}
class edit_profile
{
	public $allowed;
	public $rknclass;
	
	public function init()
	{
		$this->rknclass->load_objects(array('mail'));
		$this->rknclass->tpl->preload(array('header', 'footer', 'edit_profile'));
		if($this->rknclass->session->is_guest === true)
		{
			exit($this->rknclass->message('Error', 'You must be logged in to access this page!'));
		}
	}
	
	public function idx()
	{
		$this->rknclass->page_title='Edit Profile';
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('action', $this->rknclass->settings['site_url'] . '/index.php?ctr=edit_profile&amp;act=update_profile', 'edit_profile');
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
		
		$this->rknclass->tpl->parse('password', "<input name=\"password\" type=\"password\" class=\"form-input\">", 'edit_profile');
		$this->rknclass->tpl->parse('password2', "<input name=\"password2\" type=\"password\" class=\"form-input\">", 'edit_profile');
		$this->rknclass->tpl->parse('email', "<input name=\"email\" type=\"text\" class=\"form-input\" value=\"{$this->rknclass->user['email']}\">", 'edit_profile');
		$this->rknclass->tpl->parse('aim', "<input name=\"aim\" type=\"text\" class=\"form-input\" value=\"{$this->rknclass->user['aim']}\">", 'edit_profile');
		$this->rknclass->tpl->parse('icq', "<input name=\"icq\" type=\"text\" class=\"form-input\" value=\"{$this->rknclass->user['icq']}\">", 'edit_profile');
		$this->rknclass->tpl->parse('msn', "<input name=\"msn\" type=\"text\" class=\"form-input\" value=\"{$this->rknclass->user['msn']}\">", 'edit_profile');
		$this->rknclass->tpl->parse('gtalk', "<input name=\"gtalk\" type=\"text\" class=\"form-input\" value=\"{$this->rknclass->user['gtalk']}\">", 'edit_profile');
		$this->rknclass->tpl->parse('avatar', "<input name=\"avatar\" type=\"file\" class=\"form-input\" />", 'edit_profile');
		if($this->rknclass->user['group']['is_admin'] == '1')
		{
			$this->rknclass->tpl->parse('groups', '<select name="group" class="form-input"><option value="---">---</option></select>', 'edit_profile');
		}
		else
		{
			$this->rknclass->tpl->parse('groups', $groups, 'edit_profile');
		}
		$this->rknclass->tpl->parse('date of birth', $dob, 'edit_profile');
		$this->rknclass->tpl->process('edit_profile');
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	public function update_profile()
	{
		if($this->rknclass->post['email'] == '')
		{
			exit($this->rknclass->message('Error', 'One or more fields were left blank'));
		}
		
		if($this->rknclass->post['email'] !== $this->rknclass->user['email'])
		{
			if($this->rknclass->mail->check_email($this->rknclass->post['email']) === false)
			{
				exit($this->rknclass->message('Error', 'Invalid Email'));
			}
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users WHERE email='" . $this->rknclass->post['email'] . "'");
			if($this->rknclass->db->num_rows()>0)
			{
				exit($this->rknclass->message('Error', 'Another user already exists using this email address!'));
			}
		}
		
		if($this->rknclass->user['group']['is_admin'] !== '1')
		{
			$this->rknclass->db->query("SELECT group_id FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->post['group'] . "' AND is_public='1' LIMIT 1");
			
			if($this->rknclass->db->num_rows()!==1)
			{
			exit($this->rknclass->message('Error', 'Invalid usergroup specified!'));
			}
		}
		
		if($this->rknclass->post['password'] !== '')
		{
			if($this->rknclass->post['password'] !== $this->rknclass->post['password2'])
			{
				exit($this->rknclass->message('Error', 'The password\'s didn\'t match!'));
			}
			
			$salt=$this->rknclass->utils->rand_chars(5);
			$password=$this->rknclass->utils->pass_hash($this->rknclass->post['password'], $salt);
		}
		else
		{
			$salt=$this->rknclass->user['salt'];
			$password=$this->rknclass->user['password'];
		}
		
		if($this->rknclass->user['group']['is_admin'] == '1')
		{
			$this->rknclass->post['group']=$this->rknclass->user['group_id'];
		}
		
		if(is_uploaded_file($_FILES['avatar']['tmp_name']))
		{
			$info=@getimagesize($_FILES['avatar']['tmp_name']) or exit($this->rknclass->message('Error', 'Unable to process image'));
			
			$allowed_types=array('image/jpeg', 'image/gif', 'image/png');
			
			if(in_array($info['mime'], $allowed_types, true) !== true)
			{
				exit($this->rknclass->message('Error', 'Invalid image type'));
			}
			
			/*=========================================
			Second form of defense incase the mime
			test returned true somehow. Also prevents
			very small images being uploaded, which
			are normally used by attackers anyway
			===========================================*/
			
			if($info['0'] < 35 || $info['1'] < 35)
			{
				exit($this->rknclass->message('Error', 'Uploaded image was too small'));
			}
			
			if($info['0'] > $this->rknclass->user['group']['max_avatar_width'] || $info['1'] > $this->rknclass->user['group']['max_avatar_height'])
			{
				exit($this->rknclass->message('Error', "Uploaded image was too big!<br /><br />Maximuim image size for your group is {$this->rknclass->user['group']['max_avatar_width']} x {$this->rknclass->user['group']['max_avatar_height']}"));
			}
			
			$handle=@fopen(RKN__fullpath . 'userdata/avatars/' . $this->rknclass->user['user_id'] . '.php', 'w+') or exit($this->rknclass->message('Error', 'We were unable to process your image. Please alert administration of this problem'));
			
			$array=array('image_width' => $info['0'],
			             'image_height' => $info['1'],
			             'image_mime' => $info['mime'],
			             'image_data' =>  @file_get_contents($_FILES['avatar']['tmp_name']));
						 
			$array=serialize($array);
			
			fwrite($handle, '<?php if(!defined(\'IN_RKN_PREDATOR\')){exit(\'Access to this file is prohibited!\');}?>' . "\n" . $array);
			fclose($handle);
		}
		
		$query=$this->rknclass->db->build_query(array('update' => 'users',
		                                              'set' => array('password'     => $password,
																	 'salt'         => $salt,
																	 'email'        => $this->rknclass->post['email'],
																	 'firstname'    => $this->rknclass->post['firstname'],
																	 'surname'      => $this->rknclass->post['surname'],
																	 'icq'          => $this->rknclass->post['icq'],
																	 'aim'          => $this->rknclass->post['aim'],
																	 'msn'          => $this->rknclass->post['msn'],
																	 'gtalk'        => $this->rknclass->post['gtalk'],
																	 'group_id'     => $this->rknclass->post['group']),
		                                            'where' => array('user_id' => $this->rknclass->user['user_id'])));
																	 
		$this->rknclass->db->query($query);
		
		$this->rknclass->message('Success', 'Updated profile successfully!');
	}
}

?>