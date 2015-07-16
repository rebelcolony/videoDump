<?php
define('RKN__admintab', 'templates');
class templates extends rkn_render
{
	public function init()
	{
		if($this->rknclass->session->is_guest === true)
		{
			if($this->rknclass->get[ajax] == '1')
			{
				exit('<strong><font color="red">Session Expired!</strong> - Please login again<br /><br /><strong>TIP:</strong> If you have just spent a while filing out a form, open a new browser window/tab, login and then go back to this window/tab and hit submit</font>');
			}
			else
			{
				exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
			}
		}
		
		if($this->rknclass->user['group']['is_admin'] !== '1')
		{
			exit('You must be an admin to access this area!');	
		}
		
		$this->rknclass->load_objects(array('global_tpl', 'form'));
		
		if($this->rknclass->user['group']['is_restricted'] == '1')
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to access this area!'));
		}
	}
	
	public function idx()
	{
		$this->rknclass->page_title='Templates Management';
		$this->rknclass->global_tpl->admin_header();
		
		echo '
        <div class="page-title">Installed Templates</div>
        
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Description</th>
    <th scope="col">Author</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';
  
		$this->rknclass->db->query("SELECT tpl_id, tpl_name, tpl_description, tpl_author FROM " . TBLPRE . "templates");
		while($row=$this->rknclass->db->fetch_array())
		{
			 echo "<tr id=\"rows\">";						
			 echo "<td id=\"title\">" . $row['tpl_name'] . "</td>";
			 echo "<td>" . $row['tpl_description'] . "</td>";
			 echo "<td>" . $row['tpl_author'] . "</td>";
			 echo "<td><a href=\"index.php?ctr=edit_templates&amp;act=header&amp;id={$row['tpl_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>";
			 echo "<td><a href=\"index.php?ctr=templates&amp;act=delete_template&amp;id={$row['tpl_id']}\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>";
			 echo "</tr>";
		}
		echo '</table>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function add_template()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		$this->rknclass->pagetitle='Add new template';
		
		$this->rknclass->global_tpl->admin_header();
		
		$this->rknclass->form->new_form('Add new template');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action("index.php?ctr=templates&amp;act=process_new_template");
		$this->rknclass->form->add_input('tpl_name', 'input', 'Template Name', 'Enter the name of your new template', $info['tpl_name']);
		$this->rknclass->form->add_input('tpl_description', 'textarea', 'Template Description', 'Enter a short description for this template', $info['tpl_description']);
		$this->rknclass->form->add_input('tpl_author', 'input', 'Template Author', 'Enter the name of your new template\'s author', $info['tpl_author']);
		$this->rknclass->form->process();
		
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function process_new_template()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->post['tpl_name'] == '' || $this->rknclass->post['tpl_name'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Your template must have at least the name field filled in!'));
		}
		
		$data=serialize(array());
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "templates SET tpl_author='{$this->rknclass->post['tpl_author']}', tpl_description='{$this->rknclass->post['tpl_description']}', tpl_name='{$this->rknclass->post['tpl_name']}', tpl_data='$data'");
		
		$insert_id=$this->rknclass->db->insert_id();
		
		/*==========================================
		The array below contains all the templates
		that predator will create when you are
		creating a new template via the admin cp
		===========================================*/
		
		$templates=array("tpl_blog_page.php",
		                 "tpl_comments.php",
                         "tpl_comments_page.php",
                         "tpl_edit_profile.php",
                         "tpl_footer.php",
                         "tpl_forgot_password.php",
                         "tpl_header.php",
                         "tpl_index.php",
                         "tpl_login.php",
                         "tpl_message.php",
                         "tpl_pagination.php",
                         "tpl_plugs.php",
                         "tpl_register.php",
		 "tpl_search.php",
		 "tpl_top_frame.php",
                         "tpl_video_page.php",
                         "tpl_video_player_flv.php",
                         "tpl_video_player_wmv.php");
		
		$new_dir=RKN__fullpath . 'cache/templates/' . $insert_id . '/';
		mkdir($new_dir) or exit($this->rknclass->global_tpl->admin_error('Unable to create cache directory for this template!'));
		
		foreach($templates as $key)
		{
			$handle=fopen($new_dir . $key, 'w+');
			fwrite($handle, "<!-- BLANK TEMPLATE -->");
			fclose($handle);
		}
		
		$handle=fopen($new_dir . 'rkn_template_info.php', 'w+');
		
		$info=array('tpl_name' => $this->rknclass->post['tpl_name'], 'tpl_description' => $this->rknclass->post['tpl_description'], 'tpl_author' => $this->rknclass->post['tpl_author']);
		
		fwrite($handle, serialize($info));
		fclose($handle);
		
		$this->rknclass->global_tpl->exec_redirect('Successfully added new template', '?ctr=edit_templates[and]id=' . $insert_id);
	}
	
	public function delete_template()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		$this->rknclass->pagetitle='Template Deletion Confirmation';
		
		$this->rknclass->global_tpl->admin_header();
		
		$this->rknclass->form->new_form('Template Deletion Confirmation');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action("index.php?ctr=templates&amp;act=remove_template&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('password', 'password', 'Account Password', 'Please re-enter your account password to perform this action.');
		$this->rknclass->form->add_input('confirm', 'dropdown', 'Confirm deletion', '<strong>You are about to destroy an entire template!</strong><br /><br />Are you sure you want to proceed? <u>This action <strong>cannot</strong> be undone</u>', '<option value="no" SELECTED>No, I do not wish to remove this template</option><option value="yes">Yes, I am I sure I want to permanently delete this template</option');
		$this->rknclass->form->process();
		
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function remove_template()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid template id'));
		}
		
		$this->rknclass->db->query("SELECT count(tpl_id) FROM " . TBLPRE . "templates WHERE tpl_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		
		if($this->rknclass->db->result()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Template not found in database!'));	
		}
		
		if($this->rknclass->get['id'] == $this->rknclass->settings['default_style'])
		{
			exit($this->rknclass->global_tpl->admin_error('You cannot remove the default template'));
		}
		
		/*===================================
		The issue below should never happen,
		but better being safe than sorry...
		=====================================*/
		
		$this->rknclass->db->query("SELECT count(tpl_id) FROM " . TBLPRE . "templates");
		
		if($this->rknclass->db->result()<2)
		{
			exit($this->rknclass->global_tpl->admin_error('We have detected that you only have one template installed, thus you cannot delete it!'));
		}
		
		if($this->rknclass->post['password'] == '' || $this->rknclass->post['confirm'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}
		
		$password=$this->rknclass->utils->pass_hash($this->rknclass->post['password'], $this->rknclass->user['salt']);
		
		if($password !== $this->rknclass->user['password'])
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid password entered!'));
		}
		
		if($this->rknclass->post['confirm'] !== 'yes')
		{
			exit($this->rknclass->global_tpl->admin_error('You did not confirm the template deletion'));
		}
		
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "templates WHERE tpl_id='" . $this->rknclass->get['id'] . "'");
		
		$handle=opendir(RKN__fullpath . 'cache/templates/' . $this->rknclass->get['id'] . '/');
		while($file=readdir($handle))
		{
			if($file != '.' AND $file != '..' AND !is_dir($file))
			{
				@unlink(RKN__fullpath . 'cache/templates/' . $this->rknclass->get['id'] . '/' . $file);
			}
		}
		
		closedir($handle);
		
		@rmdir(RKN__fullpath . 'cache/templates/' . $this->rknclass->get['id'] . '/') or exit($this->rknclass->global_tpl->admin_error('Unable to remove cache directory ' . $this->rknclass->get['id'] . '! Please check permissions on cache file, and manually remove this directory via FTP'));
		
		$this->rknclass->global_tpl->exec_redirect('Successfully removed template', '?ctr=templates');
	}
	
	public function config()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		$templates='';
		$this->rknclass->db->query("SELECT tpl_id,tpl_name FROM " . TBLPRE . "templates ORDER by tpl_name ASC");
		while($row=$this->rknclass->db->fetch_array())
		{
			$templates.="<option value=\"{$row['tpl_id']}\"" . ($row['tpl_id'] == $this->rknclass->settings['default_style'] ? " SELECTED" : "") . ">{$row['tpl_name']}</option>";
		}
		$this->rknclass->page_title='Manage Template Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Template Settings');
		$this->rknclass->form->set_action('index.php?ctr=templates&amp;act=update_config');
		$this->rknclass->form->add_input('default_style', 'dropdown', 'Default Template', 'Select the default template you wish to use for your site', $templates);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function edit_template()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid template id!'));
		}
		
		header('Location:' . $this->rknclass->settings['site_url'] . '/' . RKN__adminpath . '/index.php?ctr=edit_template');
	}

	public function update_config()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if(empty($this->rknclass->post['default_style']))
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		$this->rknclass->cache->update_settings_and_cache(array('default_style' => $this->rknclass->post['default_style']));
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}	
	public function repair_cache()
	{
		if(!is_dir(RKN__fullpath . 'cache/templates/'))
		{
			@mkdir(RKN__fullpath . 'cache/templates/') or exit($this->rknclass->global_tpl->admin_error('Template cache folder doesn\'t exist, attempted to create it, but failed. Please check permissions on cache folder'));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "templates");
		while($row=$this->rknclass->db->fetch_array())
		{
			if(!is_dir(RKN__fullpath . 'cache/templates/' . $row['tpl_id'] . '/'))
			{
				@mkdir(RKN__fullpath . 'cache/templates/' . $row['tpl_id'] . '/') or exit($this->rknclass->global_tpl->admin_error('Unable to create template cache directory - Check permissions template cache folder'));
			}
			
			if(!file_exists(RKN__fullpath . 'cache/templates/.htaccess'))
			{
				$handle=@fopen(RKN__fullpath . 'cache/templates/.htaccess', 'w+') or exit($this->rknclass->global_tpl->admin_error('Unable to open/create .htaccess file in templates cache directory'));
				@fwrite($handle, "# File generated by Predator CMS {$this->rknclass->settings['version']} #\nDeny From All") or exit($this->rknclass->global_tpl->admin_error('Unable to write to .htaccess file in templates cache directory'));
				fclose($handle);
			}
			
			$data=unserialize($row['tpl_data']);
			
			foreach($data as $key => $value)
			{
				$value=base64_decode($value);
				$handle=fopen(RKN__fullpath . 'cache/templates/' . $row['tpl_id'] . '/tpl_' . $key . '.php', 'w+') or exit($this->rknclass->global_tpl->admin_error('Unable to open file tpl_' . $key . '.php - Please check folder permissions'));
				fwrite($handle, $value) or exit($this->rknclass->global_tpl->admin_error('Unable to write to file tpl_' . $key . '.php - Please check folder permissions'));
				fclose($handle);
			}
			
			$handle=@fopen(RKN__fullpath . 'cache/templates/' . $row['tpl_id'] . '/rkn_template_info.php', 'w+') or exit($this->rknclass->global_tpl->admin_error('Unable to open file rkn_template_info.php - Please check folder permissions'));
			
			$info['tpl_name']=$row['tpl_name'];
			$info['tpl_description']=$row['tpl_description'];
			$info['tpl_author']=$row['tpl_author'];
			
			$info_cache=serialize($info);
			
			fwrite($handle, $info_cache);
			fclose($handle);
			
			
			/*==================================================
			This is to prevent directory listings, on non-apache
			web servers who do not acknowledge .htaccess files
			===================================================*/
			
			$handle=@fopen(RKN__fullpath . 'cache/templates/' . $row['tpl_id'] . '/index.html', 'w+');
			@fwrite($handle, '<strong>Access to this directory is prohibited!</strong>');
			fclose($handle);
			
			$htaccess = "# Generated by Predator CMS {$this->rknclass->settings['version']} #\nDeny From All";
			file_put_contents(RKN__fullpath . 'cache/templates/' . $row['tpl_id'] . '/.htaccess', $htaccess);
		}
		$this->rknclass->global_tpl->exec_redirect('Rebuilt template cache!', '?ctr=templates');
	}
}
?>