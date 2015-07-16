<?php
class management_update extends rkn_render
{
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
	
	public function idx()
	{
		exit($this->rknclass->global_tpl->admin_error('Direct access to this file\'s index is prohibited'));
	}
	
	public function core_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		if(isset($this->rknclass->post['all']) AND !empty($this->rknclass->post['all']))
		{
			$types = array('1', '2', '3', '5');
		}
		else
		{
			$types = array();
			if(isset($this->rknclass->post['plugs']) AND !empty($this->rknclass->post['plugs']))
			{
				$types[] = '1';
			}
			if(isset($this->rknclass->post['videos']) AND !empty($this->rknclass->post['videos']))
			{
				array_push($types, '2', '3');
			}
			if(isset($this->rknclass->post['blogs']) AND !empty($this->rknclass->post['blogs']))
			{
				$types[] = '5';
			}
			
		}
		if(!isset($types) || count($types) < 1)
		{
			exit($this->rknclass->form->ajax_error('You must select the type\'s of content you want displayed!'));
		}
		
		$fields=array('site_url', 'site_name', 'site_description', 'meta_keywords', 'admin_email', 'plugs_per_page', 'optimise_db');
		
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		$new_settings = array();
		
		foreach($fields as $setting_name)
		{
			$new_settings[$setting_name] = $this->rknclass->post[$setting_name];
		}
		
		$types = serialize($types);
		
		$new_settings['listing_types'] = $types;
		
		if(!isset($this->rknclass->post['next_hourly_cron']) || empty($this->rknclass->post['next_hourly_cron']) || ($next_hourly_cron = strtotime($this->rknclass->post['next_hourly_cron'])) === false)
		{
		    exit($this->rknclass->form->ajax_error('Invalid data entered for "next hourly cron" field!'));
		}

		if(!isset($this->rknclass->post['next_daily_cron']) || empty($this->rknclass->post['next_daily_cron']) || ($next_daily_cron = strtotime($this->rknclass->post['next_daily_cron'])) === false)
		{
		    exit($this->rknclass->form->ajax_error('Invalid data entered for "next daily cron" field!'));
		}
		
		$new_settings['next_hourly_cron'] = $next_hourly_cron;
		$new_settings['next_daily_cron']  = $next_daily_cron;
		
		if(!isset($this->rknclass->post['queue_time']) || !ctype_digit($this->rknclass->post['queue_time']))
		{
		    exit($this->rknclass->form->ajax_error('Invalid data entered for queue value!'));
		}
		
		$new_settings['queue_time'] = $this->rknclass->post['queue_time'];
		
		$this->rknclass->cache->update_settings_and_cache($new_settings);
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}

	public function session_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		$fields=array('session_length');
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		if($this->rknclass->post['session_length'] < 180)
		{
			exit($this->rknclass->form->ajax_error('Session length too short! Value must be at least 3 minutes'));
		}
		$this->rknclass->cache->admin_update_settings($fields);
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}
	
	public function trade_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		$fields=array('trade_type', 'trade_min_ratio', 'trade_default_status', 'trade_min_in', 'trade_calc_method', 'trade_24_method');
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		if(strpos($this->rknclass->post['trade_min_ratio'], '%') !== false)
		{
			$this->rknclass->post['trade_min_ratio']=str_replace('%', '', $this->rknclass->post['trade_min_ratio']);
		}
		$this->rknclass->cache->admin_update_settings($fields);
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}

	public function update_site()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id']=='')
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		
		$fields=array('site_name', 'site_url', 'u_total_in', 'u_total_out', 'r_total_in', 'r_total_out','approved', 'banned');
		
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}		
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->form->ajax_error('Invalid Site - This site does not exist in our trade system. If you followed a valid link, please report this error to administration'));
		}
		$row=$this->rknclass->db->fetch_array();
		
		if($this->rknclass->post['site_name'] !== $row['name'])
		{
			$this->rknclass->db->query("SELECT url FROM " . TBLPRE . "sites WHERE name='{$this->rknclass->post['site_name']}'");
			
			if($this->rknclass->db->num_rows()>0)
			{
				exit($this->rknclass->form->ajax_error('There\'s another site in the system with the same name. Please pick another.'));
			}
		}
		$url=$this->rknclass->utils->rkn_url_parser($this->rknclass->post['site_url']);
		
		if($url === false)
		{
			exit($this->rknclass->form->ajax_error('Invalid site url. Urls must start with http://'));
		}
		
		$this->rknclass->db->query("SELECT user_id FROM " . TBLPRE . "users WHERE user_id='{$this->rknclass->post['owner']}' LIMIT 1");
		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->form->ajax_error('User not found!'));
		}
		
		if($row['owner'] !== $this->rknclass->post['owner'])
		{
		    $this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites = (total_sites - 1) WHERE user_id='{$row['owner']}' LIMIT 1");
		    $this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites = (total_sites + 1) WHERE user_id='{$this->rknclass->post['owner']}' LIMIT 1");
		}
		$query=$this->rknclass->db->build_query(array('update' => 'sites',
		                                              'set' => array('name' => $this->rknclass->post['site_name'],
													                 'url' => $url,
																	 'u_total_in' => $this->rknclass->post['u_total_in'],
																	 'u_total_out' => $this->rknclass->post['u_total_out'],
																	 'r_total_in' => $this->rknclass->post['r_total_in'],
																	 'r_total_out' => $this->rknclass->post['r_total_out'],
																	 'approved' => $this->rknclass->post['approved'],
																	 'banned' => $this->rknclass->post['banned'],
		                                                             'owner'  => $this->rknclass->post['owner']),	 
													  'where' => array('site_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		
		if($this->rknclass->post['banned'] == '1')
		{
		    if($this->rknclass->db->result($this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "banned_sites WHERE url='{$url}'")) < 1)
		    {
		        $this->rknclass->db->query("INSERT INTO " . TBLPRE . "banned_sites SET url='{$url}', ban_date='" . time() . "'");
		    }
		}

		$this->rknclass->db->query($query);
		$this->rknclass->form->ajax_success('Successfully updated site!');
	}
	
	public function del_site()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id']=='')
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->form->ajax_error('Invalid Site - This site does not exist in the trade system!'));
		}
		$row=$this->rknclass->db->fetch_array();
		
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites=total_sites-1 WHERE user_id='$row[owner]' LIMIT 1");
		$this->rknclass->global_tpl->exec_redirect('Site was successfully deleted!', $this->rknclass->get['return_url']);
	}
	
	public function add_group()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		$fields=array('name', 'plugs_approved', 'submit_limit', 'max_avatar_width', 'max_avatar_height', 'max_avatar_size', 'can_comment', 'captcha_enabled', 'can_search', 'search_flood_control', 'plug_edit_time', 'is_public', 'is_admin');
		
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}
				
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE name LIKE '" . $this->rknclass->post['name'] . "' LIMIT 1");
		
		if($this->rknclass->db->num_rows()>0)
		{
			exit($this->rknclass->global_tpl->admin_error('A usergroup with the same, or similar name already exists'));
		}
		
		
		$query="INSERT INTO " . TBLPRE . "groups SET ";
		
		foreach($fields as $key)
		{
			if(!isset($first))
			{
				$query.="$key='" . $this->rknclass->post['' . $key . ''] . "'";
				$first=true;
			}
			else
			{
				$query.=", $key='" . $this->rknclass->post['' . $key . ''] . "'";
			}
		}
		
		$this->rknclass->db->query($query);
		
		$this->rknclass->cache->rebuild_groups_cache();
		$this->rknclass->global_tpl->exec_redirect('Usergroup ' . $this->rknclass->post['name'] . ' added successfull!', $this->rknclass->get['return_url']);
	}
	
	public function del_group()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id'] === false || $this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid usergroup specified.', $this->rknclass->get['return_url']));
		}
		
		$default_groups=array('1', '2', '3', '4');
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->get['id'] . "'");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The usergroup was not found in the database!', $this->rknclass->get['return_url']));
		}
		
		if(in_array($this->rknclass->get['id'], $default_groups, true) === true)
		{
			$this->rknclass->global_tpl->exec_redirect('Unable to delete this group as it is a default usergroup', $this->rknclass->get['return_url']);
		}
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET group_id='2' WHERE group_id='" . $this->rknclass->get['id'] . "'");
		@unlink(RKN__fullpath . 'cache/groups/' . $this->rknclass->get['id'] . '.php') or $errors=true;
		if($errors===true)
		{
			if($this->rknclass->debug === true)
			{
				$this->rknclass->throw_debug_message('Unable to delete usergroup cache file, please ensure folder permissions are set correctly on the folder "' . RNK__fullpath . 'cache/groups/');
			}
		}
		$this->rknclass->global_tpl->exec_redirect('Successfully deleted usergroup!', $this->rknclass->get['return_url']);
	}
	
	public function edit_group()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		$fields=array('name', 'plugs_approved', 'require_validation', 'submit_limit', 'max_avatar_width', 'max_avatar_height', 'max_avatar_size', 'can_comment', 'captcha_enabled', 'can_search', 'search_flood_control', 'can_comment', 'plug_edit_time', 'is_public', 'is_admin');
		
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->get['id'] . "'");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Group not found in database!'));
		}
		$row=$this->rknclass->db->fetch_array();
		
		
		/*=============================
		If the groups name differs
		from the previous, perform
		a check to ensure that another
		group doesn't exist with the 
		same name
		===============================*/
		
		
		if($this->rknclass->post['name'] !== $row['name'])
		{
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE name LIKE '" . $this->rknclass->post['name'] . "' LIMIT 1");
			
			if($this->rknclass->db->num_rows()>0)
			{
				exit($this->rknclass->global_tpl->admin_error('A usergroup with the same, or similar name already exists'));
			}
		}
		
		
		/*=================================
		Overrides the posted data for the
		guest, member, moderator and admin
		groups to minimise damage from
		unauthorised admin cp access
		==================================*/
		
		if($row['group_id'] == '4')
		{
			$this->rknclass->post['is_admin']='1';
			$this->rknclass->post['is_public']='0';
		}
		if($row['group_id'] == '1' || $row['group_id'] == '2' || $row['group_id'] == '3')
		{
			$this->rknclass->post['is_admin']='0';
		}
		
		if($row['group_id'] == '1')
		{
			$this->rknclass->post['is_public']='0';
		}
		
		elseif($row['group_id'] == '2')
		{
			$this->rknclass->post['is_public']='1';
		}
		
		$query="UPDATE " . TBLPRE . "groups SET ";
		
		foreach($fields as $key)
		{
			if(!isset($first))
			{
				$query.="$key='" . $this->rknclass->post['' . $key . ''] . "'";
				$first=true;
			}
			else
			{
				$query.=", $key='" . $this->rknclass->post['' . $key . ''] . "'";
			}
		}
		$query=$query . " WHERE group_id='" . $this->rknclass->get['id'] . "' LIMIT 1";
		$this->rknclass->db->query($query);
		
		$this->rknclass->cache->rebuild_groups_cache();
		$this->rknclass->global_tpl->exec_redirect('Usergroup ' . $this->rknclass->post['name'] . ' updated successfull!', '?ctr=management[and]act=manage_groups');
	}
	
	public function image_cropper()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		$fields=array('thumb_dir', 'thumb_width', 'thumb_quality', 'thumb_height', 'v_thumb_width', 'v_thumb_height', 'v_thumb_quality', );
		if($this->rknclass->utils->check_post_array($fields) === false)
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		if(substr($this->rknclass->post['thumb_dir'], -1) == '/')
		{
			exit($this->rknclass->form->ajax_error('Thumbnail directory must <u>not</u> contain a trailing slash (/)'));
		}
		$this->rknclass->cache->admin_update_settings($fields);
		$this->rknclass->form->ajax_success('Successfully updated settings!');	
	}

	public function add_user()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		$this->rknclass->load_object('mail');
		
		if($this->rknclass->post['username'] == '' || $this->rknclass->post['password'] == '' || $this->rknclass->post['email'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank'));
		}
		
		if($this->rknclass->mail->check_email($this->rknclass->post['email']) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid Email'));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users WHERE username='{$this->rknclass->post['username']}' OR email='{$this->rknclass->post['email']}'");
		
		if($this->rknclass->db->num_rows()>0)
		{
			exit($this->rknclass->global_tpl->admin_error('A user with this username or email already exists'));
		}
		
		$this->rknclass->db->query("SELECT group_id FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->post['group'] . "' LIMIT 1");
		
		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid usergroup specified!'));
		}
		
		$salt=$this->rknclass->utils->rand_chars(5);
		
		$password=$this->rknclass->utils->pass_hash($this->rknclass->post['password'], $salt);
		
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
																	 'validated'    => '1',
																	 'joined'       => time(),
																	 'group_id'     => $this->rknclass->post['group'])));
																	 
		$this->rknclass->db->query($query);
		$this->rknclass->global_tpl->exec_redirect('User added successfully!', '?ctr=management&act=manage_users');
	}
	
	public function edit_user()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid user id specified'));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users WHERE user_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The user was not found in the database'));
		}
		
		if(empty($this->rknclass->post['username']) || empty($this->rknclass->post['email']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank'));
		}
		
		if($this->rknclass->post['username'] !== $row['username'])
		{
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users WHERE username='{$this->rknclass->post['username']}'");
			if($this->rknclass->db->num_rows()>0)
			{
				exit($this->rknclass->global_tpl->admin_error('A user with this username already exists'));
			}
		}
		
		if($this->rknclass->post['email'] !== $row['email'])
		{
			$this->rknclass->load_object('mail');
			
			if($this->rknclass->mail->check_email($this->rknclass->post['email']) === false)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid Email'));
			}
			
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users WHERE email='{$this->rknclass->post['email']}'");
			if($this->rknclass->db->num_rows()>0)
			{
				exit($this->rknclass->global_tpl->admin_error('A user with this email already exists'));
			}
		}
		
		$this->rknclass->db->query("SELECT group_id FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->post['group'] . "' LIMIT 1");
		
		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid usergroup specified!'));
		}
		
		if($this->rknclass->get['id'] == '1')
		{
			if($this->rknclass->post['group'] !== '4')
			{
				if($this->rknclass->debug === true)
				{
					$this->rknclass->throw_debug_message('Posted group id is: <strong>' . $this->rknclass->post['group'] . '</strong> whereas it should be <strong>4</strong>');
				}
				exit($this->rknclass->global_tpl->admin_error('You cannot change the super admin\'s usergroup!'));
			}
		}
		
		if($this->rknclass->post['password'] !== '')
		{
			$salt=$this->rknclass->utils->rand_chars(5);
		
			$password=$this->rknclass->utils->pass_hash($this->rknclass->post['password'], $salt);
		}
		else
		{
			$salt=$row['salt'];
			$password=$row['password'];
		}
		
		$query=$this->rknclass->db->build_query(array('update' => 'users',
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
																	 'group_id'     => $this->rknclass->post['group'],
																	 'validated'     => $this->rknclass->post['validated']),
												      'where' => array('user_id' => $this->rknclass->get['id'])));
																	 
		$this->rknclass->db->query($query);
		$this->rknclass->global_tpl->exec_redirect('User updated successfully!', '?ctr=management&act=manage_users');
	}
	
	public function del_user()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid user id specified'));
		}

		if($this->rknclass->get['id'] == $this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->admin_error('You cannot delete yourself'));
		}
		
		if($this->rknclass->get['id'] == '1')
		{
			exit($this->rknclass->global_tpl->admin_error('The super admin cannot be deleted!'));
		}
		
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "users WHERE user_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sites WHERE owner='" . $this->rknclass->get['id'] . "'");
		$this->rknclass->global_tpl->exec_redirect('User deleted successfully', '?ctr=management&act=manage_users');
	}
	
	public function delete_mail()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid email id!'));
		}
		
		$this->rknclass->db->query("SELECT count(mail_id) FROM " . TBLPRE . "mail WHERE mail_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		
		if($this->rknclass->db->result()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Unable to find email in database!'));
		}
		
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "mail WHERE mail_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "mail_queue WHERE mail_id='" . $this->rknclass->get['id'] . "'");
		
		//Make sure everything's runnin' soundly!
		$this->rknclass->db->query("OPTIMIZE TABLE " . TBLPRE . "mail");
		$this->rknclass->db->query("OPTIMIZE TABLE " . TBLPRE . "mail_queue");
		
		$this->rknclass->global_tpl->exec_redirect('Email log removed successfully!', '?ctr=management&act=mail_man');
	}
	
	public function cluster_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		$data = array();
		
		if($this->rknclass->post['thumb_server'] == 'local')
		{
			$thumb_server = '0';
		}
		else
		{
			$thumb_server = '1';
			
			if(empty($this->rknclass->post['thumb_server_address']) || empty($this->rknclass->post['thumb_server_username']) || empty($this->rknclass->post['thumb_server_password']) || empty($this->rknclass->post['thumb_server_http']))
			{
				exit($this->rknclass->form->ajax_error('One or more required fields were left blank!'));
			}
			
			$data['thumb_server_address']  = $this->rknclass->post['thumb_server_address'];
			$data['thumb_server_username'] = $this->rknclass->post['thumb_server_username'];
			$data['thumb_server_password'] = $this->rknclass->post['thumb_server_password'];
			$data['thumb_server_http']     = $this->rknclass->post['thumb_server_http'];
		}
		
		if($this->rknclass->post['video_server'] == 'local')
		{
			$video_server = '0';
		}
		else
		{
			$video_server = '1';
			
			if(empty($this->rknclass->post['video_server_address']) || empty($this->rknclass->post['video_server_username']) || empty($this->rknclass->post['video_server_password']) || empty($this->rknclass->post['video_server_http']))
			{
				exit($this->rknclass->form->ajax_error('One or more required fields were left blank!'));
			}
			
			$data['video_server_address']  = $this->rknclass->post['video_server_address'];
			$data['video_server_username'] = $this->rknclass->post['video_server_username'];
			$data['video_server_password'] = $this->rknclass->post['video_server_password'];
			$data['video_server_http']     = $this->rknclass->post['video_server_http'];
		}
		
		if(!empty($this->rknclass->post['memcache_server_addr']))
		{
			$memcache_server = '1';
			
			if(empty($this->rknclass->post['memcache_server_port']))
			{
				exit($this->rknclass->form->ajax_error('One or more required fields were left blank!'));
			}
			
			$data['memcache_server_address'] = $this->rknclass->post['memcache_server_addr'];
			$data['memcache_server_port']    = $this->rknclass->post['memcache_server_port'];
		}
		else
		{
			$memcache_server = '0';
			@unlink(RKN__fullpath . "cache/settings/main.php");
		}
		
		$cluster_settings = @serialize($data);
		
		$this->rknclass->cache->update_settings_and_cache(array('thumb_server'      => $thumb_server,
		                                                        'video_server'      => $video_server,
																'memcache_server'   => $memcache_server,
																'cluster_settings'  => $cluster_settings));
		
		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}
	
	public function i18n_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
	    if(!isset($this->rknclass->post['date_format']) || empty($this->rknclass->post['date_format']))
	    {
            exit($this->rknclass->form->ajax_error('Invalid date format!'));
	    }
	    
	    if(!isset($this->rknclass->post['utf8_support']) || in_array($this->rknclass->post['utf8_support'], array('1', '-0')) === false)
	    {
	        exit($this->rknclass->form->ajax_error('Invalid data provded for UTF-8 support dropdown!'));
	    }
	    
	    $this->rknclass->post['url_translate_chars'] = trim($_POST['url_translate_chars']);
	    
	    if(preg_match('@(\'|")@',$this->rknclass->post['url_translate_chars']))
	    {
	        exit($this->rknclass->form->ajax_error('Quotes are automatically removed from urls!'));
	    }
	    
	    $chars = array();
	    
	    if(isset($this->rknclass->post['url_translate_chars']) AND !empty($this->rknclass->post['url_translate_chars']))
	    {
	        $lines = explode("\n", $this->rknclass->post['url_translate_chars']);
	        foreach($lines as $line)
	        {
	            list($original, $new) = explode('|', $line);
	            if(!empty($original))
	            {
	                $original = html_entity_decode($original, ENT_NOQUOTES, 'UTF-8');
	                $chars[$original] = $new;
	            }
	        }
	    }
	    
	    $chars = @serialize($chars);
	    
	    if(!isset($this->rknclass->post['default_timezone']) || empty($this->rknclass->post['default_timezone']))
	    {
	        exit($this->rknclass->form->ajax_error('Invalid default timezone entered!'));
	    }
	    
	    if($this->rknclass->post['utf8_support'] == '1')
	    {
	        $this->rknclass->db->query("ALTER TABLE " . TBLPRE . "plugs CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
	    }
	    
	    $this->rknclass->cache->update_settings_and_cache(
	    array('date_format'         => $this->rknclass->post['date_format'],
	          'utf8_support'        => $this->rknclass->post['utf8_support'],
	          'url_translate_chars' => $chars,
	          'default_timezone'    => $this->rknclass->post['default_timezone']));
	    
	    $this->rknclass->form->ajax_success('Successfully updated i18n settings!');
	}
	
	public function del_site_ban()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
	    if(!isset($this->rknclass->get['id']) || empty($this->rknclass->get['id']))
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid site id!'));
	    }
	    
	    $this->rknclass->db->query("SELECT url FROM " . TBLPRE . "banned_sites WHERE ban_id='{$this->rknclass->get['id']}'");
	    if($this->rknclass->db->num_rows() > 1)
	    {
	        exit($this->rknclass->global_tpl->admin_error('Site ban not found!'));
	    }
	    else
	    {
	        $url = $this->rknclass->db->result();
	    }
	    $this->rknclass->db->query("DELETE FROM " . TBLPRE . "banned_sites WHERE ban_id='{$this->rknclass->get['id']}' LIMIT 1");
	    $this->rknclass->db->query("OPTIMIZE TABLE " . TBLPRE . "banned_sites");
	    $this->rknclass->db->query("UPDATE " . TBLPRE . "sites SET banned='0' WHERE url='{$url}' LIMIT 1");
	    
	    $this->rknclass->global_tpl->exec_redirect('Site ban removed successfully', '?ctr=management');
	}
	
	public function ffmpeg_settings()
	{
	    if(!isset($this->rknclass->post['ffmpeg_enabled']) || !ctype_digit($this->rknclass->post['ffmpeg_enabled']) || ($this->rknclass->post['ffmpeg_enabled'] == '1' AND empty($this->rknclass->post['ffmpeg_location'])))
	    {
	        exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
	    }
	    
	    $this->rknclass->cache->update_settings_and_cache(array('ffmpeg_settings' => serialize(array('enabled' => $this->rknclass->post['ffmpeg_enabled'], 'binary_path' => $this->rknclass->post['ffmpeg_location']))));
	    $this->rknclass->form->ajax_success('Successfully updated FFMPEG settings!');
	}
	
	public function add_acp_restrictions()
	{
	    if(empty($this->rknclass->post['group_id']) || !ctype_digit($this->rknclass->post['group_id']))
	    {
	        exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
	    }
	    
	    $required = array('add_plug', 'edit_plugs', 'add_hvideo', 'add_evideo', 'edit_videos', 'add_blog', 'edit_blogs', 'own_content');
	    
	    foreach($required as $field)
	    {
	    	if(!isset($this->rknclass->post[$field]) || !in_array($this->rknclass->post[$field], array('0', '1')))
	        {
	            exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
	        }
	    }
	    
	    $result   = array();
	    $result[] = $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "groups WHERE group_id='{$this->rknclass->post['group_id']}' AND is_admin='0'");
	    $result[] = $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "acp_restrictions WHERE group_id='{$this->rknclass->post['group_id']}'");
	    
	    if($this->rknclass->db->result($result[0]) > 0 || $this->rknclass->db->result($result[1]) > 0)
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid group!'));
	    }
	    
	    $fields_sql = '';
	    
	    foreach($required as $field)
	    {
	        $fields_sql .= ", {$field} = '{$this->rknclass->post[$field]}'";
	    }
	    
	    $this->rknclass->db->query("INSERT INTO " . TBLPRE . "acp_restrictions SET group_id='{$this->rknclass->post['group_id']}'{$fields_sql}");
	    $this->rknclass->db->query("UPDATE " . TBLPRE . "groups SET is_restricted = '1' WHERE group_id='{$this->rknclass->post['group_id']}' LIMIT 1");
	    
	    $this->rknclass->cache->rebuild_groups_cache();
	    $this->rknclass->global_tpl->exec_redirect('Successfully added acp restrictions', '?ctr=management&act=view_acp_restrictions');
	}

	public function edit_group_restrictions()
	{
	    if(empty($this->rknclass->get['id']) || !ctype_digit($this->rknclass->get['id']))
	    {
	        exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
	    }
	    
		$this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "acp_restrictions WHERE group_id='{$this->rknclass->get['id']}'");
	    
	    if($this->rknclass->db->result() < 1)
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid group id!'));
	    }
	    
	    $required = array('add_plug', 'edit_plugs', 'add_hvideo', 'add_evideo', 'edit_videos', 'add_blog', 'edit_blogs', 'own_content');
	    
	    foreach($required as $field)
	    {
	    	if(!isset($this->rknclass->post[$field]) || !in_array($this->rknclass->post[$field], array('0', '1')))
	        {
	            exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
	        }
	    }
	    	    
	    foreach($required as $field)
	    {
	        if(!isset($fields_sql))
	        {
	            $fields_sql = "{$field} = '{$this->rknclass->post[$field]}'";
	        }
	        else
	        {
	            $fields_sql .= ", {$field} = '{$this->rknclass->post[$field]}'";
	        }
	    }
	    
	    $this->rknclass->db->query("UPDATE " . TBLPRE . "acp_restrictions SET {$fields_sql} WHERE group_id='{$this->rknclass->get['id']}' LIMIT 1");
	    $this->rknclass->cache->rebuild_groups_cache();
	    $this->rknclass->global_tpl->exec_redirect('Successfully updated acp restrictions', '?ctr=management&act=view_acp_restrictions');
	}
	
	public function del_group_restrictions()
	{
	    if(empty($this->rknclass->get['id']) || !ctype_digit($this->rknclass->get['id']))
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid group id!'));
	    }
	    
	    $this->rknclass->db->query("DELETE FROM " . TBLPRE . "acp_restrictions WHERE group_id = '{$this->rknclass->get['id']}'");
	    $this->rknclass->db->query("UPDATE " . TBLPRE . "groups SET is_restricted='0' WHERE group_id='{$this->rknclass->get['id']}'");
	    $this->rknclass->cache->rebuild_groups_cache();
	    $this->rknclass->global_tpl->exec_redirect('Successfully removed acp restrictions!', '?ctr=management&act=manage_groups');
	}
	
	public function seo_url_settings()
	{
	    if(!isset($this->rknclass->post['max_len']) || !ctype_digit($this->rknclass->post['max_len']))
	    {
	        exit($this->rknclass->form->ajax_error('Invalid value entered for max length!'));
	    }
	    
	    if(!isset($this->rknclass->post['cat']) || !in_array($this->rknclass->post['cat'], array('0', '1')))
	    {
	        exit($this->rknclass->form->ajax_error('Invalid value entered for category in url field!'));
	    }
	    
	    if(!isset($this->rknclass->post['seperator']) || !in_array($this->rknclass->post['seperator'], array('_', '-', '~')))
	    {
	        exit($this->rknclass->form->ajax_error('Invalid value entered for url seperator field!'));
	    }
	    
		if(!isset($this->rknclass->post['case_management']) || !in_array($this->rknclass->post['case_management'], range(0, 3)))
	    {
	        exit($this->rknclass->form->ajax_error('Invalid value entered for case management field!'));
	    }

	    $blacklist = array();
	    
	    if(!empty($this->rknclass->post['blacklist']))
	    {
	        $this->rknclass->post['blacklist'] = str_replace(array("\r\n", "\r"), "\n", $this->rknclass->post['blacklist']);
	        $words = explode('\n', $this->rknclass->post['blacklist']);
	        foreach($words as $word)
	        {
	            if(!empty($word))
	            {
	                $blacklist[] = trim(htmlspecialchars_decode($word, ENT_QUOTES));
	            }
	        }
	    }

	    $new = serialize(array('max_len'         => $this->rknclass->post['max_len'],
	                           'cat'             => $this->rknclass->post['cat'],
	                           'seperator'       => $this->rknclass->post['seperator'],
	                           'case_management' => $this->rknclass->post['case_management'],
	                           'blacklist'       => $blacklist));
	    
	    $seo_url_settings = $this->rknclass->db->escape($new);
	    
	    $this->rknclass->cache->update_settings_and_cache(array('seo_url_settings' => $seo_url_settings));
	    $this->rknclass->settings['seo_url_settings'] = unserialize($new);
	    $this->rknclass->utils->rebuild_seo_urls();
	    $this->rknclass->form->ajax_success('Successfully updated seo url settings');
	}
	
	public function submission_settings()
	{
	    foreach(array('title_min_words', 'title_max_words', 'descr_min_words', 'descr_max_words') as $field)
	    {
	        if(!isset($this->rknclass->post[$field]) || !ctype_digit($this->rknclass->post[$field]))
	        {
	            exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
	        }
	    }
	    
	    $blacklist = array();
	    
	    if(!empty($this->rknclass->post['blacklist']))
	    {
	        $this->rknclass->post['blacklist'] = str_replace(array("\r\n", "\r"), "\n", $this->rknclass->post['blacklist']);
	        $words = explode('\n', $this->rknclass->post['blacklist']);
	        foreach($words as $word)
	        {
	            if(!empty($word))
	            {
	                $blacklist[] = trim(htmlspecialchars_decode($word, ENT_QUOTES));
	            }
	        }
	    }

	    $submit_settings = serialize(array('title_min_words' => $this->rknclass->post['title_min_words'],
	                                        'title_max_words' => $this->rknclass->post['title_max_words'],
	                                        'descr_min_words' => $this->rknclass->post['descr_min_words'],
	                                        'descr_max_words' => $this->rknclass->post['descr_max_words'],
	                                        'blacklist'       => $blacklist));
	    
	    $submit_settings = $this->rknclass->db->escape($submit_settings);
	    
	    $this->rknclass->cache->update_settings_and_cache(array('submit_settings' => $submit_settings));
	    $this->rknclass->form->ajax_success('Successfully updated submission settings');
	}
}
?>