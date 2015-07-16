<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}
class comments
{
	public function init()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/index.php"));
		}
		
		$this->rknclass->tpl->preload(array('header', 'footer', 'plugs', 'comments', 'comments_page'));
		
		if($this->rknclass->settings['comments_enabled'] !== '1')
		{
			exit($this->rknclass->message('Error', 'The site administrator has disabled the comments system'));
		}
		
		if($this->rknclass->user['group']['can_comment'] !== '1')
		{
			exit($this->rknclass->message('Error', 'You don\'t have permission to use this sites comment system'));
		}
		
		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		if($this->rknclass->db->result()<1)
		{
			exit($this->rknclass->message('Error', 'This plug could not be found in the database!'));
		}
		$this->rknclass->load_objects(array('plugs', 'comments', 'captcha'));
	}
	
	public function idx()
	{
		$this->rknclass->page_title='Comments';
		$this->rknclass->tpl->auto_parser('header');
		$this->rknclass->tpl->parse('plug', $this->rknclass->plugs->render_plug("plug_id='{$this->rknclass->get['id']}'"), 'comments_page');
		$this->rknclass->tpl->parse('comments', $this->rknclass->comments->listem("plug_id='{$this->rknclass->get['id']}' ORDER BY posted DESC"), 'comments_page');
		$this->rknclass->tpl->parse('action', $this->rknclass->settings['site_url'] . "/index.php?ctr=comments&amp;id={$this->rknclass->get['id']}&amp;act=process", 'comments_page');
		$this->rknclass->tpl->parse('poster', '<input type="text" name="poster" class="form-input" />', 'comments_page');
		$this->rknclass->tpl->parse('title', '<input type="text" name="title" class="form-input" />', 'comments_page');
		$this->rknclass->tpl->parse('description', '<textarea name="description" class="form-input" rows="5"></textarea>', 'comments_page');
		if($this->rknclass->settings['comments_captcha'] == '1' AND $this->rknclass->user['group']['captcha_enabled'] == '1')
		{
			$this->rknclass->captcha->create_captcha();
			$this->rknclass->tpl->parse('captcha[image]', "{$this->rknclass->settings['site_url']}/cache/captcha/{$this->rknclass->captcha->image_name}.png", 'comments_page');
			$this->rknclass->tpl->parse('captcha[input]', '<input type="text" name="captcha_string" class="form-input" />', 'comments_page');
		}
		$this->rknclass->tpl->process('comments_page');
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	public function process()
	{
		if($this->rknclass->settings['comments_captcha'] == '1' AND $this->rknclass->user['group']['captcha_enabled'] == '1')
		{
			if($this->rknclass->post['captcha_string'] == '' || $this->rknclass->post['captcha_string'] === false)
			{
				exit($this->rknclass->message('Error', "You must enter the captcha image verification string!"));	
			}
			
			if($this->rknclass->captcha->verify_captcha($this->rknclass->post['captcha_string']) === false)
			{
				exit($this->rknclass->message('Error', "Invalid captcha string. Please go back and try again"));
			}
		}
		
		if($this->rknclass->session->is_guest === true)
		{
			if($this->rknclass->post['poster'] == '')
			{
				$this->rknclass->post['poster']='Guest';
			}
			
			$this->rknclass->db->query("SELECT count(user_id) FROM " . TBLPRE . "users WHERE username='Guest'");
			
			if($this->rknclass->db->result()>0)
			{
				exit($this->rknclass->message('Error', 'Unforunately a registered user exists using this username. Please choose another name, or <a href="' . $this->rknclass->settings['site_url'] . '/index.php?ctr=register" title="Register" target="_blank">register an account</a> and have your own unique username!'));
			}
			
			if(stristr($this->rknclass->post['username'], '(Unregistered)'))
			{
				exit($this->rknclass->message('Error', "Please don't be annoying...putting <em>(Unregistered)</em> in your name serves no purpose other than forcing us to alert a shrink"));	
			}
			
			$poster_name=$this->rknclass->post['poster'] . ' (Unregistered)';
		}
		else
		{
			$poster_name=$this->rknclass->user['username'];
		}
		
		if(empty($this->rknclass->post['title']) || empty($this->rknclass->post['description']))
		{
			exit($this->rknclass->message('Error', 'One or more fields were left blank'));
		}
		
		if($this->rknclass->settings['comments_flood_control'] == '1')
		{
			$this->rknclass->db->query("SELECT count(comment_id) FROM " . TBLPRE . "comments WHERE poster_id='{$this->rknclass->user['user_id']}' AND posted>" . (time()-30) . " LIMIT 1");
			if($this->rknclass->db->result()>0)
			{
				exit($this->rknclass->message('Error', 'Flood Control: Please wait a short moment before posting another comment. Thanks!'));
			}
		}
		
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "comments SET plug_id='{$this->rknclass->get['id']}', title='{$this->rknclass->post['title']}', description='{$this->rknclass->post['description']}', poster='{$poster_name}', poster_id='{$this->rknclass->user['user_id']}', posted='" . time() . "'");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET total_comments=total_comments+1 WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		$this->rknclass->message('Success', "Comment posted successfully! <a href=\"{$this->rknclass->settings['site_url']}/index.php?ctr=comments&amp;id={$this->rknclass->get['id']}\">Click here</a> to return");
	}
}
?>