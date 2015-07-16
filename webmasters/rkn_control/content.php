<?php
class content extends rkn_render
{
	public function init()
	{
		if($this->rknclass->session->is_guest === true)
		{
			if($this->rknclass->get['ajax'] == '1')
			{
				exit('<strong><font color="red">Session Expired!</strong> - Please login again<br /><br /><strong>TIP:</strong> If you have just spent a while filing out a form, open a new browser window/tab, login and then go back to this window/tab and hit submit</font>');
			}
			else
			{
				exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
			}
		}
		
		$this->rknclass->load_objects(array('global_tpl', 'form', 'p3_archive'));
	}
	
	public function idx()
	{
		exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=content&act=submit_plug"));
	}
	
	public function submit_plug()
	{
	
		/*==============================
		The code below will redirect a
		user who hasn't added any sites
		yet to the 'Add Site' page
		===============================*/
		
		if($this->rknclass->user['total_sites']<1)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&act=add_site"));
		}
		
		/*========================
		First make sure that they
		haven't exceeded their
		groups submit limit
		=========================*/
		
		if($this->rknclass->user['group']['submit_limit'] !== '-1')
		{
			if($this->rknclass->settings['trade_24_method'] == '1')
			{
				$time = (int) strtotime(date('j F Y', strtotime('-1 day')) . ' 11:59pm');
			    $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "submit_log WHERE user_id='{$this->rknclass->user['user_id']}' AND time>{$time}");
			    if($this->rknclass->db->result() >= $this->rknclass->user['group']['submit_limit'])
			    {
			        exit($this->rknclass->global_tpl->webmasters_error('You have exceeded your 24 hour plug limit of ' . $this->rknclass->user['group']['submit_limit'] . ' plugs for this account!'));
			    }
			}
		}
		
		/*========================
		Right, lets get cracking!
		=========================*/
		
		$this->rknclass->page_title='New plug submission';
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->form->new_form('Content Submission');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_plug_submission');
		$this->rknclass->form->add_input('plug_url', 'input', 'Url of the Plug', 'Enter the full url to the plug here! This must start with <strong>http://</strong>. No homepage links!', 'http://');
		$this->rknclass->form->add_input('plug_title', 'input', 'Title of the Plug', 'Please enter the title of the plug you are currently submitting. This must be relevant to the actual content contained on the page');
		$this->rknclass->form->add_input('plug_description', 'textarea', 'Description of the Plug', 'Please enter a short, but detailed description of the plug. The better the description, the greater likelihood that you\'ll receive more traffic!');
		$this->rknclass->form->add_input('plug_tags', 'input', 'Plug Tags', 'Please enter a few tags for your plug. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>');
		$this->rknclass->form->add_input('plug_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail of your plug. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('plug_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		
		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box
		==========================*/
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE public='1' ORDER BY cat_name ASC");
		
		$categories='';
		
		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]</option>";
		}
		
		$this->rknclass->form->add_input('plug_category', 'dropdown', 'Select Category', 'Please select a category for your plug from the list', $categories);
		$this->rknclass->form->process();
		
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function process_plug_submission()
	{
		/*====================================
		The code below checks if the user
		has exceeded their 24 hour plug
		submission limit, if set via
		the admin control panel group settings
		=======================================*/
		
		if($this->rknclass->user['group']['submit_limit'] !== '-1')
		{
			if($this->rknclass->settings['trade_24_method'] == '1')
			{
			    /*
				$time=strtotime(date('j F Y', strtotime('-1 day')) . ' 11:59pm');
				$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE poster_id='" . $this->rknclass->user['user_id'] . "' AND posted>$time");
				if($this->rknclass->db->result() >= $this->rknclass->user['group']['submit_limit'])
				{
					exit($this->rknclass->global_tpl->webmasters_error('You have exceeded your 24 hour plug limit of ' . $this->rknclass->user['group']['submit_limit'] . ' plugs for this account!'));
				}
				*/
			    
			    $time = (int) strtotime(date('j F Y', strtotime('-1 day')) . ' 11:59pm');
			    $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "submit_log WHERE user_id='{$this->rknclass->user['user_id']}' AND time>{$time}");
			    if($this->rknclass->db->result() >= $this->rknclass->user['group']['submit_limit'])
			    {
			        exit($this->rknclass->global_tpl->webmasters_error('You have exceeded your 24 hour plug limit of ' . $this->rknclass->user['group']['submit_limit'] . ' plugs for this account!'));
			    }
			}
			else
			{
			    /*
				$time=strtotime(date('j F Y', strtotime('-1 day')) . ' 11:59pm');
				$check = $this->rknclass->utils->rkn_url_parser($this->rknclass->post['plug_url']);
				$count = 0;
				$this->rknclass->db->query("SELECT url FROM " . TBLPRE . "plugs WHERE poster_id='" . $this->rknclass->user['user_id'] . "' AND url LIKE '%$check%' AND posted>$time");
				while($row = $this->rknclass->db->fetch_array())
				{
					if($this->rknclass->utils->rkn_url_parser($row['url']) == $check)
					{
						$count++;
					}
				}
				*/
			    
			    $time  = (int) strtotime(date('j F Y', strtotime('-1 day')) . ' 11:59pm');
			    $check = $this->rknclass->db->escape($this->rknclass->utils->rkn_url_parser($this->rknclass->post['plug_url']));
			    
			    $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE ."submit_log WHERE site_url='{$check}' AND user_id='{$this->rknclass->user['user_id']}' AND time>{$time}");
				
			    $count = $this->rknclass->db->result();
			    
				if($count >= $this->rknclass->user['group']['submit_limit'])
				{
					exit($this->rknclass->global_tpl->webmasters_error('You have exceeded your 24 hour plug limit of ' . $this->rknclass->user['group']['submit_limit'] . ' plugs for this site!'));
				}
			}
		}
		$check=array('plug_url', 'plug_title', 'plug_description', 'plug_tags', 'plug_category');
		
		/*============================
		Fixes bug with urls containing
		the '&' character.
		=============================*/
		
		if(strpos($this->rknclass->post['plug_url'], '&amp') !== false)
		{
			$this->rknclass->post['plug_url']=str_replace('&amp;', '&', $this->rknclass->post['plug_url']);
			$this->rknclass->post['plug_url']=str_replace('&amp', '&', $this->rknclass->post['plug_url']);
		}
		
		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/
		
		foreach($check as $key)
		{
			if($this->rknclass->post['' . $key . ''] == '')
			{
				exit($this->rknclass->global_tpl->webmasters_error('One or more fields were left blank!'));
			}
		}
		
		$title_words = count(preg_split('@([\s]+)@', $this->rknclass->post['plug_title']));
		$descr_words = count(preg_split('@([\s]+)@', $this->rknclass->post['plug_description']));
		
		if($this->rknclass->settings['submit_settings']['title_min_words'] > 0 AND $title_words < $this->rknclass->settings['submit_settings']['title_min_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Title must contain at least {$this->rknclass->settings['submit_settings']['title_min_words']} words!"));
		}
		
		if($this->rknclass->settings['submit_settings']['title_max_words'] > 0 AND $title_words > $this->rknclass->settings['submit_settings']['title_max_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Title can only contain up to {$this->rknclass->settings['submit_settings']['title_max_words']} words!"));
		}
		
		if($this->rknclass->settings['submit_settings']['descr_min_words'] > 0 AND $descr_words < $this->rknclass->settings['submit_settings']['descr_min_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Description must contain at least {$this->rknclass->settings['submit_settings']['descr_min_words']} words!"));
		}
		
		if($this->rknclass->settings['submit_settings']['descr_max_words'] > 0 AND $descr_words > $this->rknclass->settings['submit_settings']['descr_max_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Description can only contain up to {$this->rknclass->settings['submit_settings']['descr_max_words']} words!"));
		}
		
		foreach($this->rknclass->settings['submit_settings']['blacklist'] as $word)
		{
		    if(stripos($this->rknclass->post['plug_title'], $word) !== false || stripos($this->rknclass->post['plug_description'], $word) !== false)
		    {
		        exit($this->rknclass->global_tpl->webmasters_error("The word <strong>{$word}</strong> has been blocked!"));
		    }
		}
		
		$approved=$this->rknclass->user['group']['plugs_approved'];

		/**
		 * @since 1.1.0
		 *
		 * Prevents users from submitting
		 * non-english, or "unwanted" characters
		 * such as strange unicode characters
		 * 
		 */
		 		
		if(!$this->rknclass->cleaner->safe_chars($this->rknclass->post['title']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('The title you are submitting contains one or more disallowed characters!'));
		}
		
		if(!$this->rknclass->cleaner->safe_chars($this->rknclass->post['description']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('The description you are submitting contains one or more disallowed characters!'));
		}
		
		/*========================
		Quick check to try and
		block attempted homepage
		plugs.
		=========================*/
		
		
		$check=@parse_url($this->rknclass->post['plug_url']);
		
		if(empty($check['query']))
		{
			$check=str_replace('/', '', $check['path']);
			
			if($check == '' || $check == 'index.php' || $check == 'index.html' || $check == 'index.asp' || $check == 'index.aspx')
			{
				exit($this->rknclass->global_tpl->webmasters_error('Attempted homepage plug'));
			}
		}
		
		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/
		
		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['plug_category']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid category'));
		}
		
		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later
		
		/*==============================
		Lets make sure that the site is
		valid, and they are the owner!!
		===============================*/
		
		$url = $this->rknclass->utils->rkn_url_parser($this->rknclass->post['plug_url']);
		
		if($url === false)
		{
			exit($this->rknclass->global_tpl->webmasters_error('An error occurred while attempting to process your site url. Please ensure you entered a valid url. <br /><br />Eg. <em>http://www.example.com/some-plug.html</em>'));
		}
		
		$query=$this->rknclass->db->build_query(array('select' => 'owner,u_total_in,u_total_out,banned,approved',
		                                              'from' => 'sites',
											          'where' => array('url' => $url),
											          'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This site does not exist in our trade system. Please add it first!'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot submit from this site as you are not its owner!'));
		}
		
		if($row['banned'] == '1')
		{
			exit($this->rknclass->global_tpl->webmasters_error('This site has been banned from our submit system'));
		}
		
		
		if($approved == '0')
		{
			if($row['approved'] !== '1')
			{
				exit($this->rknclass->global_tpl->webmasters_error('This site is still pending approval!'));
			}
		}
		
		if($approved == '0') //Only check if they arent set as pre-approved
		{
			/*================================
			Now, do they have a high enough
			ratio, or enough credits to post?
			=================================*/
			
			if($this->rknclass->settings['trade_calc_method'] == '0')
			{
				if($this->rknclass->utils->trade_check($row['u_total_in'], $row['u_total_out']) === false)
				{
					exit($this->rknclass->global_tpl->webmasters_error('Unfortunately you don\'t have enough credit/ratio to submit from this site at the moment!<br /><br />Please send us more hits before attempting to submit.'));
				}
			}
			else
			{
				if($this->rknclass->utils->trade_check_all() === false)
				{
					exit($this->rknclass->global_tpl->webmasters_error('Unfortunately you don\'t have enough credit/ratio to submit plugs from any of your sites at the moment!<br /><br />Please send us more hits before attempting to submit.'));
				}
			}
		}
		
		/*===================================
		Next, lets do a quick and basic check
		to make sure that the plug hasn't
		been submitted by the poster before
		====================================*/
		
		
		$query=$this->rknclass->db->build_query(array('select' => 'plug_id',
		                                              'from' => 'plugs',
													  'where' => array('url' => $this->rknclass->post['plug_url']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()>0)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug has already been submitted in our database'));
		}
		
		
		/*=================================
		Adds some basic protection against
		noobs trying to upload files that
		aren't images. To do: add pimp-slap
		==================================*/
		
		$remote = false;
		
		if(isset($this->rknclass->post['plug_image_remote']) AND !empty($this->rknclass->post['plug_image_remote']))
		{
			$remote = true;
		}
		
		if($remote === false)
		{
			if(!is_uploaded_file($_FILES['plug_image']['tmp_name']))
			{
				exit($this->rknclass->global_tpl->webmasters_error('You didn\'t upload an image!'));
			}
		}
		else
		{
			if(substr($this->rknclass->post['plug_image_remote'], 0, 7) !== 'http://')
			{
				exit($this->rknclass->global_tpl->webmasters_error('Invalid remote image url!'));
			}
			$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
			@copy($this->rknclass->post['plug_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->webmasters_error('Unable to rip remote image'));
		}
		
		
		($remote === false ? $info=@getimagesize($_FILES['plug_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));
		
		$allowed_images=array('image/jpeg', 'image/png', 'image/gif');
		
		if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid Image Type'));
		}
		
		if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
		{
			exit($this->rknclass->global_tpl->webmasters_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
		}
		
		$name=$this->rknclass->utils->rand_chars(7);
		
		if($info['mime'] === 'image/jpeg')
		{
			$name.='.jpg';
		}
		
		elseif($info['mime'] === 'image/png')
		{
			$name.='.png';
		}
		
		elseif($info['mime'] === 'image/gif')
		{
			$name .= '.gif';
		}
		
		if($remote === false)
			@move_uploaded_file($_FILES['plug_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->webmasters_error('Unable to store thumbnail - Please alert administration of this problem'));
		else
			@rename(RKN__fullpath . 'tmp/' . $rname, RKN__fullpath . 'tmp/' . $name);
			
		$plug_tags=$this->rknclass->utils->process_tags($this->rknclass->post['plug_tags']); //Makes sure the tags are formatted correctly
		
		$posted = $this->rknclass->utils->get_next_queue_ts();
		
		$query=$this->rknclass->db->build_query(array('insert' => 'plugs',
		                                              'set' => array('url' => $this->rknclass->post['plug_url'],
										 				             'title' => $this->rknclass->post['plug_title'],
														             'description' => $this->rknclass->post['plug_description'],
														             'tags' => $this->rknclass->post['plug_tags'],
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '1',
														             'poster' => $this->rknclass->user['username'],
														             'poster_id' => $this->rknclass->user['user_id'],
														             'posted' => $posted)));
		$this->rknclass->db->query($query);
		$insert_id = $this->rknclass->db->insert_id();
				
		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($insert_id, $this->rknclass->post['plug_title'], $cats['cat_name']));
		
		header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=cropper&id=" . $insert_id);
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$insert_id}' LIMIT 1");
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs+1, last_submit=" . time() . " WHERE user_id='{$this->rknclass->user['user_id']}'");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs=total_plugs+1 WHERE cat_name='{$cats['cat_id']}'");
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "submit_log SET site_url='{$this->rknclass->db->escape($this->rknclass->utils->rkn_url_parser($this->rknclass->post['plug_url']))}', user_id='{$this->rknclass->user['user_id']}', time='" . time() . "'");
	}
	
	/*===========================
	This section allows the user
	to edit a plug they've added
	previously to the system
	============================*/
	public function update_plug()
	{

		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid plug id'));
		}
		
		/*======================
		Checks to make sure that
		the currently logged in
		user was the original
		poster of the plug
		========================*/
		
		
		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug does not exist in our database'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($this->rknclass->user['group']['plug_edit_time'] !== '-1')
		{
			if(($row['posted'] + $this->rknclass->user['group']['plug_edit_time']) < time())
			{
				exit($this->rknclass->global_tpl->webmasters_error('Your allowed plug management time has expired'));
			}
		}	
		if($row['poster_id'] !== $this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot edit this plug as you weren\'t its original submitter.'));
		}
		
		$this->rknclass->page_title='Update plug';
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->form->new_form('Update plug');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_plug_submission&amp;id=' . $row[plug_id] . '');
		$this->rknclass->form->add_input('plug_url', 'input', 'Url of the Plug', 'Enter the full url to the plug here! This must start with <strong>http://</strong>. No homepage links!', $row['url']);
		$this->rknclass->form->add_input('plug_title', 'input', 'Title of the Plug', 'Please enter the title of the plug you are currently submitting. This must be relevant to the actual content contained on the page', $row['title']);
		$this->rknclass->form->add_input('plug_description', 'textarea', 'Description of the Plug', 'Please enter a short, but detailed description of the plug. The better the description, the greater likelihood that you\'ll receive more traffic!', $row['description']);
		$this->rknclass->form->add_input('plug_tags', 'input', 'Plug Tags', 'Please enter a few tags for your plug. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>', $row['tags']);
		$this->rknclass->form->add_input('plug_image', 'image', 'Upload and Crop Image', 'If you wish to change the plugs thumbnail, upload a new image</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('plug_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		
		
		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box and select
		the plugs current cat
		==========================*/
		
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE public='1' ORDER BY cat_name ASC");
		
		$categories='';
		
		while($row2=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row2[cat_id]\"" . ($row2['cat_id'] == $row['category_id'] ? " selected" : "") . ">$row2[cat_name]</option>";
		}
		$this->rknclass->form->add_input('plug_category', 'dropdown', 'Select Category', 'Please select a category for your plug from the list', $categories);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	/*=============================
	The section bellow is called
	when a user updates their plug
	===============================*/
	
	public function update_plug_submission()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid plug id'));
		}
		
		
		/*======================
		Checks to make sure that
		the currently logged in
		user was the original
		poster of the plug
		========================*/
		
		
		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug does not exist in our database'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		$original_url = $this->rknclass->utils->rkn_url_parser($row['url']);
		$new_url = $this->rknclass->utils->rkn_url_parser($this->rknclass->post['plug_url']);
		
		if($original_url !== $new_url)
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot change the site address when editing a plug!'));
		}
		$approved=$this->rknclass->user['group']['plugs_approved'];
		
		if($this->rknclass->user['group']['plug_edit_time'] !== '-1')
		{
			if(($row['posted'] + $this->rknclass->user['group']['plug_edit_time']) < time())
			{
				exit($this->rknclass->global_tpl->webmasters_error('Your allowed plug management time has expired'));
			}
		}	
			
		if($row['poster_id'] !== $this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot edit this plug as you weren\'t its original submitter.'));
		}	
			
		$check=array('plug_url', 'plug_title', 'plug_description', 'plug_tags', 'plug_category');
		
		/*============================
		Fixes bug with urls containing
		the '&' character.
		=============================*/
		
		if(strpos($this->rknclass->post['plug_url'], '&amp') !== false)
		{
			$this->rknclass->post['plug_url']=str_replace('&amp;', '&', $this->rknclass->post['plug_url']);
			$this->rknclass->post['plug_url']=str_replace('&amp', '&', $this->rknclass->post['plug_url']);
		}
		
		
		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/
		
		foreach($check as $key)
		{
			if($this->rknclass->post['' . $key . ''] == '')
			{
				exit($this->rknclass->global_tpl->webmasters_error('One or more fields were left blank!'));
			}
		}
		
		$title_words = count(preg_split('@([\s]+)@', $this->rknclass->post['plug_title']));
		$descr_words = count(preg_split('@([\s]+)@', $this->rknclass->post['plug_description']));
		
		if($this->rknclass->settings['submit_settings']['title_min_words'] > 0 AND $title_words < $this->rknclass->settings['submit_settings']['title_min_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Title must contain at least {$this->rknclass->settings['submit_settings']['title_min_words']} words!"));
		}
		
		if($this->rknclass->settings['submit_settings']['title_max_words'] > 0 AND $title_words > $this->rknclass->settings['submit_settings']['title_max_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Title can only contain up to {$this->rknclass->settings['submit_settings']['title_max_words']} words!"));
		}
		
		if($this->rknclass->settings['submit_settings']['descr_min_words'] > 0 AND $descr_words < $this->rknclass->settings['submit_settings']['descr_min_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Description must contain at least {$this->rknclass->settings['submit_settings']['descr_min_words']} words!"));
		}
		
		if($this->rknclass->settings['submit_settings']['descr_max_words'] > 0 AND $descr_words > $this->rknclass->settings['submit_settings']['descr_max_words'])
		{
		    exit($this->rknclass->global_tpl->webmasters_error("Description can only contain up to {$this->rknclass->settings['submit_settings']['descr_max_words']} words!"));
		}
		
		foreach($this->rknclass->settings['submit_settings']['blacklist'] as $word)
		{
		    if(stripos($this->rknclass->post['plug_title'], $word) !== false || stripos($this->rknclass->post['plug_description'], $word) !== false)
		    {
		        exit($this->rknclass->global_tpl->webmasters_error("The word <strong>{$word}</strong> has been blocked!"));
		    }
		}
		
		/**
		 * @since 1.1.0
		 *
		 * Prevents users from submitting
		 * non-english, or "unwanted" characters
		 * such as unicode characters
		 */
		 
		if(!$this->rknclass->cleaner->safe_chars($this->rknclass->post['title']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('The title you are submitting contains one or more disallowed characters!'));
		}
		
		if(!$this->rknclass->cleaner->safe_chars($this->rknclass->post['description']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('The description you are submitting contains one or more disallowed characters!'));
		}
				
		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/
		
		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['plug_category']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid category'));
		}
		
		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later
		
		/*==============================
		Lets make sure that the site is
		valid, and they are the owner!!
		===============================*/
		
		$url=$this->rknclass->utils->rkn_url_parser($this->rknclass->post['plug_url']);
		
		if($url === false)
		{
			$this->rknclass->global_tpl->webmasters_error('An error occurred while attempting to process your site url. Please ensure you entered a valid url. <br /><br />Eg. <em>http://www.example.com/some-plug.html</em>');
		}
		
		$query=$this->rknclass->db->build_query(array('select' => 'owner,u_total_in,u_total_out,banned,approved',
		                                              'from' => 'sites',
											          'where' => array('url' => $url),
											          'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This site does not exist in our trade system. Please add it first!'));
		}
		
		$row2=$this->rknclass->db->fetch_array();
		
		if($row2['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot submit from this site as you are not its owner!'));
		}
		
		if($row2['banned'] == '1')
		{
			exit($this->rknclass->global_tpl->webmasters_error('This site has been banned from our submit system'));
		}
		
		if($approved == '0')
		{
			if($row2['approved'] !== '1')
			{
				exit($this->rknclass->global_tpl->webmasters_error('This site is still pending approval!'));
			}
		}
		/*================================
		Now, do they have a high enough
		ratio, or enough credits to post?
		=================================*/
		
		if($approved == '0')
		{
			if($this->rknclass->utils->trade_check($row2['u_total_in'], $row2['u_total_out']) === false)
			{
				$this->rknclass->global_tpl->webmasters_error('Unfortunately you don\'t have enough credit/ratio to submit from this site at the moment!<br /><br />Please send us more hits before attempting to submit.');
			}
		}
		
		/*===================================
		Next, lets do a quick and basic check
		to make sure that the plug hasn't
		been submitted by the poster before
		====================================*/
		
		
		$query=$this->rknclass->db->build_query(array('select' => 'plug_id',
		                                              'from' => 'plugs',
													  'where' => array('url' => $this->rknclass->post['plug_url']),
													  'limit' => '1'));
		$this->rknclass->db->query("SELECT plug_id FROM " . TBLPRE . "plugs WHERE url='{$this->rknclass->post['plug_url']}' AND plug_id!='{$this->rknclass->get['id']}'");
		
		if($this->rknclass->db->num_rows()>0)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug has already been submitted in our database'));
		}
		
		if(is_uploaded_file($_FILES['plug_image']['tmp_name']) || !empty($this->rknclass->post['plug_image_remote']))
		{
		
			/*=================================
			If a new thumb has been uploaded,
			this section will check and process
			it, as well as removing the previous
			==================================*/
				
			$remote = false;
			
			if(isset($this->rknclass->post['plug_image_remote']) AND !empty($this->rknclass->post['plug_image_remote']))
			{
				$remote = true;
			}
			
			if($remote === true)
			{
				if(substr($this->rknclass->post['plug_image_remote'], 0, 7) !== 'http://')
				{
					exit($this->rknclass->global_tpl->webmasters_error('Invalid remote image url!'));
				}
				$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
				@copy($this->rknclass->post['plug_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->webmasters_error('Unable to rip remote image'));
			}
			
			
			($remote === false ? $info=@getimagesize($_FILES['plug_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));
			
			$allowed_images=array('image/jpeg', 'image/png', 'image/gif');
			
			if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
			{
				exit($this->rknclass->global_tpl->webmasters_error('Invalid Image Type'));
			}
			
			if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
			{
				exit($this->rknclass->global_tpl->webmasters_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
			}
			
			$name=$this->rknclass->utils->rand_chars(7);
			
			if($info['mime'] === 'image/jpeg')
			{
				$name.='.jpg';
			}
			
			elseif($info['mime'] === 'image/png')
			{
				$name.='.png';
			}
			
			elseif($info['mime'] === 'image/gif')
			{
				$name.-'.gif';
			}
			
			if($remote === false)
				@move_uploaded_file($_FILES['plug_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->webmasters_error('Unable to store thumbnail - Please alert administration of this problem'));
			else
				@rename(RKN__fullpath . 'tmp/' . $rname, RKN__fullpath . 'tmp/' . $name);
			//Ok, the current image is valid, lets delete the old one
			if($this->rknclass->settings['thumb_server'] == '0')
			{
				@unlink(RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb']) or $fucked_up=true;
			}
			else
			{
				@$handle = ftp_connect($this->rknclass->settings['cluster_settings']['thumb_server_address']);
				@ftp_login($handle, $this->rknclass->settings['cluster_settings']['thumb_server_username'], $this->rknclass->settings['cluster_settings']['thumb_server_password']);
				@ftp_delete($handle, $row['thumb']);
				@ftp_close($handle);
			}
			$cropped='0';
			if($fucked_up === true AND $this->rknclass->debug === true)
			{
				$this->rknclass->throw_debug_message('Unable to remove previous thumbnail, permission denied.' . RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' . $row[thumb]);
			}
		}
		else
		{
			$name=$row['thumb'];
			$cropped='1';
		}
		$plug_tags=$this->rknclass->utils->process_tags($plug_tags); //Makes sure the tags are formatted correctly
		
		$query=$this->rknclass->db->build_query(array('update' => 'plugs',
		                                              'set' => array('url' => $this->rknclass->post['plug_url'],
										 				             'title' => $this->rknclass->post['plug_title'],
														             'description' => $this->rknclass->post['plug_description'],
														             'tags' => $this->rknclass->post['plug_tags'],
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '1',
																	 'cropped' => $cropped),
													  'where' => array('plug_id' => $row['plug_id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($row['plug_id'], $this->rknclass->post['plug_title'], $cats['cat_name']));
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$row['plug_id']}' LIMIT 1");
		
		/*=============================
		Now that we got all that done,
		lets redirect them back to the
		webmasters area, or if there's
		a thumb, to the cropping area.
		===============================*/
		
		if($name !== $row[thumb])
		{
			header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=cropper&id={$this->rknclass->get['id']}");	
		}
		else
		{
			$this->rknclass->global_tpl->exec_redirect('Plug successfully updated!', '?ctr=content&act=my_content');
		}
	}

	public function delete_plug()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid plug id'));
		}
		
		/*======================
		Checks to make sure that
		the currently logged in
		user was the original
		poster of the plug
		========================*/
		
		
		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug does not exist in our database'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($this->rknclass->user['group']['plug_edit_time'] !== '-1')
		{
			if(($row['posted'] + $this->rknclass->user['group']['plug_edit_time']) < time())
			{
				exit($this->rknclass->global_tpl->webmasters_error('Your allowed plug management time has expired'));
			}
		}	
		
		if($row['poster_id'] !== $this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot delete this plug as you weren\'t its original submitter.'));
		}
		
		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs=total_plugs-1 WHERE cat_id='{$row['category_id']}' LIMIT 1");
		@unlink(RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb']);
		exit($this->rknclass->global_tpl->exec_redirect('Plug removed successfully!', '?ctr=content&act=my_content'));
	}
		
	/*=============================
	The section below will display
	the listings of all content
	submitted by the currently
	logged in user.
	==============================*/
	
	public function my_content()
	{
		$this->rknclass->page_title='My Submitted Content';
		if($this->rknclass->user['total_sites']<1)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&act=add_site"));
		}
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=25; //TODO: Add option in ACP
		
		
		/*========================
		Query below will set our
		own value for the pager
		=========================*/
		
		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE poster_id='{$this->rknclass->user['user_id']}'");
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->global_tpl->webmasters_header();
		echo '
        <div class="page-title">Your Content</div>
        
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Views</th>
    <th scope="col">Category</th>
    <th scope="col">Posted</th>
    <th scope="col">Chosen</th>
    <th scope="col">Approved</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
	<th scope="col">Export</th>
  </tr>';
  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE poster_id='{$this->rknclass->user['user_id']}' AND type='1' ORDER BY posted DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		
		while($row=$this->rknclass->db->fetch_array())
		{
			$plug_edit=true;
			if($this->rknclass->user['group']['plug_edit_time'] !== '-1')
			{
				if(($row['posted'] + $this->rknclass->user['group']['plug_edit_time']) < time())
				{
					$plug_edit=false;
				}
			}
			echo "<tr id=\"rows\">
    <td id=\"title\">" . (strlen($row[title]) >= 50 ? substr($row[title], 0, 46) . "..." : $row[title]) . "</td>
    <td>$row[views]</td>
    <td>$row[category]</td>
    <td>" . $this->rknclass->utils->timetostr($row[posted]) ."</td>
    <td>" . ($row['chosen'] == '1' ? "<strong><font color=\"#136f01\">Yes</font></strong>" : "<strong><font color=\"#e32c00\">No</font></strong>") . " </td>
    <td>" . ($row['approved'] == '1' ? "<strong><font color=\"#136f01\">Yes</font></strong>" : "<strong><font color=\"#e32c00\">No</font></strong>") . " </td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=content&amp;act=update_plug&amp;id=$row[plug_id]\"" . ($plug_edit === false ?" onclick=\"alert('Allowed editing time has expired for this plug!'); return false;\"" : "") . "><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=content&amp;act=delete_plug&amp;id=$row[plug_id]\"" . ($plug_edit === false ?" onclick=\"alert('Allowed editing time has expired for this plug!'); return false;\"" : " onclick=\"return confirm('Are you sure you wish to permanently delete this plug?');\"") . "><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
	 <td><a href=\"{$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=content&amp;act=download_p3&amp;id=$row[plug_id]\"><img src=\"images/p3.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=my_content&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=my_content&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->webmasters_footer();
	}

	public function our_content()
	{
		$this->rknclass->page_title='Plug our content!';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=25; //TODO: Add option in ACP
		
		
		/*========================
		Query below will set our
		own value for the pager
		=========================*/
		
		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type!='1' AND posted<" . time() . " AND approved='1'");
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->global_tpl->webmasters_header();
		echo "<script type=\"text/javascript\">
// This is variable for storing callback function
var ae_cb = null;
 
// this is a simple function-shortcut
// to avoid using lengthy document.getElementById
function ae$(a) { return document.getElementById(a); }
 
// This is a main ae_prompt function
// it saves function callback 
// and sets up dialog
function ae_prompt(cb, q, a) {
	ae_cb = cb;
	ae$('aep_prompt').innerHTML = q;
	ae$('aep_text').value = a;
	ae$('aep_ovrl').style.display = ae$('aep_ww').style.display = '';
	ae$('aep_text').focus();
	ae$('aep_text').select();
}
 
// This function is called when user presses OK(m=0) or Cancel(m=1) button
// in the dialog. You should not call this function directly.
function ae_close() {
	// hide dialog layers 
	ae$('aep_ovrl').style.display = ae$('aep_ww').style.display = 'none';
}
</script>
<style type=\"text/css\">
#aep_ovrl {
background-color: black;
-moz-opacity: 0.7; opacity: 0.7;
top: 0; left: 0; position: fixed;
width: 100%; height:100%; z-index: 99;
}
#aep_ww { position: fixed; z-index: 100; top: 0; left: 0; width: 100%; height: 100%; text-align: center;}
#aep_win { margin: 20% auto 0 auto; width: 400px; text-align: left;}
#aep_w {background-color: white; padding: 3px; border: 1px solid black; background-color: #EEE;}
#aep_text {width: 100%;}
#aep_w span {font-family: Arial, sans-serif; font-size: 10pt;}
#aep_w div {text-align: right; margin-top: 5px;}
</style>
<!-- IE specific code: -->
<!--[if lte IE 7]> 
<style type=\"text/css\"> 
#aep_ovrl { 
position: absolute; 
filter:alpha(opacity=70); 
top: expression(eval(document.body.scrollTop)); 
width: expression(eval(document.body.clientWidth)); 
} 
#aep_ww {  
position: absolute;  
top: expression(eval(document.body.scrollTop));  
} 
</style> 
<![endif]-->	
<!-- ae_prompt HTML code -->
<div id=\"aep_ovrl\" style=\"display: none;\">&nbsp;</div>
<div id=\"aep_ww\" style=\"display: none;\">
<div id=\"aep_win\"><div id=\"aep_t\"></div>
<div id=\"aep_w\"><span id=\"aep_prompt\"></span>
<br /><input type=\"text\" id=\"aep_text\">
<br><div><input type=\"button\" id=\"aep_ok\" onclick=\"ae_close();\" value=\"OK\">
</div></div>
</div>
</div>
<!-- ae_prompt HTML code -->";
		echo '
        <div class="page-title">Our Content</div>
        
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Views</th>
    <th scope="col">Category</th>
    <th scope="col">Posted</th>
	<th scope="col">Poster</th>
	<th scope="col">Link it!</th>
	<th scope="col">Thumb</th>
  </tr>';
  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type!='1' AND posted<" . time() . " AND approved='1' ORDER BY posted DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		
		while($row=$this->rknclass->db->fetch_array())
		{
			$thumbnail_src = $this->rknclass->settings['site_url'] . '/' . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb'];
			
			if($this->rknclass->settings['thumb_server'] == '1')
			{
			    $thumbnail_src = $this->rknclass->cluster_settings['thumb_server_http'] . '/' . $row['thumb'];
			}
			$plug_edit=true;
			if($this->rknclass->user['group']['plug_edit_time'] !== '-1')
			{
				if(($row['posted'] + $this->rknclass->user['group']['plug_edit_time']) < time())
				{
					$plug_edit=false;
				}
			}
			echo "<tr id=\"rows\">
    <td id=\"title\"><img src=\"images/type-video.jpg\" id=\"content-icon\" title=\"Video\" /><a href=\"{$this->rknclass->settings['site_url']}/{$row['seo_url']}.html\" target=\"_blank\">" . (strlen($row['title']) >= 50 ? substr($row['title'], 0, 46) . "..." : $row['title']) . "</a></td>
    <td>{$row['views']}</td>
    <td>{$row['category']}</td>
	<td>" . $this->rknclass->utils->timetostr($row['posted']) ."</td>
	<td>{$row['poster']}</td>
	<td><a href=\"#\" onclick=\"ae_prompt('NULL', 'Copy the video url from the box below', '" . ($this->rknclass->settings['seo_urls'] == '1' ?  "{$this->rknclass->settings['site_url']}/{$row['seo_url']}.html" : $this->rknclass->settings['site_url'] . "/index.php?ctr=view&amp;id={$row['plug_id']}") . "');\">Get Url</a></td>
	<td><a href=\"{$this->rknclass->settings['site_url']}/{$this->rknclass->settings['thumb_dir']}/{$row['thumb']}\" onclick=\"window.open('{$thumbnail_src}', 'Content Thumbnail',
    'width={$this->rknclass->settings['thumb_width']},height={$this->rknclass->settings['thumb_height']},scrollbars=no'); return false\">Get thumbnail</td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=our_content&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=our_content&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->webmasters_footer();
	}
		
	public function download_p3()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid plug id'));
		}
		
		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug does not exist in our database'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['poster_id'] !== $this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot export this plug as you weren\'t its original submitter.'));
		}
		$this->rknclass->p3_archive->generate($this->rknclass->get['plug_id']);
	}

	public function upload_p3()
	{
	
		/*==============================
		The code below will redirect a
		user who hasn't added any sites
		yet to the 'Add Site' page
		===============================*/
		
		if($this->rknclass->user['total_sites']<1)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&act=add_site"));
		}
		
		/*========================
		First make sure that they
		haven't exceeded their
		groups submit limit
		=========================*/
		
		if($this->rknclass->user['group']['submit_limit'] !== '-1')
		{
			$time=time() - 86400;
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE poster_id='" . $this->rknclass->user['user_id'] . "' AND posted>$time");
			if($this->rknclass->db->result() >= $this->rknclass->user['group']['submit_limit'])
			{
				exit($this->rknclass->global_tpl->webmasters_error('You have exceeded your 24 hour plug limit of ' . $this->rknclass->user['group']['submit_limit'] . ' plugs for this account!'));
			}
		}
		
		/*========================
		Right, lets get cracking!
		=========================*/
		
		$this->rknclass->page_title='Upload .p3 archive';
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->form->new_form('Content Submission');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_p3_submission');
		$this->rknclass->form->add_input('p3_archive', 'file', 'Upload Predator Plug Pack (.p3)', 'Please upload your Ranakin .p3 archive using this field. You can generate a .p3 archive on any professional site or network using Predator as the backend.');
		
		
		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box
		==========================*/
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE public='1' ORDER BY cat_name ASC");
		
		$categories='';
		
		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]</option>";
		}
		$this->rknclass->form->add_input('plug_category', 'dropdown', 'Select Category', 'Please select a category for your plug from the list', $categories);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function process_p3_submission()
	{
		/*====================================
		The code below checks if the user
		has exceeded their 24 hour plug
		submission limit, if set via
		the admin control panel group settings
		=======================================*/
		
		if($this->rknclass->user['group']['submit_limit'] !== '-1')
		{
			$time=strtotime(date('j F Y', strtotime('-1 day')) . ' 11:59pm');
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE poster_id='" . $this->rknclass->user['user_id'] . "' AND posted>$time");
			if($this->rknclass->db->result() >= $this->rknclass->user['group']['submit_limit'])
			{
				exit($this->rknclass->global_tpl->webmasters_error('You have exceeded your 24 hour plug limit of ' . $this->rknclass->user['group']['submit_limit'] . ' plugs for this account!'));
			}
		}
		
		$approved=$this->rknclass->user['group']['plugs_approved'];
		$check=array('plug_category');

		
		/*========================
		Process uploaded .p3 file
		==========================*/
		
		if(!file_exists($_FILES['p3_archive']['tmp_name']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('One or more fields were left blank!'));
		}
		
		$p3=$this->rknclass->p3_archive->parse_predator_plug_pack('file', $_FILES['p3_archive']['tmp_name']);
				
		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/
		
		if(empty($this->rknclass->post['plug_category']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('You must select a valid category for your .p3 archive!'));
		}
		
		/*========================
		Quick check to try and
		block attempted homepage
		plugs.
		=========================*/
		

		$check=parse_url($p3['PLUG_VALUES']['url']);
		
		if($check['query'] == '')
		{
			$check=str_replace('/', '', $check['path']);
			
			if($check == '' || $check == 'index.php' || $check == 'index.html' || $check == 'index.asp' || $check == 'index.aspx')
			{
				exit($this->rknclass->global_tpl->webmasters_error('Attempted homepage plug'));
			}
		}
		
		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/
		
		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['plug_category']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid category'));
		}
		
		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later
		
		/*==============================
		Lets make sure that the site is
		valid, and they are the owner!!
		===============================*/
		
		$url=$this->rknclass->utils->rkn_url_parser($p3['PLUG_VALUES']['url']);
		
		if($url === false)
		{
			exit($this->rknclass->global_tpl->webmasters_error('An error occurred while attempting to process your site url. Please ensure you entered a valid url. <br /><br />Eg. <em>http://www.example.com/some-plug.html</em>'));
		}
		
		$query=$this->rknclass->db->build_query(array('select' => 'owner,u_total_in,u_total_out,banned,approved',
		                                              'from' => 'sites',
											          'where' => array('url' => $url),
											          'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This site does not exist in our trade system. Please add it first!'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['owner']!==$this->rknclass->user['user_id'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('You cannot submit from this site as you are not its owner!'));
		}
		
		if($row['banned'] == '1')
		{
			exit($this->rknclass->global_tpl->webmasters_error('This site has been banned from our submit system'));
		}
		
		
		if($approved == '0')
		{
			if($row['approved'] !== '1')
			{
				exit($this->rknclass->global_tpl->webmasters_error('This site is still pending approval!'));
			}
		}
		
		if($approved == '0') //Only check if they arent set as pre-approved
		{
			/*================================
			Now, do they have a high enough
			ratio, or enough credits to post?
			=================================*/
			
			if($this->rknclass->settings['trade_calc_method'] == '0')
			{
				if($this->rknclass->utils->trade_check($row['u_total_in'], $row['u_total_out']) === false)
				{
					exit($this->rknclass->global_tpl->webmasters_error('Unfortunately you don\'t have enough credit/ratio to submit from this site at the moment!<br /><br />Please send us more hits before attempting to submit.'));
				}
			}
			else
			{
				if($this->rknclass->utils->trade_check_all() === false)
				{
					exit($this->rknclass->global_tpl->webmasters_error('Unfortunately you don\'t have enough credit/ratio to submit plugs from any of your sites at the moment!<br /><br />Please send us more hits before attempting to submit.'));
				}
			}
		}
		
		/*===================================
		Next, lets do a quick and basic check
		to make sure that the plug hasn't
		been submitted by the poster before
		====================================*/
		
		
		$query=$this->rknclass->db->build_query(array('select' => 'plug_id',
		                                              'from' => 'plugs',
													  'where' => array('url' => $p3['PLUG_VALUES']['url']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);
		
		if($this->rknclass->db->num_rows()>0)
		{
			exit($this->rknclass->global_tpl->webmasters_error('This plug has already been submitted in our database'));
		}
		
		$plug_tags=$this->rknclass->utils->process_tags($p3['PLUG_VALUES']['tags']); //Makes sure the tags are formatted correctly
		
		$query=$this->rknclass->db->build_query(array('insert' => 'plugs',
		                                              'set' => array('url' => $p3['PLUG_VALUES']['url'],
										 				             'title' => $p3['PLUG_VALUES']['title'],
														             'description' => $p3['PLUG_VALUES']['description'],
														             'tags' => $plug_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $p3['THUMB_INFO']['thumb_name'],
																	 'type' => '1',
														             'poster' => $this->rknclass->user['username'],
														             'poster_id' => $this->rknclass->user['user_id'],
																	 'cropped' => '1',
																	 'approved' => $this->rknclass->user['group']['plugs_approved'],
														             'posted' => time())));
		$this->rknclass->db->query($query);
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs+1 WHERE user_id='{$this->rknclass->user['user_id']}'");
		exit($this->rknclass->global_tpl->exec_redirect('Archive added successfully!', '?ctr=content&act=my_content'));
	}
	
}
?>