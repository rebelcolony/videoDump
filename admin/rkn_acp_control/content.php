<?php
define('RKN__admintab', 'content');
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
				exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?return_url=" . $this->rknclass->utils->page_url()));
			}
		}

		if($this->rknclass->user['group']['is_admin'] !== '1')
		{
			exit('You must be an admin to access this area!');
		}

		$this->rknclass->load_objects(array('global_tpl', 'form', 'p3_archive'));

		if($this->rknclass->user['group']['is_restricted'] == '1')
		{
		    $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "acp_restrictions WHERE group_id='{$this->rknclass->user['group_id']}'");
		    if($this->rknclass->db->num_rows() < 1)
		    {
		        exit($this->rknclass->global_tpl->admin_error('Database error'));
		    }

		    $this->rknclass->user['restrictions'] = $this->rknclass->db->fetch_array();

		    $url_restriction_mapping = array('add_plug'    => array('add_plug', 'process_plug_submission'),
		                                     'edit_plugs'  => array('view_plugs', 'update_plug', 'update_plug_submission', 'delete_content', 'update_frame', 'update_chosen', 'update_approved'),
		                                     'add_hvideo'  => array('add_hosted_video', 'process_hosted_video_submission'),
		                                     'add_evideo'  => array('add_embedded_video', 'process_embedded_video_submission'),
		                                     'edit_videos' => array('view_videos', 'update_hosted_video', 'update_embedded_video', 'update_hosted_video_process', 'process_embedded_video_update'),
		                                     'add_blog'    => array('create_blog_entry', 'process_article_submission', 'delete_content'),
		                                     'edit_blogs'  => array('view_blog_entries', 'edit_blog_entry', 'update_blog_entry', 'delete_content'));

		    $permitted = array('idx');

		    foreach($url_restriction_mapping as $perm_key => $allowed_methods)
		    {
		        if($this->rknclass->user['restrictions'][$perm_key] == '1')
		        {
		            $permitted = array_merge($permitted, $allowed_methods);
		        }
		    }

		    if(isset($this->rknclass->get['act']) AND !in_array($this->rknclass->get['act'], $permitted))
		    {
		        exit($this->rknclass->global_tpl->admin_error('You do not have permission to access this area!'));
		    }
		}
	}

	public function idx()
	{
		$this->rknclass->page_title='Content Homepage';
		$this->rknclass->global_tpl->admin_header();
		echo "<script language=\"javascript\">
    <!--

    function ClearAdminNotes (){
      if (confirm('Are you sure you want to clear the admin notepad?.'))
       {
          document.notes.notes_box.value='';
       }
      return true;
    }

    //-->
    </script>";
		echo "<table cellpadding=\"0\" cellspacing=\"0\" id=\"first-row\" valign=\"top\">
            <tr valign=\"top\">
            <td class=\"dash-title-left\">
            Last 5 Submitted Plugs
            </td>
            <td class=\"dash-title-right\">
            Admin Note Pad
            </td>
            </tr>
            <tr>
            <td valign=\"top\">
            <table width=\"350px\" cellpadding=\"0\" cellspacing=\"0\">
			    <tr id=\"last-5-plugs-header\">
                	<th id=\"last-5-plugs-title\">
                    	Title
                    </th>
                	<th>
                    	Poster
                    </th>
                	<th>
                    	Clicks
                    </th>
                	<th>
                    	Approved
                    </th>
                	<th>
                    	Edit
                    </th>
                </tr>";

			$this->rknclass->db->query("SELECT plug_id,title,poster_id,poster,views,approved FROM " . TBLPRE . "plugs WHERE type='1' ORDER BY posted DESC LIMIT 5");
			while($row=$this->rknclass->db->fetch_array())
			{
            	echo "<tr id=\"last-5-plugs-main\">
                	<td id=\"last-5-plugs-main-title\">
                    	" . (strlen($row['title'])>25 ? substr($row['title'], 0,24) . '...' : $row['title']) . "
                    </td>
                	<td>
                    	{$row['poster']}
                    </td>
                	<td>
                    	{$row['views']}
                    </td>
                	<td>
                    	" . (intval($row['approved']) === 1 ? 'Yes' : 'No') . "
                    </td>
                	<td>
                    	<a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=content&amp;act=update_plug&amp;id={$row['plug_id']}\"><img src=\"images/pencil.jpg\" border=\"0\"/></a>
                    </td>
                </tr>";
			}
			echo "
            </table>
            <table cellpading=\"0\" cellspacing=\"0\">
             <tr>
                <td id=\"latest-bottom\" align=\"right\">
				<img src=\"images/green-square.jpg\" /><a href=\"index.php?ctr=content&amp;act=plugs_pending_approval\" class=\"stats-link\">View Plugs Pending Approval</a>
                </td>
                </tr>
            </table>
            </td>";
			$this->rknclass->db->query("SELECT note_id,content FROM " . TBLPRE . "notes WHERE name='admin_dashboard' LIMIT 1");
			$row=$this->rknclass->db->fetch_array();
			echo"
            <td>
            	<table width=\"350px\" cellpadding=\"0\" cellspacing=\"0\">
            		<tr>
            			<td id=\"admin-notes-header\">
            			</td>
            		</tr>
            		<tr>
            			<td id=\"admin-notes-bg\">
                        <div id=\"notes-sum\">Enter any information for yourself or other admins to view below....</div>
                        <form id=\"notes\" name=\"notes\" action=\"index.php?ctr=content&amp;act=update_admin_notes\" style=\"display: inline; margin: 0;\" method=\"post\">
                        <textarea name=\"note\" id=\"notes_box\">{$row['content']}</textarea>
                        <div id=\"notes-buttons\"><a href=\"#\" onclick=\"ClearAdminNotes(); return false;\"><img src=\"images/form-clear.jpg\" border=\"0\" /></a>&nbsp;<input type=\"image\" name=\"Submit\" src=\"images/form-submit.jpg\" /></div>
                        </form>
            			</td>
            		</tr>
            	</table>
            </td>
            </tr>
            </table>";

			//Second row
			echo "<table cellpadding=\"0\" cellspacing=\"0\" id=\"second-row\">
            <tr>
            <td class=\"dash-title-left\">
            Latest Users
            </td>
            <td class=\"dash-title-right\">
            Today's Top 5 Referrers
            </td>
            </tr>
            <tr>
            <td>
            <table width=\"350px\" cellpadding=\"0\" cellspacing=\"0\">
            	<tr id=\"admin-users-header\">
                	<th>
                    	&nbsp;&nbsp;&nbsp;Username
                    </th>
                	<th>
                    	Join Date
                    </th>
                	<th>
                    	Edit
                    </th>
                </tr>
                ";
				$this->rknclass->db->query("SELECT username,user_id,joined FROM " . TBLPRE . "users ORDER BY joined DESC LIMIT 5");
				while($row=$this->rknclass->db->fetch_array())
				{
            		echo "<tr id=\"admin-users-main\">
                	<td id=\"last-5-plugs-main-title\">
                    	{$row['username']}
                    </td>
                	<td id=\"latest-users-join-date\">
                    	" . date("F jS, Y", $row['joined']) . "
                    </td>
                 	<td>
                    	<a href=\"index.php?ctr=management&amp;act=edit_user&amp;id={$row['user_id']}\"><img src=\"images/pencil.jpg\" border=\"0\"/></a>
                    </td>
                </tr>";
				}
 echo "
            </table>
            <table cellpading=\"0\" cellspacing=\"0\">
             <tr>
                <td id=\"latest-bottom\" align=\"right\">
				<img src=\"images/green-square.jpg\" /><a href=\"index.php?ctr=management&amp;act=manage_users\" class=\"stats-link\">View all Users</a>
                </td>
                </tr>
            </table>
            </td>
            <td>
            <table width=\"350px\" cellpadding=\"0\" cellspacing=\"0\">
            	<tr id=\"admin-users-header\">
                	<th>
                    	&nbsp;&nbsp;&nbsp;Referrer
                    </th>
                	<th align=\"right\">
                    	Todays:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;In&nbsp;
                    </th>
                	<th>
                    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Out
                    </th>
                    <th align=\"left\">
                        Ratio
                    </th>
                </tr>";

				$this->rknclass->db->query("SELECT url, u_todays_in, u_todays_out FROM " . TBLPRE . "sites WHERE owner > 0 ORDER BY u_todays_in DESC LIMIT 5");
				while($row=$this->rknclass->db->fetch_array())
				{
					$ratio=@ceil(($row['u_todays_in']/$row['u_todays_out'])*100);

					if($ratio == 0 AND $row['u_todays_out'] == 0)
					{
						$ratio=100;
					}

				echo"
                <tr id=\"admin-users-main\">
                	<td id=\"last-5-plugs-main-title\">
                    	www.{$row['url']}/
                    </td>
                	<td align=\"right\">
                    	{$row['u_todays_in']}&nbsp;&nbsp;
                    </td>
                	<td align=\"center\">
                    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$row['u_todays_out']}
                    </td>
                	<td align=\"left\">
                    	&nbsp;&nbsp;&nbsp;" . ($ratio<$this->rknclass->settings['trade_min_ratio'] ? "<strong><font color=\"red\">{$ratio}%</font></strong>" : "<strong><font color=\"green\">{$ratio}%</font></strong>") . "
                    </td>
                </tr>
				";
				}

			echo "
            </table>
            <table cellpading=\"0\" cellspacing=\"0\">
             <tr>
                <td id=\"latest-bottom\" align=\"right\">
				<img src=\"images/green-square.jpg\" /><a href=\"index.php?ctr=management&amp;act=view_sites\" class=\"stats-link\">View all referrers</a>
                </td>
                </tr>
            </table>
            </td>
            </tr>
            </table>";
		$this->rknclass->global_tpl->admin_footer();
	}

	public function add_plug()
	{

		/*========================
		Right, lets get cracking!
		=========================*/

		$this->rknclass->page_title='New plug submission';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Content Submission');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_plug_submission');
		$this->rknclass->form->add_input('plug_url', 'input', 'Url of the Plug', 'Enter the full url to the plug here! This must start with <strong>http://</strong>', 'http://');
		$this->rknclass->form->add_input('plug_title', 'input', 'Title of the Plug', 'Please enter the title of the plug you are currently submitting');
		$this->rknclass->form->add_input('plug_description', 'textarea', 'Description of the Plug', 'Please enter a short, but detailed description of the plug. The better the description, the greater likelihood that you\'ll receive more traffic!');
		$this->rknclass->form->add_input('plug_tags', 'input', 'Plug Tags', 'Please enter a few tags for your plug. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>');
		$this->rknclass->form->add_input('plug_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail of your plug. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('plug_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');


		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box
		==========================*/

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('plug_category', 'dropdown', 'Select Category', 'Please select a category for your plug from the list', $categories);
		$this->rknclass->form->add_input('plug_schedule', 'input', 'Schedule', 'If you would like to schedule your plug to appear at a specific time, please enter it here', date('j M Y g:i:sa', time()));

		if($this->rknclass->settings['queue_time'] > 0)
		{
		    $this->rknclass->form->add_input('queue_obey', 'dropdown', 'Add to queue?', 'Please select whether or not you want this plug to be added to the content queue system<br /><br /><strong>If set to yes, the field above will have no effect!</strong>', '<option value="0">No</option><option value="1" SELECTED>Yes</option>');
		}

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_plug_submission()
	{

		/*============================
		Fixes bug with urls containing
		the '&' character.
		=============================*/

		if(strpos($this->rknclass->post['plug_url'], '&amp') !== false)
		{
			$this->rknclass->post['plug_url']=str_replace('&amp;', '&', $this->rknclass->post['plug_url']);
			$this->rknclass->post['plug_url']=str_replace('&amp', '&', $this->rknclass->post['plug_url']);
		}

		$check=array('plug_url', 'plug_title', 'plug_description', 'plug_tags', 'plug_category', 'plug_schedule');

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['plug_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

		$remote = false;

		if(isset($this->rknclass->post['plug_image_remote']) AND !empty($this->rknclass->post['plug_image_remote']))
		{
			$remote = true;
		}

		if($remote === false)
		{
			if(!is_uploaded_file($_FILES['plug_image']['tmp_name']))
			{
				exit($this->rknclass->global_tpl->admin_error('You didn\'t upload an image!'));
			}
		}
		else
		{
			if(substr($this->rknclass->post['plug_image_remote'], 0, 7) !== 'http://')
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
			}
			$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
			@copy($this->rknclass->post['plug_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
		}


		($remote === false ? $info=@getimagesize($_FILES['plug_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

		$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

		if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid Image Type'));
		}

		if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
		{
			exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
		{
			@move_uploaded_file($_FILES['plug_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
		}
		else
		{
			@rename(RKN__fullpath . 'tmp/' . $rname, RKN__fullpath . 'tmp/' . $name);
		}

		$plug_tags=$this->rknclass->utils->process_tags($plug_tags); //Makes sure the tags are formatted correctly

		if(isset($this->rknclass->post['queue_obey']) AND $this->rknclass->post['queue_obey'] == '1')
		{
		    $posted = $this->rknclass->utils->get_next_queue_ts();
		}
		else
		{
		    $posted = strtotime($this->rknclass->post['plug_schedule']);
		    if($posted === false)
		    {
		        exit($this->rknclass->form->admin_error('Invalid data entered for plug schedule field!'));
		    }
		}

		$query=$this->rknclass->db->build_query(array('insert' => 'plugs',
		                                              'set' => array('url' => $plug_url,
										 				             'title' => $plug_title,
														             'description' => $plug_description,
														             'tags' => $plug_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '1',
														             'poster' => $this->rknclass->user['username'],
														             'poster_id' => $this->rknclass->user['user_id'],
																	 'approved' => '1',
														             'posted' => $posted)));
		$this->rknclass->db->query($query);
		$insert_id = $this->rknclass->db->insert_id();
		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($insert_id, $plug_title, $cats['cat_name']));

		header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$insert_id}");

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$insert_id}' LIMIT 1");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs+1 WHERE user_id='{$this->rknclass->user['user_id']}'");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs=total_plugs+1 WHERE cat_name='{$cats['cat_id']}' LIMIT 1");
	}

	public function add_hosted_video()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		$this->rknclass->page_title='Upload new hosted video';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Hosted video submission');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_hosted_video_submission');
		$this->rknclass->form->add_input('video_title', 'input', 'Title of the Video', 'Please enter the title of the video you are currently submitting');
		$this->rknclass->form->add_input('video_description', 'textarea', 'Description of the Video', 'Please enter a short, but detailed description of the video.');
		$this->rknclass->form->add_input('video_tags', 'input', 'Video Tags', 'Please enter a few tags for your video. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>');
		$this->rknclass->form->add_input('video_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail of your video. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('video_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('video_schedule', 'input', 'Schedule', 'If you would like to schedule your video to appear at a specific time, please enter it here', date('j M Y g:i:sa', time()));

		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box
		==========================*/

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('video_category', 'dropdown', 'Select Category', 'Please select a category for your video from the list.', $categories);

		$handle=opendir(($this->rknclass->settings['video_server'] == '0' ? RKN__fullpath . 'videos' : 'ftp://' . $this->rknclass->settings['cluster_settings']['video_server_username'] . ':' . $this->rknclass->settings['cluster_settings']['video_server_password'] . '@' . $this->rknclass->settings['cluster_settings']['video_server_address'] . '/'));

		$ffmpeg = @unserialize($this->rknclass->settings['ffmpeg_settings']);

		$videos='';
		while(($video=readdir($handle)) !== false)
		{
			if($video !== '.' AND $video !== '..' AND strpos($video, 'imported_vid_') === false)
			{
			    $types = array('flv', 'wmv');

			    if($ffmpeg['enabled'] == '1')
			    {
			        array_push($types, '3gp', '3gp2', 'avi', 'mov', 'm4v', 'mp4');
			    }

			    if(in_array(@end(explode('.', $video)), $types, true))
			    {
			        $videos.="<option value=\"$video\">$video</option>";
			    }
			}
		}
		closedir($handle);
		$this->rknclass->form->add_input('video_filename', 'dropdown', 'Select Video', 'Please select a video from the list. Predator automatically detects your uploaded .wmv and .flv video files, as well as others if using FFMPEG integration.' . ($this->rknclass->settings['video_server'] == '1' ? '<br /><br /><strong><font color="green">Video info taken from FTP server</font></strong>' : ''), $videos);

		if($this->rknclass->settings['queue_time'] > 0)
		{
		    $this->rknclass->form->add_input('queue_obey', 'dropdown', 'Add to queue?', 'Please select whether or not you want this video to be added to the content queue system<br /><br /><strong>If set to yes, the field above will have no effect!</strong>', '<option value="0">No</option><option value="1">Yes</option>');
		}

		$this->rknclass->form->add_input('sponsor_site_id', 'dropdown', 'Video Sponsor', 'Please select a sponsor to use with this video. <br /><br />This determines what banners / advertisements to display when viewers visit this video page', $this->get_sponsor_dropdown_list());

		$ffmpeg = @unserialize($this->rknclass->settings['ffmpeg_settings']);

		if($ffmpeg['enabled'] == '1')
		{
		    $this->rknclass->form->add_input('ffmpeg_convert', 'dropdown', 'Convert using FFMPEG', 'If set to Yes, your video will be converted using FFMPEG. If set to No, your video will not be converted and none of the options below will have any effect.', '<option value="0">No</option><option value="1" SELECTED>Yes</option>');
		    $this->rknclass->form->add_input('ffmpeg_width', 'input', 'Output video width', 'Enter the desired width of your converted video, <strong>in pixels</strong><br /><br />Leave blank to not change dimensions.');
		    $this->rknclass->form->add_input('ffmpeg_height', 'input', 'Output video height', 'Enter the desired height of your converted video, <strong>in pixels</strong><br /><br />Leave blank to not change dimensions.');
		    $this->rknclass->form->add_input('ffmpeg_thumb', 'dropdown', 'Grab thumbnail from video', 'If set to yes, a thumbnail will be ripped from the video. Otherwise you will have to upload/crop and image using the original fields above.', '<option value="0">No</option><option value="1" SELECTED>Yes</option>');
		}

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_hosted_video_submission()
	{
		$check=array('video_filename', 'video_title', 'video_description', 'video_tags', 'video_category', 'video_schedule');

		$ffmpeg = @unserialize($this->rknclass->settings['ffmpeg_settings']);

		if($ffmpeg['enabled'] == '1')
		{
		    array_push($check, 'ffmpeg_convert', 'ffmpeg_thumb');
		}

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		if($this->rknclass->settings['video_server'] == '0')
		{
			if(!file_exists(RKN__fullpath . 'videos/' . $this->rknclass->post['video_filename']))
			{
				exit($this->rknclass->global_tpl->admin_error('The specified video was not found!'));
			}
		}

		if(!is_numeric($this->rknclass->post['sponsor_site_id']))
		{
			$sponsor_site_id = '0';
		}
		else
		{
			$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' LIMIT 1");

			if($this->rknclass->db->result() < 1)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor site selected!'));
			}
			else
			{
				$sponsor_site_id= $this->rknclass->post['sponsor_site_id'];
			}
		}

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['video_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

		if($ffmpeg['enabled'] !== '1' || $this->rknclass->post['ffmpeg_convert'] !== '1' || $this->rknclass->post['ffmpeg_thumb'] == '0')
		{
			$remote = false;

    		if(isset($this->rknclass->post['video_image_remote']) AND !empty($this->rknclass->post['video_image_remote']))
    		{
    			$remote = true;
    		}

    		if($remote === false)
    		{
    			if(!is_uploaded_file($_FILES['video_image']['tmp_name']))
    			{
    				exit($this->rknclass->global_tpl->admin_error('You didn\'t upload an image!'));
    			}
    		}
    		else
    		{
    			if(substr($this->rknclass->post['video_image_remote'], 0, 7) !== 'http://')
    			{
    				exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
    			}
    			$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
    			@copy($this->rknclass->post['video_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
    		}


    		($remote === false ? $info=@getimagesize($_FILES['video_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

    		$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

    		if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
    		{
    			exit($this->rknclass->global_tpl->admin_error('Invalid Image Type'));
    		}

    		if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
    		{
    			exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
    		{
    			@move_uploaded_file($_FILES['video_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
    		}
    		else
    		{
    			@rename(RKN__fullpath . 'tmp/' . $rname, RKN__fullpath . 'tmp/' . $name);
    		}
		}

		$video_tags=$this->rknclass->utils->process_tags($video_tags); //Makes sure the tags are formatted correctly

		if(isset($this->rknclass->post['queue_obey']) AND $this->rknclass->post['queue_obey'] == '1')
		{
		    $posted = $this->rknclass->utils->get_next_queue_ts();
		}
		else
		{
		    $posted = strtotime($this->rknclass->post['video_schedule']);
		    if($posted === false)
		    {
		        exit($this->rknclass->form->admin_error('Invalid data entered for video schedule field!'));
		    }
		}

		if($ffmpeg['enabled'] == '1' AND $this->rknclass->post['ffmpeg_convert'] == '1')
		{
		    $approved = 0;
		}
		else
		{
		    $approved = 1;
		}

		$query=$this->rknclass->db->build_query(array('insert' => 'plugs',
		                                              'set' => array('title' => $video_title,
														             'description' => $video_description,
														             'tags' => $video_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '2',
														             'poster' => $this->rknclass->user['username'],
														             'poster_id' => $this->rknclass->user['user_id'],
																	 'approved' => $approved,
														             'posted' => $posted)));
		$this->rknclass->db->query($query);
		$insert_id = $this->rknclass->db->insert_id();
		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($insert_id, $video_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$insert_id}' LIMIT 1");


		if($ffmpeg['enabled'] !== '1' || $this->rknclass->post['ffmpeg_convert'] == '0')
		{
    		if(strpos($this->rknclass->post['video_filename'], '.flv') !== false)
    		{
    			$player='flv';
    		}

    		elseif(strpos($this->rknclass->post['video_filename'], '.wmv') !== false)
    		{
    			$player='wmv';
    		}

    		$new='imported_vid_' . $this->rknclass->utils->rand_chars(7) . ".$player";

    		if($this->rknclass->settings['video_server'] == '0')
    		{
    			rename(RKN__fullpath . 'videos/' . $this->rknclass->post['video_filename'], RKN__fullpath . 'videos/' . $new);
    		}
    		else
    		{
    			@$handle = ftp_connect($this->rknclass->settings['cluster_settings']['video_server_address']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to video server'));
    			@ftp_login($handle, $this->rknclass->settings['cluster_settings']['video_server_username'], $this->rknclass->settings['cluster_settings']['video_server_password']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to video server<br />Invalid user / pass'));
    			@ftp_rename($handle, $this->rknclass->post['video_filename'], $new) or exit($this->rknclass->global_tpl->admin_error('Unable to rename video in FTP directory'));
    			@ftp_close($handle);
    		}

    		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "videos SET plug_id='" . $insert_id . "', file_name='" . $new . "', player='" . $player . "', sponsor_site_id='{$sponsor_site_id}'");
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs+1 WHERE user_id='{$this->rknclass->user['user_id']}'");

		if($ffmpeg['enabled'] !== '1' || $this->rknclass->post['ffmpeg_convert'] !== '1' || $this->rknclass->post['ffmpeg_thumb'] == '0')
		{
		    header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$insert_id}");
		}

		if($ffmpeg['enabled'] == '1' AND $this->rknclass->post['ffmpeg_convert'] == '1')
		{
		    if(empty($this->rknclass->post['ffmpeg_width']) || empty($this->rknclass->post['ffmpeg_height']) || $this->rknclass->post['ffmpeg_width'] < 100 || $this->rknclass->post['ffmpeg_height'] < 100)
		    {
		        $dimensions = 'NULL';
		    }
		    else
		    {
		        $dimensions = '\'' . serialize(array('width' => $this->rknclass->post['ffmpeg_width'], 'height' => $this->rknclass->post['ffmpeg_height'])) . '\'';
		    }

		    $this->rknclass->db->query("INSERT INTO " . TBLPRE . "ffmpeg_queue SET plug_id='{$insert_id}', dimensions={$dimensions}, filename='{$this->rknclass->post['video_filename']}', thumb='{$this->rknclass->post['ffmpeg_thumb']}'");

		    if($this->rknclass->post['ffmpeg_thumb'] == '1')
		    {
		        $this->rknclass->global_tpl->exec_redirect('Successfully added video to queue', '?ctr=content&act=conversion_queue');
		    }
		}
	}

	public function add_embedded_video()
	{

		$this->rknclass->page_title='Add a new embedded video';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Embedded video submission');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_embedded_video_submission');
		$this->rknclass->form->add_input('video_title', 'input', 'Title of the Video', 'Please enter the title of the video you are currently submitting');
		$this->rknclass->form->add_input('video_description', 'textarea', 'Description of the Video', 'Please enter a short, but detailed description of the video.');
		$this->rknclass->form->add_input('video_tags', 'input', 'Video Tags', 'Please enter a few tags for your video. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>');
		$this->rknclass->form->add_input('video_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail of your video. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('video_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');


		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box
		==========================*/

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('video_category', 'dropdown', 'Select Category', 'Please select a category for your video from the list.', $categories);
		$this->rknclass->form->add_input('video_embed_code', 'textarea', 'Video HTML/Embed Code', 'Please enter the videos html/embed code which will be displayed on the video page.');
		$this->rknclass->form->add_input('video_schedule', 'input', 'Schedule', 'If you would like to schedule your video to appear at a specific time, please enter it here', date('j M Y g:i:sa', time()));

	    if($this->rknclass->settings['queue_time'] > 0)
		{
		    $this->rknclass->form->add_input('queue_obey', 'dropdown', 'Add to queue?', 'Please select whether or not you want this video to be added to the content queue system<br /><br /><strong>If set to yes, the field above will have no effect!</strong>', '<option value="0">No</option><option value="1">Yes</option>');
		}

		$this->rknclass->form->add_input('sponsor_site_id', 'dropdown', 'Video Sponsor', 'Please select a sponsor to use with this video. <br /><br />This determines what banners / advertisements to display when viewers visit this video page', $this->get_sponsor_dropdown_list());
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_embedded_video_submission()
	{
		$check=array('video_embed_code', 'video_title', 'video_description', 'video_tags', 'video_category', 'video_schedule');

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		if(!is_numeric($this->rknclass->post['sponsor_site_id']))
		{
			$sponsor_site_id = '0';
		}
		else
		{
			$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' LIMIT 1");

			if($this->rknclass->db->result() < 1)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor site selected!'));
			}
			else
			{
				$sponsor_site_id= $this->rknclass->post['sponsor_site_id'];
			}
		}

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['video_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

		$remote = false;

		if(isset($this->rknclass->post['video_image_remote']) AND !empty($this->rknclass->post['video_image_remote']))
		{
			$remote = true;
		}

		if($remote === false)
		{
			if(!is_uploaded_file($_FILES['video_image']['tmp_name']))
			{
				exit($this->rknclass->global_tpl->admin_error('You didn\'t upload an image!'));
			}
		}
		else
		{
			if(substr($this->rknclass->post['video_image_remote'], 0, 7) !== 'http://')
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
			}
			$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
			@copy($this->rknclass->post['video_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
		}


		($remote === false ? $info=@getimagesize($_FILES['video_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

		$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

		if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid Image Type'));
		}

		if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
		{
			exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
		{
			@move_uploaded_file($_FILES['video_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
		}
		else
		{
			@rename(RKN__fullpath . 'tmp/' . $rname, RKN__fullpath . 'tmp/' . $name);
		}

		$video_tags=$this->rknclass->utils->process_tags($video_tags); //Makes sure the tags are formatted correctly

	    if(isset($this->rknclass->post['queue_obey']) AND $this->rknclass->post['queue_obey'] == '1')
		{
		    $posted = $this->rknclass->utils->get_next_queue_ts();
		}
		else
		{
		    $posted = strtotime($this->rknclass->post['video_schedule']);
		    if($posted === false)
		    {
		        exit($this->rknclass->form->admin_error('Invalid data entered for video schedule field!'));
		    }
		}

		$query=$this->rknclass->db->build_query(array('insert' => 'plugs',
		                                              'set' => array('title' => $video_title,
														             'description' => $video_description,
														             'tags' => $video_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '3',
														             'poster' => $this->rknclass->user['username'],
														             'poster_id' => $this->rknclass->user['user_id'],
																	 'approved' => '1',
														             'posted' => $posted)));
		$this->rknclass->db->query($query);
		$insert_id=$this->rknclass->db->insert_id();
		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($insert_id, $video_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$insert_id}' LIMIT 1");

		$this->rknclass->post['video_embed_code']=$this->rknclass->db->escape($_POST['video_embed_code']);

		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "videos SET plug_id='" . $insert_id . "', html_code='" . $this->rknclass->post['video_embed_code'] . "', sponsor_site_id='$sponsor_site_id'");
		header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$insert_id}");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs+1 WHERE user_id='{$this->rknclass->user['user_id']}'");
	}

	public function view_plugs()
	{
		$this->rknclass->page_title='View submitted plugs';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type='1' AND poster_id='{$this->rknclass->get['user_id']}'");
		}
		else
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type='1'");
		}

		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">All Plugs Submitted</div>
 <table id="listings" cellpadding="0" cellspacing="0">
  <tr id="columns">
    <th scope="col">Thumb</th>
    <th scope="col" id="title">' . $this->order_by('title', 'Title') . '</th>
    <th scope="col">' . $this->order_by('poster', 'Poster') . '</th>
    <th scope="col">' . $this->order_by('views', 'Hits') . '</th>
    <th scope="col">' . $this->order_by('category', 'Category') . '</th>
    <th scope="col">' . $this->order_by('posted', 'Date') . '</th>
    <th scope="col">' . $this->order_by('framed', 'Frame') . '</th>
    <th scope="col">' . $this->order_by('chosen', 'Chsn') . '</th>
    <th scope="col">' . $this->order_by('approved', 'Apr') . '</th>
    <th scope="col">Edit</th>
    <th scope="col">Del</th>
  </tr>';

		$order     = 'posted DESC';
		$order_url = '';
		$this->fetch_order($order, $order_url);

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type='1' AND poster_id='{$this->rknclass->get['user_id']}' ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		else
		{
  			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type='1' ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		while($row=$this->rknclass->db->fetch_array())
		{
			$thumbnail_src = $this->rknclass->settings['site_url'] . '/' . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb'];

			if($this->rknclass->settings['thumb_server'] == '1')
			{
			    $thumbnail_src = $this->rknclass->settings['cluster_settings']['thumb_server_http'] . '/' . $row['thumb'];
			}

			echo "<tr id=\"rows\">
	<td><img src=\"{$thumbnail_src}\" width=\"40\" height=\"40\" onmouseover=\"Tip('<img src=\'{$thumbnail_src}\' />', DELAY, 0)\"  onmouseout=\"UnTip()\"/></td>
    <td id=\"title\"><a href=\"{$this->rknclass->settings['site_url']}/index.php?ctr=view&amp;id={$row['plug_id']}\" target=\"_blank\">" . (strlen($row['title']) >= 50 ? substr($row['title'], 0, 46) . "..." : $row['title']) . "</a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=content&amp;act=view_plugs&amp;user_id=$row[poster_id]\">$row[poster]</a></td>
    <td id=\"views-{$row['plug_id']}\" ondblclick=\"edit_views('{$row['plug_id']}', '$row[views]');\">$row[views]</td>
    <td id=\"cat-{$row['plug_id']}\" ondblclick=\"edit_cat('{$row['plug_id']}');\">$row[category]</td>
    <td>" . ($row['posted'] > time() ? "<font color=\"#FF3300\">" : "") . $this->rknclass->utils->timetostr($row[posted]) . ($row['posted'] < time() ? "</font>" : "") . "</td>
    <td id=\"frame-{$row['plug_id']}\"><strong>" . ($row['framed'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('frame', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('frame', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td id=\"chosen-{$row['plug_id']}\"><strong>" . ($row['chosen'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('chosen', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('chosen', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td id=\"approved-{$row['plug_id']}\"><strong>" . ($row['approved'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('approved', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('approved', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td><a href=\"index.php?ctr=content&amp;act=update_plug&amp;id={$row['plug_id']}&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_content&amp;id={$row['plug_id']}&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\" onclick=\"return confirm('Are you sure you want to permanently delete this plug?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_plugs&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_plugs&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		else
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_plugs&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_plugs&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_plug()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid plug id'));
		}

		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This plug does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($row['type'] !== '1')
		{
			exit($this->rknclass->global_tpl->admin_error('The content is not a plug'));
		}

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$this->rknclass->page_title='Update plug';
		$this->rknclass->global_tpl->admin_header();
		if($_GET['popup']) {
			echo '<link rel="stylesheet" href="popup.css">';
		}
		$this->rknclass->form->new_form('Update plug');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_plug_submission&amp;id=' . $row[plug_id] . ($_GET['popup'] ? '&amp;popup=true' : ''));
		$this->rknclass->form->add_input('plug_url', 'input', 'Url of the Plug', 'Enter the full url to the plug here! This must start with <strong>http://</strong>. No homepage links!', $row[url]);
		$this->rknclass->form->add_input('plug_title', 'input', 'Title of the Plug', 'Please enter the title of the plug you are currently submitting. This must be relevant to the actual content contained on the page', $row[title]);
		$this->rknclass->form->add_input('plug_description', 'textarea', 'Description of the Plug', 'Please enter a short, but detailed description of the plug. The better the description, the greater likelihood that you\'ll receive more traffic!', $row[description]);
		$this->rknclass->form->add_input('plug_tags', 'input', 'Plug Tags', 'Please enter a few tags for your plug. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>', $row[tags]);
		$this->rknclass->form->add_input('plug_image', 'image', 'Upload and Crop Image', 'If you wish to change the plugs thumbnail, upload a new image</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('plug_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');

		$this->rknclass->form->add_input('plug_time', 'input', 'Scheduled Time', 'Enter the time you want this plug to display at. <br /><br /><strong>Time is currently:</strong>: ' . date('j M Y g:i:sa', time()), date('j M Y g:i:sa', $row['posted']));

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row2=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"{$row2['cat_id']}\"" . ($row2['cat_id'] == $row['category_id'] ? " selected" : "") . ">{$row2['cat_name']}" . ($row2['public'] == '0' ? " (Private)" : "") . "</option>";
		}

		$this->rknclass->form->add_input('plug_category', 'dropdown', 'Select Category', 'Please select a category for your plug from the list', $categories);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	/*=============================
	The section bellow is called
	when a user updates their plug
	===============================*/

	public function update_plug_submission()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid plug id'));
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
			exit($this->rknclass->global_tpl->admin_error('This plug does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		/*============================
		Fixes bug with urls containing
		the '&' character.
		=============================*/

		if(strpos($this->rknclass->post['plug_url'], '&amp') !== false)
		{
			$this->rknclass->post['plug_url']=str_replace('&amp;', '&', $this->rknclass->post['plug_url']);
			$this->rknclass->post['plug_url']=str_replace('&amp', '&', $this->rknclass->post['plug_url']);
		}

		$check=array('plug_url', 'plug_title', 'plug_description', 'plug_tags', 'plug_category');

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['plug_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

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
		                                              'set' => array('url' => $plug_url,
										 				             'title' => $plug_title,
														             'description' => $plug_description,
														             'tags' => $plug_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '1',
																	 'cropped' => $cropped,
																	 'posted' => strtotime($this->rknclass->post['plug_time'])),
													  'where' => array('plug_id' => $row['plug_id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($row['plug_id'], $plug_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$row['plug_id']}' LIMIT 1");

		if($name !== $row[thumb])
		{
			header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$this->rknclass->get['id']}");
		}
		else
		{
			if($_GET['popup']) {
				echo '<script>window.opener.location.reload(); window.close();</script>';
				exit;
			} else {
				$this->rknclass->global_tpl->exec_redirect('Plug successfully updated!', '?ctr=content');
			}
		}
	}

	public function delete_content()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] == false)
		{
			$this->rknclass->global_tpl->admin_error('Content not found');
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		if($this->rknclass->user['group']['is_restricted'] == '1')
		{
		    switch($row['type'])
		    {
		        case '1':
		            if($this->rknclass->user['restrictions']['edit_plugs'] == '0')
		            {
		                exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		            }
		            break;
		        case '2':
		            if($this->rknclass->user['restrictions']['edit_videos'] == '0' || $this->rknclass->user['restrictions']['add_hvideo'] == '0')
		            {
		                exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		            }
		            break;
		        case '3':
		            if($this->rknclass->user['restrictions']['edit_videos'] == '0' || $this->rknclass->user['restrictions']['add_evideo'] == '0')
		            {
		                exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		            }
		            break;
		        case '5':
		    		if($this->rknclass->user['restrictions']['edit_plugs'] == '0')
		            {
		                exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		            }
		            break;
		    }
		}

		if($row['type'] == '1' || $row['type'] == '2' || $row['type'] == '3' || $row['type'] == '5')
		{
			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "plugs WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		}

		if($row['type'] == '2')
		{
			$this->rknclass->db->query("SELECT file_name FROM " . TBLPRE . "videos WHERE plug_id='" . $this->rknclass->get['id'] . "'");
			$row['file_name']=$this->rknclass->db->result();


			if($this->rknclass->settings['video_server'] == '0')
			{
				@unlink(RKN__fullpath . 'videos/' . $row['file_name']);
			}
			else
			{
				@$handle = ftp_connect($this->rknclass->settings['cluster_settings']['video_server_address']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to video server'));
				@ftp_login($handle, $this->rknclass->settings['cluster_settings']['video_server_username'], $this->rknclass->settings['cluster_settings']['video_server_password']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to video server<br />Invalid user / pass'));
				@ftp_delete($handle, $row['file_name']);
				@ftp_close($handle);
			}

			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "ffmpeg_queue WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		}

		if($row['type'] == '2' || $row['type'] == '3')
		{
			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "videos WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
			$this->rknclass->db->query("OPTIMIZE TABLE " . TBLPRE . "videos");
		}

		if($row['type'] == '5')
		{
			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "blog_articles WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
			$this->rknclass->db->query("OPTIMIZE TABLE " . TBLPRE . "blog_articles");
		}

		if($this->rknclass->settings['thumb_server'] == '0')
		{
			@unlink(RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' .  $row['thumb']);
		}
		else
		{
				@$handle = ftp_connect($this->rknclass->settings['cluster_settings']['thumb_server_address']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to thumb server'));
				@ftp_login($handle, $this->rknclass->settings['cluster_settings']['thumb_server_username'], $this->rknclass->settings['cluster_settings']['thumb_server_password']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to thumb server<br />Invalid user / pass'));
				@ftp_delete($handle, $row['thumb']);
				@ftp_close($handle);
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs-1 WHERE user_id='" . $row['poster_id'] . "' LIMIT 1");

		if($this->rknclass->get['return_url'] !== '')
		{
			$return_to=$this->rknclass->get['return_url'];
			if(strpos($return_to, '[and]') !== false)
			{
				$return_to=str_replace('[and]', '&', $return_to);
			}
		}
		else
		{
			$return_to='?ctr=content';
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs=total_plugs-1 WHERE cat_id='{$row['category_id']}' LIMIT 1");
		$this->rknclass->db->query("OPTIMIZE TABLE " . TBLPRE . "plugs");
		$this->rknclass->global_tpl->exec_redirect('Content successfully deleted!', $return_to);
	}

	public function plugs_pending_approval()
	{
		$this->rknclass->page_title='Plugs Pending Approval';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP

		if(!empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type='1' AND approved='0' AND poster_id='{$this->rknclass->get['user_id']}'");
		}
		else
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type='1' AND approved='0'");
		}

		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Plugs Pending Approval</div>
 <table id="listings" cellpadding="0" cellspacing="0">
  <tr id="columns">
    <th scope="col">Thumb</th>
    <th scope="col" id="title">' . $this->order_by('title', 'Title') . '</th>
    <th scope="col">' . $this->order_by('poster', 'Poster') . '</th>
    <th scope="col">' . $this->order_by('category', 'Category') . '</th>
    <th scope="col">' . $this->order_by('posted', 'Date') . '</th>
    <th scope="col">' . $this->order_by('approved', 'Apr') . '</th>
    <th scope="col">' . $this->order_by('framed', 'Frame') . '</th>
    <th scope="col">' . $this->order_by('chosen', 'Chsn') . '</th>
    <th scope="col">' . ucfirst($this->rknclass->settings['trade_type']) . '</th>
    <th scope="col">Edit</th>
    <th scope="col">Del</th>
  </tr>';

		$order     = 'posted DESC';
		$order_url = '';
		$this->fetch_order($order, $order_url);

		if(!empty($this->rknclass->get['user_id']))
		{
			$result = $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type='1' AND approved='0' AND poster_id='{$this->rknclass->get['user_id']}' ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		else
		{
  			$result = $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type='1' AND approved='0' ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		while($row=$this->rknclass->db->fetch_array($result))
		{
		    $trade_query = $this->rknclass->db->query("SELECT u_total_in,u_total_out FROM " . TBLPRE . "sites WHERE url='" . $this->rknclass->utils->rkn_url_parser($row['url']) . "' LIMIT 1");

			$row2 = $this->rknclass->db->fetch_array($trade_query);

			$ratio=$this->rknclass->utils->get_trade_by_in_out($row2['u_total_in'], $row2['u_total_out']);

			if($this->rknclass->utils->trade_check($row2['u_total_in'], $row2['u_total_out']) === false)
			{
				$ratio="<font color=\"#e32c00\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
			}
			else
			{
				$ratio="<font color=\"#136f01\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
			}

			$thumbnail_src = $this->rknclass->settings['site_url'] . '/' . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb'];

			if($this->rknclass->settings['thumb_server'] == '1')
			{
			    $thumbnail_src = $this->rknclass->cluster_settings['thumb_server_http'] . '/' . $row['thumb'];
			}

			echo "<tr id=\"rows\">
	<td><img src=\"{$thumbnail_src}\" width=\"40\" height=\"40\" onmouseover=\"Tip('<img src=\'{$thumbnail_src}\' />', DELAY, 0)\"  onmouseout=\"UnTip()\"/></td>
    <td id=\"title\"><a href=\"{$this->rknclass->settings['site_url']}/index.php?ctr=view&amp;id={$row['plug_id']}\" target=\"_blank\">" . (strlen($row[title]) >= 50 ? substr($row[title], 0, 46) . "..." : $row[title]) . "</a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=content&amp;act=plugs_pending_approval&amp;user_id=$row[poster_id]\">$row[poster]</a></td>
    <td id=\"cat-{$row['plug_id']}\" ondblclick=\"edit_cat('{$row['plug_id']}');\">$row[category]</td>
    <td>" . ($row['posted'] > time() ? "<font color=\"#FF3300\">" : "") . $this->rknclass->utils->timetostr($row[posted]) . ($row['posted'] < time() ? "</font>" : "") . "</td>
    <td id=\"approved-{$row['plug_id']}\"><strong>" . ($row['approved'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('approved', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('approved', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td id=\"frame-{$row['plug_id']}\"><strong>" . ($row['framed'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('frame', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('frame', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td id=\"chosen-{$row['plug_id']}\"><strong>" . ($row['chosen'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('chosen', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('chosen', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td><strong>{$ratio}</strong></td>
    <td><a href=\"index.php?ctr=content&amp;act=update_plug&amp;id={$row['plug_id']}&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_content&amp;id={$row['plug_id']}&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\" onclick=\"return confirm('Are you sure you want to permanently delete this plug?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		else
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function view_videos()
	{
		$this->rknclass->page_title='View videos';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type IN('2', '3') AND poster_id='{$this->rknclass->get['user_id']}'");
		}
		else
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type IN('2', '3')");
		}

		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">All Videos</div>
 <table id="listings" cellpadding="0" cellspacing="0">
  <tr id="columns">
    <th scope="col">Thumb</th>
    <th scope="col" id="title">' . $this->order_by('title', 'Title') . '</th>
    <th scope="col">' . $this->order_by('poster', 'Poster') . '</th>
    <th scope="col">' . $this->order_by('views', 'Hits') . '</th>
    <th scope="col">' . $this->order_by('category', 'Category') . '</th>
    <th scope="col">' . $this->order_by('posted', 'Date') . '</th>
    <th scope="col">' . $this->order_by('framed', 'Frame') . '</th>
    <th scope="col">' . $this->order_by('chosen', 'Chsn') . '</th>
    <th scope="col">' . $this->order_by('approved', 'Apr') . '</th>
    <th scope="col">Edit</th>
    <th scope="col">Del</th>
  </tr>';

		$order     = 'posted DESC';
		$order_url = '';
		$this->fetch_order($order, $order_url);

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type IN('2', '3') AND poster_id='{$this->rknclass->get['user_id']}' ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		else
		{
  			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type IN('2', '3') ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		while($row=$this->rknclass->db->fetch_array())
		{
			$thumbnail_src = $this->rknclass->settings['site_url'] . '/' . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb'];

			if($this->rknclass->settings['thumb_server'] == '1')
			{
			    $thumbnail_src = $this->rknclass->settings['cluster_settings']['thumb_server_http'] . '/' . $row['thumb'];
			}

			switch($row['type'])
			{
			    case '2':
			        $type = 'hosted';
			        break;
			    case '3':
			        $type = 'embedded';
			        break;
			    default:
			        $type = 'hosted';
			        break;
			}
			echo "<tr id=\"rows\">
	<td><img src=\"{$thumbnail_src}\" width=\"40\" height=\"40\" onmouseover=\"Tip('<img src=\'{$thumbnail_src}\' />', DELAY, 0)\"  onmouseout=\"UnTip()\"/></td>
    <td id=\"title\"><a href=\"{$this->rknclass->settings['site_url']}/index.php?ctr=view&amp;id={$row['plug_id']}\" target=\"_blank\">" . (strlen($row['title']) >= 50 ? substr($row['title'], 0, 46) . "..." : $row['title']) . "</a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=content&amp;act=view_videos&amp;user_id=$row[poster_id]\">$row[poster]</a></td>
    <td id=\"views-{$row['plug_id']}\" ondblclick=\"edit_views('{$row['plug_id']}', '$row[views]');\">$row[views]</td>
    <td id=\"cat-{$row['plug_id']}\" ondblclick=\"edit_cat('{$row['plug_id']}');\">$row[category]</td>
    <td>" . ($row['posted'] > time() ? "<font color=\"#FF3300\">" : "") . $this->rknclass->utils->timetostr($row[posted]) . ($row['posted'] < time() ? "</font>" : "") . "</td>
    <td id=\"frame-{$row['plug_id']}\"><strong>" . ($row['framed'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('frame', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('frame', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td id=\"chosen-{$row['plug_id']}\"><strong>" . ($row['chosen'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('chosen', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('chosen', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td id=\"approved-{$row['plug_id']}\"><strong>" . ($row['approved'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('approved', '{$row['plug_id']}', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('approved', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td><a href=\"index.php?ctr=content&amp;act=update_{$type}_video&amp;id={$row['plug_id']}&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_content&amp;id={$row['plug_id']}&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\" onclick=\"return confirm('Are you sure you want to permanently delete this video?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_videos&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_videos&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		else
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_videos&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=content&amp;act=view_videos&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}
	public function update_hosted_video()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if($this->rknclass->get['id'] == false || $this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid file'));
		}

		$this->rknclass->db->query("SELECT " . TBLPRE . "plugs.*, " . TBLPRE . "videos.file_name, " . TBLPRE . "videos.sponsor_site_id FROM " . TBLPRE . "plugs LEFT JOIN " . TBLPRE . "videos ON " . TBLPRE . "plugs.plug_id = " . TBLPRE . "videos.plug_id WHERE " . TBLPRE . "plugs.plug_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Not found in database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$this->rknclass->page_title='Update hosted video';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Update hosted video');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_hosted_video_process&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('video_title', 'input', 'Title of the Video', 'Please enter the title of the video you are currently submitting', $row['title']);
		$this->rknclass->form->add_input('video_description', 'textarea', 'Description of the Video', 'Please enter a short, but detailed description of the video.', $row['description']);
		$this->rknclass->form->add_input('video_tags', 'input', 'Video Tags', 'Please enter a few tags for your video. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>', $row['tags']);
		$this->rknclass->form->add_input('video_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail of your video. <strong>Better quality images, attract more viewers!</strong><br />Leave blank if you do not wish to change<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('video_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('video_time', 'input', 'Scheduled Time', 'Enter the time you want this plug to display at. <br /><br /><strong>Time is currently:</strong>: ' . date('j M Y g:i:sa', time()), date('j M Y g:i:sa', $row['posted']));

		/*=========================
		Build our list of publicly
		avaliable categories for
		the dropdown box
		==========================*/

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row2=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row2[cat_id]\"" . ($row['category_id'] === $row2['cat_id'] ? " selected" : "") . ">$row2[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('video_category', 'dropdown', 'Select Category', 'Please select a category for your video from the list.', $categories);

		$handle=opendir(($this->rknclass->settings['video_server'] == '0' ? RKN__fullpath . 'videos' : 'ftp://' . $this->rknclass->settings['cluster_settings']['video_server_username'] . ':' . $this->rknclass->settings['cluster_settings']['video_server_password'] . '@' . $this->rknclass->settings['cluster_settings']['video_server_address'] . '/'));

		$videos='<option value="---">---</option>';
		while(($video=readdir($handle)) !== false)
		{
			if($video !== '.' AND $video !== '..' AND strpos($video, 'imported_vid_') === false)
			{
			    $types = array('flv', 'wmv');

			    if($ffmpeg['enabled'] == '1')
			    {
			        array_push($types, '3gp', '3gp2', 'avi', 'mov', 'm4v', 'mp4');
			    }

			    if(in_array(@end(explode('.', $video)), $types, true))
			    {
			        $videos.="<option value=\"$video\">$video</option>";
			    }
			}
		}
		closedir($handle);
		$this->rknclass->form->add_input('video_filename', 'dropdown', 'Select Video', 'Please select a video from the list. Predator automatically detects your uploaded .wmv and .flv video files.<br /><br />Leave blank if you do not wish to change', $videos);
		$this->rknclass->form->add_input('sponsor_site_id', 'dropdown', 'Video Sponsor', 'Please select a sponsor to use with this video. <br /><br />This determines what banners / advertisements to display when viewers visit this video page', $this->get_sponsor_dropdown_list($row['sponsor_site_id']));
		$ffmpeg = @unserialize($this->rknclass->settings['ffmpeg_settings']);

		if($ffmpeg['enabled'] == '1')
		{
		    $this->rknclass->form->add_input('ffmpeg_convert', 'dropdown', 'Convert using FFMPEG', 'If set to Yes, your video will be converted using FFMPEG. If set to No, your video will not be converted and none of the options below will have any effect.', '<option value="0">No</option><option value="1" SELECTED>Yes</option>');
		    $this->rknclass->form->add_input('ffmpeg_width', 'input', 'Output video width', 'Enter the desired width of your converted video, <strong>in pixels</strong><br /><br />Leave blank to not change dimensions.');
		    $this->rknclass->form->add_input('ffmpeg_height', 'input', 'Output video height', 'Enter the desired height of your converted video, <strong>in pixels</strong><br /><br />Leave blank to not change dimensions.');
		    $this->rknclass->form->add_input('ffmpeg_thumb', 'dropdown', 'Grab thumbnail from video', 'If set to yes, a thumbnail will be ripped from the video. Otherwise you will have to upload/crop and image using the original fields above.', '<option value="0">No</option><option value="1" SELECTED>Yes</option>');
		}
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_embedded_video()
	{

		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid video id'));
		}

		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This plug does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$this->rknclass->db->query("SELECT html_code,sponsor_site_id FROM " . TBLPRE . "videos WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Video data not found!'));
		}

		$row2 = $this->rknclass->db->fetch_array();

		$html = $row2['html_code'];

		$sponsor_site_id = $row2['sponsor_site_id'];

		if($row['type'] !== '3')
		{
			exit($this->rknclass->global_tpl->admin_error('The content is not an embedded video!'));
		}

		$this->rknclass->page_title='Update embedded video';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Embedded video submission');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_embedded_video_update&amp;id=' . $this->rknclass->get['id'] . '');
		$this->rknclass->form->add_input('video_title', 'input', 'Title of the Video', 'Please enter the title of the video you are currently submitting', $row['title']);
		$this->rknclass->form->add_input('video_description', 'textarea', 'Description of the Video', 'Please enter a short, but detailed description of the video.', $row['description']);
		$this->rknclass->form->add_input('video_tags', 'input', 'Video Tags', 'Please enter a few tags for your video. Tags are keywords which you think are the most relevant to the submission.<br /><br /><strong>These should be one word, seperated by a single space</strong>', $row['tags']);
		$this->rknclass->form->add_input('video_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail of your video. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('video_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');

		$this->rknclass->form->add_input('video_time', 'input', 'Scheduled Time', 'Enter the time you want this plug to display at. <br /><br /><strong>Time is currently:</strong>: ' . date('j M Y g:i:sa', time()), date('j M Y g:i:sa', $row['posted']));

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row2=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row2[cat_id]\"" . ($row2[cat_id] == $row[category_id] ? " selected" : "") . ">$row2[cat_name]</option>";
		}

		$this->rknclass->form->add_input('video_category', 'dropdown', 'Select Category', 'Please select a category for your video from the list.', $categories);
		$this->rknclass->form->add_input('video_embed_code', 'textarea', 'Video HTML/Embed Code', 'Please enter the videos html/embed code which will be displayed on the video page.', $html);
		$this->rknclass->form->add_input('sponsor_site_id', 'dropdown', 'Video Sponsor', 'Please select a sponsor to use with this video. <br /><br />This determines what banners / advertisements to display when viewers visit this video page', $this->get_sponsor_dropdown_list($sponsor_site_id));
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_embedded_video_update()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid video id'));
		}

		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This plug does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$check=array('video_title', 'video_description', 'video_tags', 'video_category', 'video_embed_code');

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		if(!is_numeric($this->rknclass->post['sponsor_site_id']))
		{
			$sponsor_site_id = '0';
		}
		else
		{
			$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' LIMIT 1");

			if($this->rknclass->db->result() < 1)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor site selected!'));
			}
			else
			{
				$sponsor_site_id = $this->rknclass->post['sponsor_site_id'];
			}
		}

		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['video_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

		if(is_uploaded_file($_FILES['video_image']['tmp_name']) || !empty($this->rknclass->post['video_image_remote']))
		{

			/*=================================
			If a new thumb has been uploaded,
			this section will check and process
			it, as well as removing the previous
			==================================*/

			$remote = false;

			if(isset($this->rknclass->post['video_image_remote']) AND !empty($this->rknclass->post['video_image_remote']))
			{
				$remote = true;
			}

			if($remote === true)
			{
				if(substr($this->rknclass->post['video_image_remote'], 0, 7) !== 'http://')
				{
					exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
				}
				$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
				@copy($this->rknclass->post['video_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
			}


			($remote === false ? $info=@getimagesize($_FILES['video_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

			$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

			if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid Image Type'));
			}

			if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
			{
				exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
				@move_uploaded_file($_FILES['video_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
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
		$video_tags=$this->rknclass->utils->process_tags($video_tags); //Makes sure the tags are formatted correctly

		$query=$this->rknclass->db->build_query(array('update' => 'plugs',
		                                              'set' => array('title' => $video_title,
														             'description' => $video_description,
														             'tags' => $video_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '3',
																	 'posted' => strtotime($this->rknclass->post['video_time']),
																	 'cropped' => $cropped),
													  'where' => array('plug_id' => $row['plug_id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($row['plug_id'], $video_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$row['plug_id']}' LIMIT 1");

		$this->rknclass->post['video_embed_code']=$this->rknclass->db->escape($_POST['video_embed_code']);

		$this->rknclass->db->query("UPDATE " . TBLPRE . "videos SET html_code='" . $this->rknclass->post['video_embed_code'] . "', sponsor_site_id='$sponsor_site_id' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($name !== $row[thumb])
		{
			header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$this->rknclass->get['id']}");
		}
		else
		{
			$this->rknclass->global_tpl->exec_redirect('Video successfully updated!', '?ctr=content');
		}
	}

	public function update_hosted_video_process()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid video id'));
		}

		$ffmpeg = @unserialize($this->rknclass->settings['ffmpeg_settings']);

		if($ffmpeg['enabled'] == '1')
		{
		    array_push($check, 'ffmpeg_convert', 'ffmpeg_thumb');
		}

		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'plugs',
													  'where' => array('plug_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This hosted video does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$check=array('video_title', 'video_description', 'video_tags', 'video_category', 'video_filename');

		if(!ctype_digit((string)$this->rknclass->post['sponsor_site_id']))
		{
			$sponsor_site_id = '0';
		}
		else
		{
			$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' LIMIT 1");

			if($this->rknclass->db->result() < 1)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor site selected!'));
			}
			else
			{
				$sponsor_site_id= $this->rknclass->post['sponsor_site_id'];
			}
		}

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['video_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

		if(is_uploaded_file($_FILES['video_image']['tmp_name']) || !empty($this->rknclass->post['video_image_remote']))
		{

			/*=================================
			If a new thumb has been uploaded,
			this section will check and process
			it, as well as removing the previous
			==================================*/

			$remote = false;

			if(isset($this->rknclass->post['video_image_remote']) AND !empty($this->rknclass->post['video_image_remote']))
			{
				$remote = true;
			}

			if($remote === true)
			{
				if(substr($this->rknclass->post['video_image_remote'], 0, 7) !== 'http://')
				{
					exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
				}
				$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
				@copy($this->rknclass->post['video_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
			}


			($remote === false ? $info=@getimagesize($_FILES['video_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

			$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

			if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid Image Type'));
			}

			if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
			{
				exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
				@move_uploaded_file($_FILES['video_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
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

		if($ffmpeg['enabled'] == '1' AND $this->rknclass->post['ffmpeg_convert'] == '1' AND $this->rknclass->post['video_filename'] != '---')
		{
		    $approved = 0;
		}
		else
		{
		    $approved = 1;
		}

		$video_tags=$this->rknclass->utils->process_tags($video_tags); //Makes sure the tags are formatted correctly

		$query=$this->rknclass->db->build_query(array('update' => 'plugs',
		                                              'set' => array('title' => $video_title,
														             'description' => $video_description,
														             'tags' => $video_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '2',
																	 'posted' => strtotime($this->rknclass->post['video_time']),
																	 'approved' => $approved,
																	 'cropped' => $cropped),
													  'where' => array('plug_id' => $row['plug_id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($row['plug_id'], $video_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$row['plug_id']}' LIMIT 1");

		if($this->rknclass->post['video_filename'] != '---')
		{
		    if($ffmpeg['enabled'] !== '1' || $this->rknclass->post['ffmpeg_convert'] == '0')
		    {
    			if(strpos($this->rknclass->post['video_filename'], '.flv') !== false)
    			{
    				$player='flv';
    			}

    			elseif(strpos($this->rknclass->post['video_filename'], '.wmv') !== false)
    			{
    				$player='wmv';
    			}

    			$new='imported_vid_' . $this->rknclass->utils->rand_chars(7) . ".$player";

    			if($this->rknclass->settings['video_server'] == '0')
    			{
    				rename(RKN__fullpath . 'videos/' . $this->rknclass->post['video_filename'], RKN__fullpath . 'videos/' . $new);
    			}
    			else
    			{
    				@$handle = ftp_connect($this->rknclass->settings['cluster_settings']['video_server_address']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to video server'));
    				@ftp_login($handle, $this->rknclass->settings['cluster_settings']['video_server_username'], $this->rknclass->settings['cluster_settings']['video_server_password']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to video server<br />Invalid user / pass'));
    				@ftp_rename($handle, $this->rknclass->post['video_filename'], $new) or exit($this->rknclass->global_tpl->admin_error('Unable to rename video in FTP directory'));
    				@ftp_close($handle);
    			}

    			$this->rknclass->db->query("UPDATE " . TBLPRE . "videos SET file_name='" . $new . "', player='" . $player . "' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		    }
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "videos SET sponsor_site_id='$sponsor_site_id' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($name !== $row['thumb'])
		{
			header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$this->rknclass->get['id']}");
		}
		else
		{
			$this->rknclass->global_tpl->exec_redirect('Video successfully updated!', '?ctr=content');
		}

		if($ffmpeg['enabled'] == '1' AND $this->rknclass->post['ffmpeg_convert'] == '1' AND $this->rknclass->post['video_filename'] != '---')
		{
		    if(empty($this->rknclass->post['ffmpeg_width']) || empty($this->rknclass->post['ffmpeg_height']) || $this->rknclass->post['ffmpeg_width'] < 100 || $this->rknclass->post['ffmpeg_height'] < 100)
		    {
		        $dimensions = 'NULL';
		    }
		    else
		    {
		        $dimensions = '\'' . serialize(array('width' => $this->rknclass->post['ffmpeg_width'], 'height' => $this->rknclass->post['ffmpeg_height'])) . '\'';
		    }

		    $this->rknclass->db->query("DELETE FROM " . TBLPRE . "ffmpeg_queue WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		    $this->rknclass->db->query("INSERT INTO " . TBLPRE . "ffmpeg_queue SET plug_id='{$this->rknclass->get['id']}', dimensions={$dimensions}, filename='{$this->rknclass->post['video_filename']}', thumb='{$this->rknclass->post['ffmpeg_thumb']}'");
		}
	}
	/*=============================
	The functions below are used
	for updating the AJAX parts
	of the plugs listings pages
	==============================*/
	public function update_frame()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content id'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET framed='" . ($this->rknclass->get['type'] == 'yes' ? '1' : '0') . "' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($this->rknclass->get['type'] == 'yes')
		{
			echo "<a href=\"#\" onclick=\"ajax_update('frame', '{$this->rknclass->get['id']}', 'no'); return false;\"><font color=\"#136f01\">Yes</font></a>";
		}
		else
		{
			echo "<a href=\"#\" onclick=\"ajax_update('frame', '{$this->rknclass->get['id']}', 'yes'); return false;\"><font color=\"#e32c00\">No</font></a>";
		}
	}

	public function update_chosen()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content id'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET chosen='" . ($this->rknclass->get['type'] == 'yes' ? '1' : '0') . "' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($this->rknclass->get['type'] == 'yes')
		{
			echo "<a href=\"#\" onclick=\"ajax_update('chosen', '{$this->rknclass->get['id']}', 'no'); return false;\"><font color=\"#136f01\">Yes</font></a>";
		}
		else
		{
			echo "<a href=\"#\" onclick=\"ajax_update('chosen', '{$this->rknclass->get['id']}', 'yes'); return false;\"><font color=\"#e32c00\">No</font></a>";
		}
	}

	public function update_approved()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content id'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET approved='" . ($this->rknclass->get['type'] == 'yes' ? '1' : '0') . "' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($this->rknclass->get['type'] == 'yes')
		{
			echo "<a href=\"#\" onclick=\"ajax_update('approved', '{$this->rknclass->get['id']}', 'no'); return false;\"><font color=\"#136f01\">Yes</font></a>";
		}
		else
		{
			echo "<a href=\"#\" onclick=\"ajax_update('approved', '{$this->rknclass->get['id']}', 'yes'); return false;\"><font color=\"#e32c00\">No</font></a>";
		}
	}

	public function update_views()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content id'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET views='" . $this->rknclass->get['view_count'] . "' WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		echo $this->rknclass->get['view_count'];
	}

	public function update_cat()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_id='" . $this->rknclass->get['cat_id'] . "'");

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category id'));
		}
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET category_id='" . $this->rknclass->get['cat_id'] . "', category='$row[cat_name]' WHERE plug_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		echo $row['cat_name'];
	}

	public function update_country()
	{
		if(empty($this->rknclass->get['id']) || !ctype_digit((string) $this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid country id'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "countries SET flagged='" . ($this->rknclass->get['type'] == 'yes' ? '1' : '0') . "' WHERE country_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		if($this->rknclass->get['type'] == 'yes')
		{
			echo "<a href=\"#\" onclick=\"ajax_update('country', '{$this->rknclass->get['id']}', 'no'); return false;\"><font color=\"#136f01\">Yes</font></a>";
		}
		else
		{
			echo "<a href=\"#\" onclick=\"ajax_update('country', '{$this->rknclass->get['id']}', 'yes'); return false;\"><font color=\"#e32c00\">No</font></a>";
		}
	}

	public function view_comments()
	{
		$this->rknclass->page_title='View Comments';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=25; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT count(comment_id) FROM " . TBLPRE . "comments");

		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Latest Comments Submitted</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Poster</th>
	<th scope="col">Date Posted</th>
    <th scope="col">Content Id</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';
  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "comments ORDER BY posted DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">" . (strlen($row[title]) >= 50 ? substr($row[title], 0, 46) . "..." : $row[title]) . "</td>
    <td>{$row['poster']}</td>
	<td>{$this->rknclass->utils->timetostr($row[posted])}</td>
	<td><a href=\"{$this->rknclass->settings['site_url']}/index.php?ctr=view&amp;id={$row['plug_id']}\" target=\"_blank\">{$row['plug_id']}</a></td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_comment&amp;id=$row[comment_id]\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_comment&amp;id=$row[comment_id]&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\" onclick=\"return confirm('Are you sure you want to permanently delete this comment?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=view_videos&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=view_videos&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_comment()
	{

		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid comment id'));
		}

		$this->rknclass->db->query("SELECT count(comment_id) FROM " . TBLPRE . "comments WHERE comment_id='{$this->rknclass->get['id']}'");

		if($this->rknclass->db->result()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The comment couldn\'t be found in the database'));
		}


		$query=$this->rknclass->db->build_query(array('select' => '*',
		                                              'from' => 'comments',
													  'where' => array('comment_id' => $this->rknclass->get['id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This comment does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Update comment';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Update comment');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_comment&amp;id=' . $row['comment_id'] . '');
		$this->rknclass->form->add_input('title', 'input', 'Title', 'Enter the title of the comment', $row['title']);
		$this->rknclass->form->add_input('description', 'textarea', 'Description', 'Enter the main comment body/description', $row['description']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_comment()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid comment id'));
		}

		$this->rknclass->db->query("SELECT count(comment_id) FROM " . TBLPRE . "comments WHERE comment_id='{$this->rknclass->get['id']}'");

		if($this->rknclass->db->result()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The comment couldn\'t be found in the database'));
		}

		if(empty($this->rknclass->post['title']) || empty($this->rknclass->post['description']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields where left blank'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "comments SET title='{$this->rknclass->post['title']}', description='{$this->rknclass->post['description']}' WHERE comment_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully updated comment!', '?ctr=content[and]act=view_comments');
	}

	public function delete_comment()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] == false)
		{
			$this->rknclass->global_tpl->admin_error('Invalid Comment');
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "comments WHERE comment_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		$row=$this->rknclass->db->fetch_array();

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "comments WHERE comment_id='" . $this->rknclass->get['id'] . "' LIMIT 1");

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET total_comments=total_comments-1 WHERE plug_id='{$row['plug_id']}' LIMIT 1");

		if($this->rknclass->get['return_url'] !== '')
		{
			$return_to=$this->rknclass->get['return_url'];
			if(strpos($return_to, '[and]') !== false)
			{
				$return_to=str_replace('[and]', '&', $return_to);
			}
		}
		else
		{
			$return_to='?ctr=content&amp;act=view_comments';
		}
		$this->rknclass->global_tpl->exec_redirect('Comment successfully deleted!', $return_to);
	}

	public function update_admin_notes()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(empty($this->rknclass->post['note']))
		{
			exit($this->rknclass->global_tpl->admin_error('You didn\'t enter any text!'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "notes SET content='{$this->rknclass->post['note']}' WHERE name='admin_dashboard' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Updated notes successfully!', '?ctr=content');
	}

	public function add_category()
	{
		$this->rknclass->page_title='Add category';
		$this->rknclass->global_tpl->admin_header();
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Add new category');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=create_category');
		$this->rknclass->form->add_input('category_name', 'input', 'Category Name', 'Please enter the desired name of the category. To ensure proper seo, please refrain from using characters such as ! and %');
		$this->rknclass->form->add_input('public', 'dropdown', 'Category Permissions', 'Select whether or not you want this category to be public. When set to private, only administrators will be able to submit content in this category', '<option value="1" SELECTED>Public</option><option value="0">Private</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function create_category()
	{
		if(empty($this->rknclass->post['category_name']) || $this->rknclass->post['public'] == '')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_name LIKE '%{$this->rknclass->post['category_name']}%'");
		if($this->rknclass->db->num_rows()>0)
		{
			while($row=$this->rknclass->db->fetch_array())
			{
				$cat_name=strtolower($this->rknclass->post['category_name']);
				$row_name=strtolower($row['cat_name']);
				if($cat_name == $row_name)
				{
					exit($this->rknclass->form->ajax_error('Another category already exists with this name!'));
				}
			}
		}

		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "cats SET cat_name='{$this->rknclass->post['category_name']}', public='{$this->rknclass->post['public']}'");
		$this->rknclass->form->ajax_success('Successfully added category <em>' . $this->rknclass->post['category_name'] .'</em>!');
	}

	public function view_categories()
	{
		$this->rknclass->page_title='Manage Categories';

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Categories</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Name</th>
    <th scope="col">Public</th>
    <th scope="col">Total Content</th>
    <th scope="col">Perc. of Total</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';

		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs");
		$total_plugs=$this->rknclass->db->result();

  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats");
		while($row=$this->rknclass->db->fetch_array())
		{

			$perc=@ceil(($row['total_plugs']/$total_plugs)*100);

			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['cat_name']}</td>
    <td>" . ($row['public'] == '1' ? "<strong><font color=\"green\">Yes</font></strong>" : "<strong><font color=\"red\">No</font></strong>") . "</td>
   <td>{$row['total_plugs']}</td>
   <td>$perc %</td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_category&amp;id=$row[cat_id]\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_category&amp;id=$row[cat_id]\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_category()
	{
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->get['id']}' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The category could not be found in the database!'));
		}

		$row=$this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Edit category';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit category');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_category&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('category_name', 'input', 'Category Name', 'Please enter the desired name of the category. To ensure proper seo, please refrain from using characters such as ! and %', $row['cat_name']);
		$this->rknclass->form->add_input('public', 'dropdown', 'Category Permissions', 'Select whether or not you want this category to be public. When set to private, only administrators will be able to submit content in this category', '<option value="1">Public</option><option value="0"' . ($row['public'] == '0' ? " SELECTED" : "") . '>Private</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_category()
	{
		if(empty($this->rknclass->post['category_name']) || $this->rknclass->post['public'] == '')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank'));
		}

		$this->rknclass->db->query("SELECT cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->get['id']}' LIMIT 1");
		if(strtolower($this->rknclass->db->result()) != strtolower($this->rknclass->post['category_name']))
		{
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_name LIKE '%{$this->rknclass->post['category_name']}%'");
			if($this->rknclass->db->num_rows()>0)
			{
				while($row=$this->rknclass->db->fetch_array())
				{
					$cat_name=strtolower($this->rknclass->post['category_name']);
					$row_name=strtolower($row['cat_name']);
					if($cat_name == $row_name)
					{
						exit($this->rknclass->global_tpl->admin_error('Another category already exists with this name!'));
					}
				}
			}
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET cat_name='{$this->rknclass->post['category_name']}', public='{$this->rknclass->post['public']}' WHERE cat_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET category='{$this->rknclass->post['category_name']}' WHERE category_id='{$this->rknclass->get['id']}'");

		$this->rknclass->global_tpl->exec_redirect('Successfully update category <em>' . $this->rknclass->post['category_name'] .'</em>!', '?ctr=content&act=view_categories');
	}

	public function delete_category()
	{

		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->get['id']}' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The category could not be found in the database!'));
		}

		$row=$this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Remove category';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Remove category');
		$this->rknclass->form->ajax=false;

		$cats='';
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_id!='{$this->rknclass->get['id']}' ORDER by cat_name ASC");
		while($row2=$this->rknclass->db->fetch_array())
		{
			$cats.="<option value=\"{$row2['cat_id']}\">{$row2['cat_name']} " . ($row2['public'] == '1' ? "[public]" : "[private]") . "</option>\n";
		}
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=remove_category&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('cat_id', 'dropdown', 'Content Destination', 'Please select the category where you want all existing plugs in this category to be moved to', $cats);
		$this->rknclass->form->add_input('password', 'password', 'Password', 'Please enter your password to confirm this action. <br /><br /><strong>WARNING: This action <u>CANNOT</u> be undone!</strong>', '');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function remove_category()
	{
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The category could not be found in the database!'));
		}

		$row=$this->rknclass->db->fetch_array();

		if(empty($this->rknclass->post['password']) || empty($this->rknclass->post['cat_id']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		$pass=$this->rknclass->user['password'];
		$salt=$this->rknclass->user['salt'];

		if((string)$this->rknclass->utils->pass_hash($this->rknclass->post['password'], $salt) !== (string)$pass)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid password!'));
		}

		$cat_name=$this->rknclass->db->result($this->rknclass->db->query("SELECT cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['cat_id']}' LIMIT 1"));

		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET category_id='{$this->rknclass->post['cat_id']}', category='$cat_name' WHERE category_id='{$this->rknclass->get['id']}'");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs=total_plugs+{$row['total_plugs']} WHERE cat_id='{$this->rknclass->post['cat_id']}' LIMIT 1");

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE category_id='{$this->rknclass->get['id']}'");
		$num_plugs=$this->rknclass->db->result();
		$this->rknclass->global_tpl->exec_redirect('Successfully removed category! Plugs affected: ' . intval($num_plugs), '?ctr=content&act=view_categories');
	}

	public function ads_between_plugs()
	{
		$this->rknclass->page_title='Ads Between Plugs';
		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Ads Between Plugs</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title" width="85%">Title</th>
	<th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';
		$ads=@unserialize($this->rknclass->settings['ads_between_plugs']);

		foreach($ads as $id => $data)
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">" . base64_decode($data['title']) . "</td>
	<td><a href=\"index.php?ctr=content&amp;act=update_ad_bp&amp;id=$id\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_ad_bp&amp;id=$id\" onclick=\"return confirm('Are you sure you want to permanently remove this advertisement?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}

		echo '</table>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function new_ad_bp()
	{
		$this->rknclass->page_title='Add Advertisement';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add new Advertisement between listings');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_new_ad_bp');
		$this->rknclass->form->add_input('title', 'input', 'Advertisement Title', 'Please enter a name for your advertisement so that you can identify it on the admin page');
		$this->rknclass->form->add_input('code', 'textarea', 'Advertisement Content', 'Please enter the html code for this advertisement');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_new_ad_bp()
	{
        if(defined('RKN__demo') AND RKN__demo == '1')
        {
            exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
        }
		$ads=@unserialize($this->rknclass->settings['ads_between_plugs']);

		if($this->rknclass->post['title'] == '' || $this->rknclass->post['code'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields went blank!'));
		}

		$ads[]=array('title' => base64_encode($this->rknclass->post['title']),
		             'code'  => base64_encode(stripslashes($_POST['code'])));

		$ads=@serialize($ads);

		$this->rknclass->cache->update_settings_and_cache(array('ads_between_plugs' => $ads));

		$this->rknclass->global_tpl->exec_redirect('Successfully add new listings advertisement!', '?ctr=content&act=ads_between_plugs');
	}

	public function update_ad_bp()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if((string)($this->rknclass->get['id']) == '') //We're dealing with zeros due to array storage of ads
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement specified'));
		}

		$ads=@unserialize($this->rknclass->settings['ads_between_plugs']);

		if(@!isset($ads['' . $this->rknclass->get['id'] . '']))
		{
			exit($this->rknclass->global_tpl->admin_error('The advertisement couldn\'t be found in the datastore!'));
		}

		$this->rknclass->page_title='Manage Advertisement';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Manage Advertisement between listings');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=edit_ad_bp&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('title', 'input', 'Advertisement Title', 'Please enter a name for your advertisement so that you can identify it on the admin page', base64_decode($ads['' . $this->rknclass->get['id'] . '']['title']));
		$this->rknclass->form->add_input('code', 'textarea', 'Advertisement Content', 'Please enter the html code for this advertisement', base64_decode($ads['' . $this->rknclass->get['id'] . '']['code']));
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_ad_bp()
	{
		if((string)($this->rknclass->get['id']) == '') //We're dealing with zeros due to array storage of ads
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement specified'));
		}

		$ads=@unserialize($this->rknclass->settings['ads_between_plugs']);

		if(@!isset($ads['' . $this->rknclass->get['id'] . '']))
		{
			exit($this->rknclass->global_tpl->admin_error('The advertisement couldn\'t be found in the datastore!'));
		}

		if($this->rknclass->post['title'] == '' || $this->rknclass->post['code'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields went blank!'));
		}

		$ads['' . $this->rknclass->get['id'] . '']=array('title' => base64_encode($this->rknclass->post['title']),
		                                                 'code'  => base64_encode(stripslashes($_POST['code'])));

		$ads=@serialize($ads);
		$this->rknclass->cache->update_settings_and_cache(array('ads_between_plugs' => $ads));

		$this->rknclass->global_tpl->exec_redirect('Successfully updated listings advertisement!', '?ctr=content&act=ads_between_plugs');
	}

	public function delete_ad_bp()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if((string)($this->rknclass->get['id']) == '') //We're dealing with zeros due to array storage of ads
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement specified'));
		}

		$ads=@unserialize($this->rknclass->settings['ads_between_plugs']);

		if(@!isset($ads['' . $this->rknclass->get['id'] . '']))
		{
			exit($this->rknclass->global_tpl->admin_error('The advertisement couldn\'t be found in the datastore!'));
		}

		unset($ads['' . $this->rknclass->get['id'] . '']);

		$re_ordered=array();

		foreach($ads as $value)
		{
			$re_ordered[]=$value;
		}

		$ads=@serialize($re_ordered);

		$this->rknclass->cache->update_settings_and_cache(array('ads_between_plugs' => $ads));
		$this->rknclass->global_tpl->exec_redirect('Advertisement removed successfully!', '?ctr=content&act=ads_between_plugs');
	}

	public function ads_bp_settings()
	{
		$this->rknclass->page_title='Listing Ads\' settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Listing Ads\' settings');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=edit_ad_bp_settings');
		$this->rknclass->form->add_input('ads_between_plugs_enabled', 'dropdown', 'Listings advertisements enabled?', 'Select whether or not you want to enable to display of advertisements between plugs', '<option value="0">No</option><option value="1"' . ($this->rknclass->settings['ads_between_plugs_enabled'] == '1' ? " SELECTED" : "") . '>Yes</option>');
		$this->rknclass->form->add_input('ads_between_plugs_count', 'input', 'Display every x plugs', 'Enter the number of plugs to display before displaying an advertisement. If you have 20 plugs per page, you might want to set this to 5 for example', $this->rknclass->settings['ads_between_plugs_count']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_ad_bp_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }

		if($this->rknclass->post['ads_between_plugs_enabled'] == '' || $this->rknclass->post['ads_between_plugs_count'] == '')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}

		$this->rknclass->cache->update_settings_and_cache(array('ads_between_plugs_enabled' => $this->rknclass->post['ads_between_plugs_enabled'], 'ads_between_plugs_count' => $this->rknclass->post['ads_between_plugs_count']));

		$this->rknclass->form->ajax_success('Successfully updated settings!');
	}

	public function download_p3()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		$this->rknclass->load_object('p3_archive');

		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid plug id'));
		}

		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		if(intval($this->rknclass->db->result())<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The plug couldn\'t be found in the database'));
		}

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This plug does not exist in our database'));
		}

		$this->rknclass->p3_archive->generate($this->rknclass->get['plug_id']);
	}

	public function create_blog_entry()
	{
		define('TINYMCE', true);
		$this->rknclass->page_title='Create new blog entry';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Create new Blog article');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_article_submission');
		$this->rknclass->form->add_input('blog_title', 'input', 'Entry Title', 'Please enter the name of this article / blog entry.');
		$this->rknclass->form->add_input('blog_description', 'tinymce', 'Quick Description');
		$this->rknclass->form->add_input('blog_body', 'tinymce', 'Article Body');
		$this->rknclass->form->add_input('blog_tags', 'input', 'Tags', 'Please enter some tags for your blog entry. Tags are keywords which you think are the most relevant to your article.<br /><br /><strong>These should be one word, seperated by a single space</strong>');
		$this->rknclass->form->add_input('blog_schedule', 'input', 'Schedule', 'If you would like to schedule your article to appear at a specific time, please enter it here', date('j M Y g:i:sa', time()));
		$this->rknclass->form->add_input('blog_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail for your blog entry on the contenst listings page. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('blog_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('blog_category', 'dropdown', 'Select Category', 'Please select a category for your blog entry from the list', $categories);

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_article_submission()
	{
		$check=array('blog_title', 'blog_description', 'blog_tags', 'blog_category', 'blog_body', 'blog_schedule');

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['blog_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

		$remote = false;

		if(isset($this->rknclass->post['blog_image_remote']) AND !empty($this->rknclass->post['blog_image_remote']))
		{
			$remote = true;
		}

		if($remote === false)
		{
			if(!is_uploaded_file($_FILES['blog_image']['tmp_name']))
			{
				exit($this->rknclass->global_tpl->admin_error('You didn\'t upload an image!'));
			}
		}
		else
		{
			if(substr($this->rknclass->post['blog_image_remote'], 0, 7) !== 'http://')
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
			}
			$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
			@copy($this->rknclass->post['blog_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
		}


		($remote === false ? $info=@getimagesize($_FILES['blog_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

		$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

		if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid Image Type'));
		}

		if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
		{
			exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
		{
			@move_uploaded_file($_FILES['blog_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
		}
		else
		{
			@rename(RKN__fullpath . 'tmp/' . $rname, RKN__fullpath . 'tmp/' . $name);
		}

		$blog_tags=$this->rknclass->utils->process_tags($blog_tags); //Makes sure the tags are formatted correctly

		$blog_description = $this->rknclass->db->escape($_POST['blog_description']);

		$query=$this->rknclass->db->build_query(array('insert' => 'plugs',
		                                              'set' => array('title' => $blog_title,
														             'description' => $blog_description,
														             'tags' => $blog_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '5',
														             'poster' => $this->rknclass->user['username'],
														             'poster_id' => $this->rknclass->user['user_id'],
																	 'approved' => '1',
														             'posted' => @strtotime($this->rknclass->post['blog_schedule']))));

		$body=$this->rknclass->db->escape($_POST['blog_body']);

		$this->rknclass->db->query($query);

		$plug_id=$this->rknclass->db->insert_id();

		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($plug_id, $blog_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$plug_id}' LIMIT 1");
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "blog_articles SET plug_id='$plug_id', body='$body'");

		header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id=$plug_id");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs=total_plugs+1 WHERE user_id='{$this->rknclass->user['user_id']}'");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs=total_plugs+1 WHERE cat_name='{$cats['cat_id']}' LIMIT 1");
	}

	public function edit_blog_entry()
	{
		define('TINYMCE', true);

		$this->rknclass->db->query("SELECT title,description,tags,category_id,posted FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('This blog entry could not be found in the database!'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$this->rknclass->page_title='Edit blog entry';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit Blog article');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_blog_entry&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('blog_title', 'input', 'Entry Title', 'Please enter the name of this article / blog entry.', $row['title']);
		$this->rknclass->form->add_input('blog_description', 'tinymce', 'Quick Description', 'Please enter a short description for the blog entry. This should be text only, and does not display on the main blog page', $row['description']);
		$this->rknclass->form->add_input('blog_body', 'tinymce', 'Article Body', NULL, $this->rknclass->db->result($this->rknclass->db->query("SELECT body FROM " . TBLPRE ."blog_articles WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1")));
		$this->rknclass->form->add_input('blog_tags', 'input', 'Tags', 'Please enter some tags for your blog entry. Tags are keywords which you think are the most relevant to your article.<br /><br /><strong>These should be one word, seperated by a single space</strong>', $row['tags']);
		$this->rknclass->form->add_input('blog_schedule', 'input', 'Schedule', 'If you would like to schedule your article to appear at a specific time, please enter it here', date('j M Y g:i:sa', $row['posted']));
		$this->rknclass->form->add_input('blog_image', 'image', 'Upload and Crop Image', 'Please upload an image which will be used as the thumbnail for your blog entry on the contenst listings page. <strong>Better quality images, attract more viewers!</strong><br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');
		$this->rknclass->form->add_input('blog_image_remote', 'input', 'Rip remote image', '<strong>Alternatively</strong>, you can enter the url to a remote image which you\'d like to rip<br /><br /><strong>Images must be in either jpeg/jpg, gif or png format</strong>');

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='';

		while($row2=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row2[cat_id]\"" . ($row['category_id'] == $row2['cat_id'] ? ' SELECTED' : '') . ">$row2[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('blog_category', 'dropdown', 'Select Category', 'Please select a category for your blog entry from the list', $categories);

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function view_blog_entries()
	{
		$this->rknclass->page_title='View blog entries';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=25; //TODO: Add option in ACP

		if(!empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type='5' AND poster_id='{$this->rknclass->get['user_id']}'");
		}
		else
		{
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE type='5'");
		}

		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">View blog entries</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Poster</th>
    <th scope="col">Views</th>
    <th scope="col">Category</th>
    <th scope="col">Posted</th>
    <th scope="col">Chosen</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';

		if(!empty($this->rknclass->get['user_id']))
		{
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type='5' AND poster_id='{$this->rknclass->get['user_id']}' ORDER BY posted DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		else
		{
  			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE type='5' ORDER BY posted DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "<tr id=\"rows\">
    <td id=\"title\"><img src=\"images/type-link.jpg\" id=\"content-icon\" title=\"Blog Entry\" /><a href=\"{$this->rknclass->settings['site_url']}/index.php?ctr=view&amp;id={$row['plug_id']}\" target=\"_blank\">" . (strlen($row['title']) >= 50 ? substr($row['title'], 0, 46) . "..." : $row['title']) . "</a></td>
    <td>$row[poster]</td>
    <td id=\"views-$row[plug_id]\" ondblclick=\"edit_views('$row[plug_id]', '$row[views]');\">$row[views]</td>
    <td id=\"cat-$row[plug_id]\" ondblclick=\"edit_cat('$row[plug_id]');\">$row[category]</td>
    <td>" . ($row['posted'] > time() ? "<font color=\"#FF3300\">" : "") . $this->rknclass->utils->timetostr($row[posted]) . ($row['posted'] < time() ? "</font>" : "") . "</td>
    <td id=\"chosen-$row[plug_id]\"><strong>" . ($row['chosen'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('chosen', '$row[plug_id]', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('chosen', '$row[plug_id]', 'yes'); return false;\">No</a>") . "</strong></td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_blog_entry&amp;id=$row[plug_id]\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_content&amp;id=$row[plug_id]&amp;return_url=?" . str_replace('&', '[and]', $_SERVER['QUERY_STRING']) . "\" onclick=\"return confirm('Are you sure you want to permanently delete this blog entry?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

       if(isset($this->rknclass->get['user_id']) AND !empty($this->rknclass->get['user_id']))
        {
            if($this->pager_data['previous'] !== false)
            {
                echo '<a href="index.php?ctr=content&amp;act=view_blog_entries&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
            }
            if($this->pager_data['next'] !== false)
            {
                echo '<a href="index.php?ctr=content&amp;act=view_blog_entries&amp;user_id=' . $this->rknclass->get['user_id'] . '&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
            }
        }
        else
        {
            if($this->pager_data['previous'] !== false)
            {
                echo '<a href="index.php?ctr=content&amp;act=view_blog_entries&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
            }
            if($this->pager_data['next'] !== false)
            {
                echo '<a href="index.php?ctr=content&amp;act=view_blog_entries&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
            }
        }
        echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_blog_entry()
	{
		if($this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid blog id'));
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
			exit($this->rknclass->global_tpl->admin_error('This plug does not exist in our database'));
		}

		$row=$this->rknclass->db->fetch_array();

		if($this->rknclass->user['group']['is_restricted'] == '1' AND $this->rknclass->user['restrictions']['own_content'] == '1' AND $row['poster_id'] !== $this->rknclass->user['user_id'])
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to edit this item!'));
		}

		$check=array('blog_title', 'blog_description', 'blog_tags', 'blog_category', 'blog_body', 'blog_schedule');

		/*==============================
		Its much quicker to do a foreach
		rather than writting a gazillion
		ifs and elses. Predator = Smart
		===============================*/

		foreach($check as $key)
		{
			if($this->rknclass->post[$key] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
			$$key=$this->rknclass->post[$key];
		}

		/*=============================
		We better check and make sure
		they aren't trying to submit
		to a bogus category...
		==============================*/

		$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['blog_category']}' LIMIT 1");

		if($this->rknclass->db->num_rows()!==1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid category'));
		}

		$cats=$this->rknclass->db->fetch_array(); //gets our cats array for insert later

			if(is_uploaded_file($_FILES['blog_image']['tmp_name']) || !empty($this->rknclass->post['blog_image_remote']))
		{

			/*=================================
			If a new thumb has been uploaded,
			this section will check and process
			it, as well as removing the previous
			==================================*/

			$remote = false;

			if(isset($this->rknclass->post['blog_image_remote']) AND !empty($this->rknclass->post['blog_image_remote']))
			{
				$remote = true;
			}

			if($remote === true)
			{
				if(substr($this->rknclass->post['blog_image_remote'], 0, 7) !== 'http://')
				{
					exit($this->rknclass->global_tpl->admin_error('Invalid remote image url!'));
				}
				$rname = $this->rknclass->utils->rand_chars(7) . 'tmp';
				@copy($this->rknclass->post['blog_image_remote'], RKN__fullpath . 'tmp/' . $rname) or exit($this->rknclass->global_tpl->admin_error('Unable to rip remote image'));
			}


			($remote === false ? $info=@getimagesize($_FILES['blog_image']['tmp_name']) : $info=@getimagesize(RKN__fullpath . 'tmp/' . $rname));

			$allowed_images=array('image/jpeg', 'image/png', 'image/gif');

			if(in_array($info['mime'], $allowed_images, true) === false || $info[0] === 0 || $info[1] === 0)
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid Image Type'));
			}

			if($info['0'] < $this->rknclass->settings['thumb_width'] || $info['1'] < $this->rknclass->settings['thumb_height'])
			{
				exit($this->rknclass->global_tpl->admin_error("The image you have uploaded is too small! <br />Images must be at least {$this->rknclass->settings['thumb_width']} x {$this->rknclass->settings['thumb_height']}"));
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
				@move_uploaded_file($_FILES['blog_image']['tmp_name'], RKN__fullpath . 'tmp/' . $name) or exit($this->rknclass->global_tpl->admin_error('Unable to store thumbnail - Please alert administration of this problem'));
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

		$blog_tags=$this->rknclass->utils->process_tags($blog_tags); //Makes sure the tags are formatted correctly

		$blog_description = $this->rknclass->db->escape($_POST['blog_description']);

		$query=$this->rknclass->db->build_query(array('update' => 'plugs',
		                                              'set' => array('title' => $blog_title,
														             'description' => $blog_description,
														             'tags' => $blog_tags,
																	 'category' => $cats['cat_name'],
																	 'category_id' => $cats['cat_id'],
																	 'thumb' => $name,
																	 'type' => '5',
																	 'cropped' => $cropped,
																	 'posted' => strtotime($this->rknclass->post['blog_schedule'])),
													  'where' => array('plug_id' => $row['plug_id']),
													  'limit' => '1'));
		$this->rknclass->db->query($query);

		$seo_url = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($row['plug_id'], $blog_title, $cats['cat_name']));
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$plug_id}' LIMIT 1");

		$body=$this->rknclass->db->escape($_POST['blog_body']);

		$this->rknclass->db->query("UPDATE " . TBLPRE . "blog_articles SET body='$body' WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($name !== $row[thumb])
		{
			header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=cropper&id={$this->rknclass->get['id']}");
		}
		else
		{
			$this->rknclass->global_tpl->exec_redirect('Blog successfully updated!', '?ctr=content&act=view_blog_entries');
		}
	}

	public function add_sponsor()
	{
		$this->rknclass->page_title='Add content sponsor';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add new Content Sponsor');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=create_sponsor');

		$this->rknclass->form->add_input('sponsor_url', 'input', 'Sponsor Url', 'Enter your sponsor/content provider\'s website address', 'http://');
		$this->rknclass->form->add_input('sponsor_name', 'input', 'Sponsor Name', 'Please enter a <strong>unique</strong> name for this sponsor');
		$this->rknclass->form->add_input('sponsor_description', 'textarea', 'Sponsor Description', 'Please provide a short description for this sponsor. Optional, but could come in useful if you have a lot of sponsors.');
		$this->rknclass->form->add_input('sponsor_username', 'input', 'Sponsor Username', 'Its very easy to forget the login information for your various sponsors. You can optionally add your sponsor username here for safe-keeping.');
		$this->rknclass->form->add_input('sponsor_password', 'input', 'Sponsor Password', 'Enter your account password for this sponsor.');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function create_sponsor()
	{
		$check=array('sponsor_url', 'sponsor_name');

		foreach($check as $form_value)
		{
			if($this->rknclass->post['' . $form_value . ''] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more required fields were left blank!'));
			}
		}

		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "sponsors SET sponsor_name='{$this->rknclass->post['sponsor_name']}', sponsor_description='{$this->rknclass->post['sponsor_description']}', sponsor_url='{$this->rknclass->post['sponsor_url']}', sponsor_username='{$this->rknclass->post['sponsor_username']}', sponsor_password='{$this->rknclass->post['sponsor_password']}'");

		$this->rknclass->global_tpl->exec_redirect('Successfully added new sponsor!', '?ctr=content&act=add_sponsor_site');
	}

	public function add_sponsor_site()
	{
		$this->rknclass->db->query("SELECT sponsor_name,sponsor_id FROM " . TBLPRE . "sponsors ORDER BY sponsor_name ASC");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->exec_redirect('You must add at least one content provider first!', '?ctr=content&act=add_sponsor'));
		}

		$content_providers=NULL;

		while($row=$this->rknclass->db->fetch_array())
		{
			$content_providers.="<option value=\"{$row['sponsor_id']}\">{$row['sponsor_name']}</option>";
		}

		$this->rknclass->page_title='Add sponsor site';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add new sponsor site');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=create_sponsor_site');

		$this->rknclass->form->add_input('sponsor_site_url', 'input', 'Sponsor Site Url', 'Enter your sponsor site\'s main url', 'http://');
		$this->rknclass->form->add_input('sponsor_site_name', 'input', 'Sponsor Name', 'Please enter a <strong>unique</strong> name for this sponsor site');
		$this->rknclass->form->add_input('sponsor_site_parent', 'dropdown', 'Content Provider', 'Please select this sponsor site\'s parent from the list of content providers', $content_providers);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function create_sponsor_site()
	{
		if($this->rknclass->post['sponsor_site_url'] == '' || $this->rknclass->post['sponsor_site_name'] == '' || $this->rknclass->post['sponsor_site_parent'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		if(!is_numeric($this->rknclass->post['sponsor_site_parent']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor!'));
		}

		$count=$this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->post['sponsor_site_parent']}' LIMIT 1"));

		if(intval($count) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor!'));
		}

		$this->rknclass->db->query("SELECT count(sponsor_site_url) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_url='{$this->rknclass->post['sponsor_site_url']}' LIMIT 1");

		if(intval($this->rknclass->db->result()) > 0)
		{
			exit($this->rknclass->global_tpl->admin_error('Another sponsor site with this url already exists!'));
		}

		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "sponsors_sites SET sponsor_site_url='{$this->rknclass->post['sponsor_site_url']}', sponsor_site_name='{$this->rknclass->post['sponsor_site_name']}', sponsor_site_parent='{$this->rknclass->post['sponsor_site_parent']}'");

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors SET sponsor_site_count=sponsor_site_count+1 WHERE sponsor_id='{$this->rknclass->post['sponsor_site_parent']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully added sponsor site', '?ctr=content&act=new_ad');
	}

	public function new_ad()
	{
		$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites");
		if($this->rknclass->db->result() < 1)
		{
			exit($this->rknclass->global_tpl->exec_redirect('You must add at least 1 sponsor site!', '?ctr=content&act=add_sponsor_site'));
		}

		$this->rknclass->page_title='Create new advertisement';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('New advertisement Wizard');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=new_ad_continued');

		$this->rknclass->form->add_input('ad_title', 'input', 'Advertisement Title', 'Please enter a name for your advertisement');
		$this->rknclass->form->add_input('ad_type', 'dropdown', 'Advertisement Type', 'Please select the type of advertisement you are adding. In most cases, this will be <em>Banner ad</em>', '<option value="banner" SELECTED>Banner Ad</option><option value="html">HTML</option>');
		$this->rknclass->form->add_input('ad_sponsor_site', 'dropdown', 'Advertisement Sponsor Site', 'Please select the sponsor site you wish to assign this advertisement to', $this->get_sponsor_dropdown_list());

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function new_ad_continued()
	{
		$ad_types=array('html', 'banner');

		if(in_array($this->rknclass->post['ad_type'], $ad_types) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement type'));
		}

		if(!is_numeric($this->rknclass->post['ad_sponsor_site']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site'));
		}

		if($this->rknclass->post['ad_title'] == '' || $this->rknclass->post['ad_sponsor_site'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields where left blank'));
		}

		$type=$this->rknclass->post['ad_type'];

		if($type === 'html')
		{
			define('TINYMCE', true);
		}
		$this->rknclass->page_title='Create new advertisement';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Create new advertisement');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols

		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=create_' . $type . '_ad&amp;id=' . $this->rknclass->post['ad_sponsor_site']);
		$this->rknclass->form->add_input('ad_title', 'input', 'Advertisement Title', 'Please enter a name for your advertisement', $this->rknclass->post['ad_title']);
		if($type === 'banner')
		{
			$this->rknclass->form->add_input('ad_banner', 'image', 'Advertisement Banner', 'Please upload a banner for this advertisement. Please ensure banners are in either jpg/jpeg, gif or png format');
		}
		else
		{
			$this->rknclass->form->add_input('ad_html', 'tinymce', 'Advertisement HTML', 'Please enter the html for your advertisement');
		}
		$this->rknclass->form->add_input('ad_flagged', 'dropdown', 'Advertisement Flagged?', 'Please select whether or not you want this advertisement to be flagged. <br /><br />Flagged advertisements will only appear to users from <a href="index.php?ctr=content&amp;act=flagged_countries" target="_blank">Flagged countries</a>', '<option value="0" SELECTED>No</option><option value="1">Yes</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function create_banner_ad()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(!is_numeric($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		$count = $this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}' LIMIT 1"));

		if(intval($count) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		if($this->rknclass->post['ad_title'] == '' || !file_exists($_FILES['ad_banner']['tmp_name']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		$allowed_types=array('image/jpeg', 'image/gif', 'image/png');

		$info=@getimagesize($_FILES['ad_banner']['tmp_name']);

		if(in_array($info['mime'], $allowed_types) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Banner images must be either either jpg/jpeg, gif or png!'));
		}

		$new_name=$this->rknclass->utils->rand_chars(5);

		switch($info['mime'])
		{
			case 'image/jpeg':
				$new_name.='.jpg';
				break;

			case 'image/gif':
				$new_name.='.gif';
				break;

			case 'image/png':
				$new_name.='.png';
				break;

			default:
				$new_name.='.jpg';
		}

		@move_uploaded_file($_FILES['ad_banner']['tmp_name'], RKN__fullpath . 'banner_ads/' . $new_name) or exit($this->rknclass->global_tpl->admin_error('Unable to save uploaded banner ad. <br />Please check CHMOD permissions on the banner_ads folder (should be set to 0777)'));

		$image_data=array('width'  => $info['0'],
		                  'height' => $info['1'],
						  'mime'   => $info['mime'],
						  'name'   => $new_name);

		if($this->rknclass->post['ad_flagged'] == '1')
		{
			$flagged = '1';
		}
		else
		{
			$flagged = '0';
		}

		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "sponsors_ads SET ad_sponsor_site_id='{$this->rknclass->get['id']}', ad_title='{$this->rknclass->post['ad_title']}', ad_type='banner', ad_data='" . serialize($image_data) . "', ad_flagged='$flagged'");

		$this->rknclass->db->query("SELECT count(banner_link_id) FROM " . TBLPRE . "sponsors_banner_links LIMIT 1");

		if($this->rknclass->db->result() < 1)
		{
			$this->rknclass->global_tpl->exec_redirect('Successfully created new <strong>banner ad</strong><br /><br />Taking you to <strong>create a banner link for this sponsor</strong>', '?ctr=content&act=new_banner_link');
		}
		else
		{
			$this->rknclass->global_tpl->exec_redirect('Successfully created new <strong>banner ad</strong>', '?ctr=content&act=manage_sponsor_ads');
		}
	}

	public function create_html_ad()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(!is_numeric($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		$count = $this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}' LIMIT 1"));

		if(intval($count) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		if($this->rknclass->post['ad_title'] == '' || $this->rknclass->post['ad_html'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		$html=$this->rknclass->db->escape($_POST['ad_html']);

		if($this->rknclass->post['ad_flagged'] == '1')
		{
			$flagged = '1';
		}
		else
		{
			$flagged = '0';
		}

		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "sponsors_ads SET ad_sponsor_site_id='{$this->rknclass->get['id']}', ad_title='{$this->rknclass->post['ad_title']}', ad_type='html', ad_data='" . $html . "', ad_flagged='$flagged'");

		$this->rknclass->global_tpl->exec_redirect('Successfully created new <strong>html ad</strong>', '?ctr=content&act=manage_sponsor_ads');
	}

	public function add_feed()
	{
		$this->rknclass->page_title='Add feed';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add new Feed');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=add_new_feed_continued');

		$this->rknclass->form->add_input('feed_url', 'input', 'Feed Url', 'Please enter the url to the content feed', 'http://');
		$this->rknclass->form->add_input('feed_type', 'dropdown', 'Feed Type', 'Please select the type of feed you are adding.', '<option value="rss" SELECTED>RSS 2.0</option>');
		$this->rknclass->form->add_input('feed_site', 'input', 'Feed site', 'Some trade partner\'s feeds may reside on a different url than their actual site(s). If you would like to manually assign this feed to a specific site in the trade system, please enter its url here. Otherwise, leave blank.<br /><br />This option is used when determining a feed\'s ratio for the auto feed importer');

		$fis = @unserialize($this->rknclass->settings['feed_import_settings']);

		$flagged = ($fis['who2'] == 'flagged' ? TRUE : FALSE);

		if($flagged) $this->rknclass->form->add_input('feed_flagged', 'dropdown', 'Mark for import?', 'Please select whether or not you want this feed to be imported automatically', '<option value="1">Yes</option><option value="0" SELECTED>No</option>');

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function add_new_feed_continued()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		$supported_feeds = array('rss');

		if($this->rknclass->post['feed_url'] == '' || in_array($this->rknclass->post['feed_type'], $supported_feeds) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('One or more required fields were left blank!'));
		}

		if(strpos($this->rknclass->post['feed_url'], '&amp;') !== false)
		{
			$this->rknclass->post['feed_url'] = str_replace('&amp;', '&', $this->rknclass->post['feed_url']);
		}

		$xml=@file_get_contents($this->rknclass->post['feed_url']);

		@simplexml_load_string($xml) or exit($this->rknclass->global_tpl->admin_error('Unable to read feed data'));

		$parser=new SimpleXMLElement($xml);
		$title=strip_tags($parser->channel->title);
		$description=strip_tags($parser->channel->description);

		$title=$this->rknclass->db->escape($title);
		$description=$this->rknclass->db->escape($description);

		if(empty($this->rknclass->post['feed_site']))
		{
			$url = $this->rknclass->utils->rkn_url_parser($this->rknclass->post['feed_url']);
		}
		else
		{
			$url = $this->rknclass->utils->rkn_url_parser($this->rknclass->post['feed_site']);
		}

		$this->rknclass->db->query("SELECT site_id FROM " . TBLPRE . "sites WHERE url='$url' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('This feed\'s site url could not be found in the trade system'));
		}

		$site_id = $this->rknclass->db->result();

		$fis = @unserialize($this->rknclass->settings['feed_import_settings']);

		$flagged = ($fis['who2'] == 'flagged' ? TRUE : FALSE);

		if($flagged)
		{
			if(empty($this->rknclass->post['feed_flagged']))
			{
				$flagged = '0';
			}
			else
			{
				$flagged = '1';
			}
		}
		else
		{
			$flagged = '0';
		}
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "feeds SET feed_url='{$this->rknclass->post['feed_url']}', feed_title='$title', feed_description='$description', feed_type='{$this->rknclass->post['feed_type']}' ,site_id='$site_id', feed_flagged='$flagged'");

		$this->rknclass->global_tpl->exec_redirect('Successfully added feed!', '?ctr=content&act=edit_feed&id=' . $this->rknclass->db->insert_id());
	}

	public function edit_feed()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if($this->rknclass->get['id'] == '' || !is_numeric($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid feed specified'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "feeds WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");
		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Feed not found in database'));
		}

		$fis = @unserialize($this->rknclass->settings['feed_import_settings']);

		$flagged = ($fis['who2'] == 'flagged' ? TRUE : FALSE);

		$row=$this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Edit feed';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit Feed');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_feed&amp;id=' . $this->rknclass->get['id']);

		$this->rknclass->form->add_input('feed_url', 'input', 'Feed Url', 'Please enter the url to the content feed', $row['feed_url']);
		$this->rknclass->form->add_input('feed_title', 'input', 'Feed Title', 'Please enter a title for this RSS feed', $row['feed_title']);
		$this->rknclass->form->add_input('feed_description', 'textarea', 'Feed Description', 'Please enter a short description for this RSS feed', $row['feed_description']);
		$this->rknclass->form->add_input('feed_type', 'dropdown', 'Feed Type', 'Please select the type of feed you are adding.', '<option value="rss" SELECTED>RSS 2.0</option>');

		if($flagged) $this->rknclass->form->add_input('feed_flagged', 'dropdown', 'Mark for import?', 'Please select whether or not you want this feed to be imported automatically', '<option value="1">Yes</option><option value="0"' . ($row['feed_flagged'] == '0' ? ' SELECTED' : '') . '>No</option>');

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_feed()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if($this->rknclass->get['id'] == '' || !is_numeric($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid feed specified'));
		}

		$this->rknclass->db->query("SELECT count(feed_id) FROM " . TBLPRE . "feeds WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");

		if(intval($this->rknclass->db->result()) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Feed not found in database'));
		}

		if(strpos($this->rknclass->post['feed_url'], '&amp;') !== false)
		{
			$this->rknclass->post['feed_url'] = str_replace('&amp;', '&', $this->rknclass->post['feed_url']);
		}

		$check=array('feed_url', 'feed_title', 'feed_type');
		foreach($check as $field)
		{
			if($this->rknclass->post['' . $field . ''] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank'));
			}
		}

		$supported_feeds=array('rss');

		if(in_array($this->rknclass->post['feed_type'], $supported_feeds) === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid feed type'));
		}

		$xml=@file_get_contents($this->rknclass->post['feed_url']) or exit($this->rknclass->global_tpl->admin_error('Invalid feed specified'));

		@simplexml_load_string($xml) or exit($this->rknclass->global_tpl->admin_error('Invalid feed specified'));

		$fis = @unserialize($this->rknclass->settings['feed_import_settings']);

		$flagged = ($fis['who2'] == 'flagged' ? TRUE : FALSE);

		if($flagged)
		{
			if(!isset($this->rknclass->post['feed_flagged']))
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank'));
			}
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "feeds SET feed_url='{$this->rknclass->post['feed_url']}', feed_title='{$this->rknclass->post['feed_title']}', feed_description='{$this->rknclass->post['feed_description']}'" . ($flagged ? ", feed_flagged='{$this->rknclass->post['feed_flagged']}'" : '') . " WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Feed successfully updated!', '?ctr=content&act=manage_feeds');
	}

	public function manage_feeds()
	{
		$this->rknclass->page_title='Content Feeds';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT count(feed_id) FROM " . TBLPRE . "feeds");

		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();

		$fis = @unserialize($this->rknclass->settings['feed_import_settings']);

		$flagged = ($fis['who2'] == 'flagged' ? TRUE : FALSE);

		echo '
        <div class="page-title">Content Feeds</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Description</th>
    <th scope="col">Type</th>' . ($flagged ? "\n\t" . '<th scope="col">Auto</th>' : '') . '
    <th scope="col">Import Content</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';
  		$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "feeds ORDER BY feed_title ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

		while($row=$this->rknclass->db->fetch_array($result))
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">" . (strlen($row['feed_title']) > 40 ? substr($row['feed_title'], 0, 37) . '...' : $row['feed_title']) . "</td>
    <td>" . (strlen($row['feed_description']) > 40 ? substr($row['feed_description'], 0, 37) . '...' : $row['feed_description']) . "</td>
    <td>" . ucfirst($row['feed_type']) . "</td>";
    if($flagged) echo "<td>" . ($row['feed_flagged'] == '1' ? '<strong><font color="green">Yes</font></strong>' : '<strong><font color="red">No</font></strong>');
	echo "<td><a href=\"index.php?ctr=content&amp;act=feed_import&amp;id={$row['feed_id']}\">View Media</a></td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_feed&amp;id={$row['feed_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_feed&amp;id={$row['feed_id']}\" onclick=\"return confirm('Are you sure you want to permanently delete this feed?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function feed_import()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		@set_time_limit(180);

		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid feed id'));
		}

		$this->rknclass->db->query("SELECT feed_url FROM " . TBLPRE . "feeds WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Feed not found in database'));
		}

		$feed_url=$this->rknclass->db->result();

		$rss = file_get_contents($feed_url);

		$parser = new SimpleXMLElement($rss);

		$this->rknclass->page_title='Import Feed Content';

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Retrieved Feed Data</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Description</th>
    <th scope="col">Thumb' . ($this->rknclass->get['type'] !== 'with_preview' ? " <a href=\"index.php?ctr=content&amp;act=feed_import&amp;id={$this->rknclass->get['id']}&amp;type=with_preview\"> (Preview?)</a>" :  "<a href=\"index.php?ctr=content&amp;act=feed_import&amp;id={$this->rknclass->get['id']}\"> (Hide?)</a>") . '</th>
  </tr>';

  		$num = 0;
		foreach($parser->channel->item as $item)
		{
			if(isset($item->thumb))
			{
				$thumb=$item->thumb;
			}
			elseif($item->enclosure['url'])
			{
				$thumb=$item->enclosure['url'];
			}
			elseif($item->image)
			{
				$thumb=$item->image;
			}
			elseif($item->thumbnail)
			{
				$thumb=$item->thumbnail;
			}

			$link = $this->rknclass->cleaner->clean($item->link);

			$num++;
			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE url='" . $this->rknclass->db->escape($item->link) . "' LIMIT 1");

			if(intval($this->rknclass->db->result()) < 1)
			{
				if(strlen($item->description) < 1 || trim($item->description) == '')
				{
					$item->description = 'N/A';
				}
				echo "<tr id=\"rows\">
	    <td id=\"title\"><a href=\"$link\" target=\"_blank\">" . (strlen($item->title) > 40 ? substr($item->title, 0, 37) . '...' : $item->title) . "</a></td>
	    <td>" . (strlen($item->description) > 60 ? substr($item->description, 0, 57) . '...' : $item->description) . "</td>
	    " . ($this->rknclass->get['type'] == 'with_preview' ? "<td><a href=\"$item->link\" target=\"_blank\"><img src=\"$thumb\" width=\"{$this->rknclass->settings['thumb_width']}\" height=\"{$this->rknclass->settings['thumb_height']}\"></a></td>" : "<td><a href=\"$thumb\" target=\"_blank\">View</a></td>");
			}

		}

		echo '</table>';
		$this->rknclass->form->new_form('Import Settings');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=add_from_feed&id=' . $this->rknclass->get['id']);

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "cats ORDER BY cat_name ASC");

		$categories='<option value="special::random" SELECTED>[Random]</option><option value=\"---\">---</option>';

		while($row=$this->rknclass->db->fetch_array())
		{
			$categories.="<option value=\"$row[cat_id]\">$row[cat_name]" . ($row['public'] == '0' ? " (Private)" : "") . "</option>";
		}
		$this->rknclass->form->add_input('category', 'dropdown', 'Select Category', 'Please select a category for your plugs from the list', $categories);
		$this->rknclass->form->add_input('schedule', 'input', 'Advanced Scheduling', 'This section allows you to enter how often you want a plug to be added in minutes.<br /><br />If you enter 20, one plug from the list above will be added every 20 minutes. <br /><br /><strong>A value of 0 will add all the plugs at once</strong>', '0');

		if($this->rknclass->settings['queue_time'] > 0)
		{
		    $this->rknclass->form->add_input('obey_queue', 'dropdown', 'Obey Queue System', 'Please select whether or not you want the plugs to be added to the queue.<br /><br /><strong>If set to yes, this will override the option above</strong>', '<option value="0">No</option><option value="1">Yes</option>');
		}

		$this->rknclass->form->add_input('tags', 'input', 'Tags', 'Please enter some tags to be used for the imported plugs.<br /><br /><strong>These should be one word, seperated by a single space</strong>');
		$this->rknclass->form->add_input('ignore_description', 'dropdown', 'Ignore Description', 'Select whether or not you want to ignore plugs in this feed which don\'t have a description', '<option value="1" SELECTED>Yes</option><option value="0">No</option>');
		$this->rknclass->form->add_input('limit', 'input', 'Import Limit', 'What is the Maximum amount of plugs you want added to the queue?' . "<br /><br /><strong>If you attempt to import a large amount of plugs, the script may time-out, resulting in an error message and/or a blank page</strong><br /><br />Server-defined Max Execution Time: <strong>" . ini_get('max_execution_time') . " seconds</strong>", $num);
		$this->rknclass->form->add_input('date_order', 'dropdown', 'Date Order', 'Please select the order in which you want plugs to be imported.<br /><br /><strong>Ascending</strong> will import the plugs from oldest to newest, as they would appear if they had been manually added to your site', '<option value="des">Descending</option><option value="asc" SELECTED>Ascending</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function add_from_feed()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid feed id'));
		}

		$this->rknclass->db->query("SELECT feed_url FROM " . TBLPRE . "feeds WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Feed not found in database'));
		}

		$feed_url = $this->rknclass->db->result();

		$fields = array('category', 'tags', 'limit');

		foreach($fields as $field_name)
		{
			if(empty($this->rknclass->post['' . $field_name . '']))
			{
				exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
			}
		}

		if(!ctype_digit($this->rknclass->post['schedule']))
		{
			$this->rknclass->post['schedule'] = '0';
		}

		if($this->rknclass->post['category'] == 'special::random')
		{
			$site_cats = array();

			$this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats ORDER BY cat_name ASC");
			while($row = $this->rknclass->db->fetch_array())
			{
				$site_cats[] = array('id' => $row['cat_id'], 'name' => $row['cat_name']);
			}

			$cat_count = count($site_cats);

			if($cat_count < 1)
			{
				exit($this->rknclass->global_tpl->admin_error('There must be at least one category on your site'));
			}
		}
		else
		{
			$this->rknclass->db->query("SELECT cat_name FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['category']}' LIMIT 1");

			if(intval($this->rknclass->db->num_rows()) > 0)
			{
				$cat_name = $this->rknclass->db->result();
			}
			else
			{
				exit($this->rknclass->global_tpl->admin_error('Invalid category'));
			}
		}

		$rss = file_get_contents($feed_url);

		$parser = new SimpleXMLElement($rss);

  		$num = 0;

        if($this->rknclass->post['date_order'] == 'asc')
        {
            $array = array();
            foreach($parser->channel->item as $item)
            {
				$array[] = $item;
			}
            $array = array_reverse($array);
            $items = $array;
            unset($array);
        }
        else
        {
			$items = $parser->channel->item;
		}

		foreach($items as $item)
		{
			if($num >= $this->rknclass->post['limit'])
			{
				break;
			}

			if(isset($item->thumb))
			{
				$thumb=$item->thumb;
			}
			elseif($item->enclosure['url'])
			{
				$thumb=$item->enclosure['url'];
			}
			elseif($item->image)
			{
				$thumb=$item->image;
			}
			elseif($item->thumbnail)
			{
				$thumb=$item->thumbnail;
			}

			$num++;

			$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE url='" . $this->rknclass->db->escape($item->link) . "' LIMIT 1");

			if($this->rknclass->db->result() < 1)
			{
				$destination = RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' . $this->rknclass->utils->rand_chars(15) . '.tmp';

				@copy($thumb, $destination);

				@$info = getimagesize($thumb);

				$allowed_ext = array('image/jpeg', 'image/gif', 'image/png');

				/*================================
				Checks to ensure thumbnail is
				an image, and that the dimensions
				are at leat 3 quarters of the admin
				defined image-cropper settings
				==================================*/

				if(in_array($info['mime'], $allowed_ext) === false || $info['0'] < ($this->rknclass->settings['thumb_width'] * 0.75) || $info['1'] < ($this->rknclass->settings['thumb_height'] * 0.75))
				{
					$num -= 1;
					continue;
				}

				switch($info['mime'])
				{
					case 'image/jpeg':
						$ext = '.jpg';
						break;
					case 'image/gif':
						$ext = '.gif';
						break;
					case 'image/png':
						$ext = '.png';
						break;
					default:
						$ext = '.jpg';
				}

				$thumb_name = $this->rknclass->utils->rand_chars(7) . $ext;
				$final = RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' . $thumb_name;

				if($this->rknclass->settings['thumb_server'] == '0')
				{
					try
					{
						if(!@rename($destination, $final))
						{
							throw new Exception('Unable to rename thumbnail');
						}
					}
					catch(Exception $e)
					{
						exit($this->rknclass->global_tpl->admin_error($e->getMessage));
					}
				}
				else
				{
					if(!isset($handle))
					{
						@$handle = ftp_connect($this->rknclass->settings['cluster_settings']['thumb_server_address']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to thumb server'));
						@ftp_login($handle, $this->rknclass->settings['cluster_settings']['thumb_server_username'], $this->rknclass->settings['cluster_settings']['thumb_server_password']) or exit($this->rknclass->global_tpl->admin_error('System unable to connect to thumb server<br />Invalid user / pass'));
					}
					@ftp_put($handle, $thumb_name, $destination, FTP_BINARY) or exit($this->rknclass->global_tpl->admin_error('Unable to FTP upload thumbnail'));
				}

				$url         = trim($this->rknclass->db->escape(strip_tags(stripslashes($item->link))));
				$title       = trim($this->rknclass->db->escape(strip_tags(stripslashes($item->title))));
				$description = trim($this->rknclass->db->escape(strip_tags(stripslashes($item->description))));
				$tags        = $this->rknclass->utils->process_tags($this->rknclass->post['tags']);

				if(empty($title) || empty($url))
				{
					$num -= 1;
					continue;
				}

				if($this->rknclass->post['ignore_description'] == '1')
				{
					if(empty($description))
					{
						$num -= 1;
						continue;
					}
				}

				if(!preg_match('@http://(.*)@', $url))
				{
					$num -= 1;
					continue;
				}

				if(intval($this->rknclass->post['schedule']) === 0)
				{
					$posted = time();
				}
				else
				{
					if(!isset($posted))
					{
						$posted = time();
					}
					else
					{
						$posted += (60 * $this->rknclass->post['schedule']);
					}
				}

				if($this->rknclass->post['obey_queue'] == '1')
				{
				    $posted = $this->rknclass->utils->get_next_queue_ts();
				}

				if($this->rknclass->post['category'] == 'special::random')
				{
					$cat = $site_cats['' . rand(0, ($cat_count - 1)) . ''];
				}
				else
				{
					$cat = array('id' => $this->rknclass->post['category'], 'name' => $cat_name);
				}

				$this->rknclass->db->query("INSERT INTO " . TBLPRE . "plugs SET title='$title', description='$description', tags='$tags', poster='{$this->rknclass->user['username']}', poster_id='{$this->rknclass->user['user_id']}', approved='1', cropped='1', posted='$posted', category='{$cat['name']}', category_id='{$cat['id']}', type='1', thumb='$thumb_name', url='$url'");

				$insert_id = $this->rknclass->db->insert_id();
				$seo_url   = $this->rknclass->db->escape($this->rknclass->utils->make_seo_content_url($insert_id, $title, $cat['name']));

				$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET seo_url='{$seo_url}' WHERE plug_id='{$insert_id}' LIMIT 1");

			}
			else
			{
				$num -= 1;
				continue;
			}
		}

		if(isset($handle))
		{
			@ftp_close($handle);
		}

		if($num > 0)
		{
			$this->rknclass->global_tpl->exec_redirect("Successfully added $num plugs", '?ctr=content&act=view_plugs');
		}
		else
		{
			$this->rknclass->global_tpl->admin_error('Unforunately Predator was unable to find any usable plugs.<br />Ensure the feed is valid and contains all required information');
		}
	}

	public function delete_feed()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid feed id'));
		}

		$this->rknclass->db->query("SELECT count(feed_id) FROM " . TBLPRE . "feeds WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");

		if(intval($this->rknclass->db->result()) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Feed not found in database'));
		}

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "feeds WHERE feed_id='{$this->rknclass->get['id']}' LIMIT 1");
		$this->rknclass->global_tpl->exec_redirect('Successfully removed feed', '?ctr=content&act=manage_feeds');
	}

	final private function get_sponsor_dropdown_list($id = null, $dont_include = null)
	{
		$options='<option value="---">---</option>';

		$result = $this->rknclass->db->query("SELECT sponsor_id,sponsor_name FROM " . TBLPRE . "sponsors ORDER BY sponsor_name ASC");
		while($row = $this->rknclass->db->fetch_array($result))
		{
		    $options .= "<option value=\"invalid\">{$row['sponsor_name']}</option>";
		    $result2  = $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_parent='{$row['sponsor_id']}'" . ($dont_include !== null ? " AND sponsor_site_id != '{$dont_include}'" : '') . " ORDER BY sponsor_site_name ASC");

		    while($row2 = $this->rknclass->db->fetch_array($result2))
		    {
		        $options .= "<option value=\"{$row2['sponsor_site_id']}\"" . ($row2['sponsor_site_id'] == $id ? ' SELECTED' : '') . ">-- {$row2['sponsor_site_name']}</option>";
		    }
		}

		return $options;
	}

	public function manage_sponsors()
	{
		$this->rknclass->page_title='Content Sponsors';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page();
		$this->rknclass->pager->limit=50;

		$this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors");

		$this->rknclass->pager->total=$this->rknclass->db->result();
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Content Sponsors</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Name</th>
    <th scope="col">Description</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';
  		$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors ORDER BY sponsor_name ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

		while($row=$this->rknclass->db->fetch_array($result))
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['sponsor_name']}</td>
    <td>{$row['sponsor_description']}</td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_sponsor&amp;id={$row['sponsor_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_sponsor&amp;id={$row['sponsor_id']}\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_sponsor()
	{
		if(!isset($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->get['id']}'");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		$row = $this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Manage Content Sponsor';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Manage Content Sponsor');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_sponsor&amp;id=' . $this->rknclass->get['id']);

		$this->rknclass->form->add_input('sponsor_url', 'input', 'Sponsor Url', 'Enter your sponsor/content provider\'s website address', $row['sponsor_url']);
		$this->rknclass->form->add_input('sponsor_name', 'input', 'Sponsor Name', 'Please enter a <strong>unique</strong> name for this sponsor', $row['sponsor_name']);
		$this->rknclass->form->add_input('sponsor_description', 'textarea', 'Sponsor Description', 'Please provide a short description for this sponsor. Optional, but could come in useful if you have a lot of sponsors.', $row['sponsor_description']);
		$this->rknclass->form->add_input('sponsor_username', 'input', 'Sponsor Username', 'Its very easy to forget the login information for your various sponsors. You can optionally add your sponsor username here for safe-keeping.', $row['sponsor_username']);
		$this->rknclass->form->add_input('sponsor_password', 'input', 'Sponsor Password', 'Enter your account password for this sponsor.', $row['sponsor_password']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_sponsor()
	{
		if(!isset($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		if($this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->get['id']}' LIMIT 1")) < 1)
		{
			exit($this->rknclass->form->ajax_error('Invalid sponsor id'));
		}

		if(empty($this->rknclass->post['sponsor_url']) || empty($this->rknclass->post['sponsor_name']))
		{
			exit('One or more required fields were left blank');
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors SET sponsor_url='{$this->rknclass->post['sponsor_url']}', sponsor_name='{$this->rknclass->post['sponsor_name']}', sponsor_description='{$this->rknclass->post['sponsor_description']}', sponsor_username='{$this->rknclass->post['sponsor_username']}', sponsor_password='{$this->rknclass->post['sponsor_password']}' WHERE sponsor_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->form->ajax_success('Successfully updated sponsor!');
	}

	public function manage_sponsors_sites()
	{
		$this->rknclass->db->query("SELECT sponsor_id, sponsor_name FROM " . TBLPRE . "sponsors");

		$sponsors = array();
		while($sponsor = $this->rknclass->db->fetch_array())
		{
			$sponsors[$sponsor['sponsor_id']] = $sponsor['sponsor_name'];
		}

		$this->rknclass->page_title='Sponsor Sites';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page();
		$this->rknclass->pager->limit=50;

		$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites");

		$this->rknclass->pager->total=$this->rknclass->db->result();
		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Sponsor Sites</div>

 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Name</th>
    <th scope="col">Website Address</th>
    <th scope="col">Parent</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';
  		$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_sites ORDER BY sponsor_site_name ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

		while($row=$this->rknclass->db->fetch_array($result))
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['sponsor_site_name']}</td>
    <td>{$row['sponsor_site_url']}</td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_sponsor&amp;id={$row['sponsor_site_parent']}\">{$sponsors[$row['sponsor_site_parent']]}</a></td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_sponsor_site&amp;id={$row['sponsor_site_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"index.php?ctr=content&amp;act=delete_sponsor_site&amp;id={$row['sponsor_site_id']}\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=plugs_pending_approval&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_sponsor_site()
	{
		if(!isset($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}'");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site id'));
		}

		$row = $this->rknclass->db->fetch_array();

		$content_providers=NULL;

		$this->rknclass->db->query("SELECT sponsor_name,sponsor_id FROM " . TBLPRE . "sponsors ORDER BY sponsor_name ASC");

		while($row2=$this->rknclass->db->fetch_array())
		{
			$content_providers.="<option value=\"{$row2['sponsor_id']}\"" . ($row2['sponsor_id'] == $row['sponsor_site_parent'] ? " SELECTED" : "") . ">{$row2['sponsor_name']}</option>";
		}

		$this->rknclass->page_title='Manage sponsor site';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Manage Sponsor Site');
		$this->rknclass->form->ajax=false; //Disables ajax on form, since AJAX uploads are not supported due to js security protocols
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_sponsor_site&amp;id=' . $row['sponsor_site_id']);

		$this->rknclass->form->add_input('sponsor_site_url', 'input', 'Sponsor Site Url', 'Enter your sponsor site\'s main url', $row['sponsor_site_url']);
		$this->rknclass->form->add_input('sponsor_site_name', 'input', 'Sponsor Name', 'Please enter a <strong>unique</strong> name for this sponsor site', $row['sponsor_site_name'], $row['sponsor_site_name']);
		$this->rknclass->form->add_input('sponsor_site_parent', 'dropdown', 'Content Provider', 'Please select this sponsor site\'s parent from the list of content providers', $content_providers);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_sponsor_site()
	{
		if($this->rknclass->post['sponsor_site_url'] == '' || $this->rknclass->post['sponsor_site_name'] == '' || $this->rknclass->post['sponsor_site_parent'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		if(!is_numeric($this->rknclass->post['sponsor_site_parent']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor!'));
		}

		if(!isset($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}'");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site id'));
		}

		$row = $this->rknclass->db->fetch_array();

		$count=$this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->post['sponsor_site_parent']}' LIMIT 1"));

		if(intval($count) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor!'));
		}

		if($this->rknclass->post['sponsor_site_url'] !== $row['sponsor_site_url'])
		{
			$this->rknclass->db->query("SELECT count(sponsor_site_url) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_url='{$this->rknclass->post['sponsor_site_url']}' LIMIT 1");
			if(intval($this->rknclass->db->result()) > 0)
			{
				exit($this->rknclass->global_tpl->admin_error('Another sponsor site with this url already exists!'));
			}
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_sites SET sponsor_site_url='{$this->rknclass->post['sponsor_site_url']}', sponsor_site_name='{$this->rknclass->post['sponsor_site_name']}', sponsor_site_parent='{$this->rknclass->post['sponsor_site_parent']}' WHERE sponsor_site_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully updated sponsor site', '?ctr=content&act=manage_sponsors_sites');
	}

	public function auto_feed_settings()
	{
		$this->rknclass->page_title='Automatic Content Importation';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT feed_cron, feed_import_settings FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();

		$fis = @unserialize($row['feed_import_settings']);
		if(!is_array($fis))
		{
			$fis['import_order'] = 'topref';
			$fis['content_order'] = 'asc';
			$fis['who2'] = 'all';
			$fis['cat'] = 'special::random';
			$fis['tags'] = null;
			$fis['ratio_required'] = '1';
		}

		$fio = array('rand' => 'Random', 'topref' => 'Highest All-time Ratio');

		$fis_import_order = null;

		foreach($fio as $select_key => $select_text)
		{
			$fis_import_order .= "<option value=\"$select_key\"" . ($select_key == $fis['import_order'] ? " SELECTED" : "") . ">$select_text</option>";
		}

		$fis_content_order = null;

		$fco = array('asc' => "Old plugs imported first", 'desc' => 'New plugs imported first');

		foreach($fco as $select_key => $select_text)
		{
			$fis_content_order .= "<option value=\"$select_key\"" . ($select_key == $fis['content_order'] ? " SELECTED" : "") . ">$select_text</option>";
		}

		$fis_who2 = null;

		$fw2 = array('all' => "All approved sites", 'flagged' => 'Admin-defined list of feeds');

		foreach($fw2  as $select_key => $select_text)
		{
			$fis_who2 .= "<option value=\"$select_key\"" . ($select_key == $fis['who2'] ? " SELECTED" : "") . ">$select_text</option>";
		}

		$fis_cats = '<option value="special::random">[Random]</option>';

		$this->rknclass->db->query("SELECT cat_id, cat_name FROM " . TBLPRE . "cats ORDER BY cat_name ASC");
		while($row2 = $this->rknclass->db->fetch_array())
		{
			$fis_cats .= "<option value=\"{$row2['cat_id']}\"" . ($row2['cat_id'] == $fis['cat'] ? ' SELECTED' : '') . ">{$row2['cat_name']}</option>";
		}

		$frr = array('1' => "Yes", '0' => 'No');

		foreach($frr  as $select_key => $select_text)
		{
			$fis_ratio_required .= "<option value=\"$select_key\"" . ($select_key == $fis['ratio_required'] ? " SELECTED" : "") . ">$select_text</option>";
		}

		$this->rknclass->form->new_form('Automatic Content Importation');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_auto_feed_settings');
		$this->rknclass->form->add_input('feed_cron', 'input', 'Feed Import Schedule', 'Enter, in <strong>minutes</strong>, how often you want a plug to be imported.<br /><br />A value of 0 will disable the auto feed system', $row['feed_cron']);
		$this->rknclass->form->add_input('fis_import_order', 'dropdown', 'Import Selection Order', 'Please select how you want Predator to choose which feeds to import', $fis_import_order);
		$this->rknclass->form->add_input('fis_content_order', 'dropdown', 'Content Ordering', 'Please select the order in which you want the content to be imported.<br /><br />"New plugs imported first" will import the site\'s latest plug on their content feed each time Predator checks.', $fis_content_order);
		$this->rknclass->form->add_input('fis_who2', 'dropdown', 'Who to import?', 'This option allows you to choose which sites\' rss feeds you\'d like imported.<br /><br />Admin defined feeds can be controlled via "Mark for import" on the feed management page', $fis_who2);
		$this->rknclass->form->add_input('fis_cat', 'dropdown', 'Content Category', 'Please select the category you would feed content to be imported into.<br /><br />[Random] will make Predator pick a category at random for each plug imported via the feeds system', $fis_cats);
		$this->rknclass->form->add_input('fis_tags', 'input', 'Tags', 'Please input some tags you would like to use for imported plugs. <br /><br />Enter <strong>special::guess</strong> if you would like Predator to attempt to "guess" the tags to use', $fis['tags']);
		$this->rknclass->form->add_input('fis_ratio_required', 'dropdown', 'Require Ratio', 'Please select whether or not you want Predator to ignore sites in the feed system if their ratio isn\'t high enough', $fis_ratio_required);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_auto_feed_settings()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }

		if(!ctype_digit((string)$this->rknclass->post['feed_cron']) || $this->rknclass->post['feed_cron'] < 0)
		{
			exit($this->rknclass->form->ajax_error('Invalid feed cron value entered'));
		}

		if($this->rknclass->post['fis_cat'] !== 'special::random')
		{
			$this->rknclass->db->query("SELECT count(cat_id) FROM " . TBLPRE . "cats WHERE cat_id='{$this->rknclass->post['fis_cat']}' LIMIT 1");

			if($this->rknclass->db->result() < 1)
			{
				exit($this->rknclass->form->ajax_error('Invalid category selected!'));
			}
		}

		if(!ctype_digit((string)$this->rknclass->post['fis_ratio_required']))
		{
			exit($this->rknclass->form->ajax_error('Invalid require ratio value entered'));
		}

		$data = array('import_order'  => $this->rknclass->post['fis_import_order'],
		              'content_order' => $this->rknclass->post['fis_content_order'],
					  'who2'          => $this->rknclass->post['fis_who2'],
					  'cat'           => $this->rknclass->post['fis_cat'],
					  'tags'          => $this->rknclass->post['fis_tags'],
					  'ratio_required' => $this->rknclass->post['fis_ratio_required']);

		$data = @serialize($data);

		$this->rknclass->cache->update_settings_and_cache(
		array('feed_cron'            => $this->rknclass->post['feed_cron'],
		      'feed_import_settings' => $data));

		$this->rknclass->form->ajax_success('Successfully updated auto feed settings!');
	}

	public function countries()
	{
		$this->rknclass->page_title='GeoIP Countries';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=100; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT count(country_id) FROM " . TBLPRE . "countries");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">GeoIP Countries</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Country Name</th>
    <th scope="col">Country Code</th>
    <th scope="col">Country Flag</th>
    <th scope="col">Flagged</th>
  </tr>';

  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "countries ORDER BY country_name ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['country_name']}</td>
    <td>{$row['country_code']}</td>
    <td><img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" width=\"30\" height=\"18\"/></td>
    <td id=\"country-$row[country_id]\"><strong>" . ($row['flagged'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('country', '$row[country_id]', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('country', '$row[country_id]', 'yes'); return false;\">No</a>") . "</strong></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=countries&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=countries&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function search_engine()
	{
		if(!isset($this->rknclass->post['search_phrase']))
		{
			$this->rknclass->post['search_phrase'] = null;
		}

		$options = array('sites' => 'Sites', 'users' => 'Users', 'content' => 'Content');

		natsort($options);

		$dropdown = null;

		foreach($options as $opt_value => $opt_name)
		{
			$dropdown .= "<option value=\"$opt_value\">$opt_name</option>";
		}

		$page_limit_opts = array(10,20,50,100,250);

		$dropdown2 = null;

		foreach($page_limit_opts as $value)
		{
			$dropdown2 .= "<option value=\"$value\"" . ($value == '20' ? ' SELECTED' : '') . ">$value</option>";
		}

		$this->rknclass->page_title='Search Engine';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Search Engine');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=perform_search');
		$this->rknclass->form->add_input('search_phrase', 'input', 'Search Phrase', 'Please enter what you are looking for', $this->rknclass->post['search_phrase']);
		$this->rknclass->form->add_input('search_type', 'dropdown', 'Search Type', 'Please select the type of information you are looking to find', $dropdown);
		$this->rknclass->form->add_input('search_pp', 'dropdown', 'Results Per Page', 'Please select the Maximum results you want listed per page', $dropdown2);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function perform_search()
	{
		if(empty($this->rknclass->post['search_phrase']) || empty($this->rknclass->post['search_type']) || empty($this->rknclass->post['search_pp']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		$this->rknclass->cleaner->clean_search_query($this->rknclass->post['search_phrase']);
		$this->rknclass->session->add_key('acp_search_phrase', $this->rknclass->post['search_phrase']);

		switch($this->rknclass->post['search_type'])
		{
			case 'sites':
				$this->search_results_sites();
				break;
			case 'users':
				$this->search_results_users();
				break;
			case 'content':
				$this->search_results_content();
				break;
			default:
				$this->search_results_content();
		}
	}

	public function search_results_sites()
	{
		if(empty($this->rknclass->session->data['acp_search_phrase']))
		{
			exit($this->rknclass->global_tpl->admin_error('No search phrase was entered'));
		}

		$this->rknclass->page_title='Search Results';
		$this->rknclass->load_object('pager');

		if(!isset($this->rknclass->get['per_page']))
		{
			$this->rknclass->pager->limit=$this->rknclass->post['search_pp'];
		}
		else
		{
			$this->rknclass->pager->limit=$this->rknclass->get['per_page'];
		}

		$this->rknclass->pager->set_page();

		$this->rknclass->db->query("SELECT count(site_id) FROM " . TBLPRE . "sites WHERE (url LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' OR name LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%') AND owner>0");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

  		$result = $this->rknclass->db->query("SELECT " . TBLPRE . "sites.*, " . TBLPRE . "users.username FROM " . TBLPRE . "sites LEFT JOIN " . TBLPRE . "users ON " . TBLPRE . "sites.owner = " . TBLPRE . "users.user_id WHERE (" . TBLPRE . "sites.url LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' OR " . TBLPRE . "sites.name LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%') ORDER BY " . TBLPRE . "sites.url ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

  		if($this->rknclass->db->num_rows() < 1)
  		{
			exit($this->rknclass->global_tpl->admin_error('0 results returned for <em>' . $this->rknclass->session->data['acp_search_phrase'] . '</em>'));
		}

  		$this->rknclass->global_tpl->admin_header();
		echo '<div class="page-title">Search Results</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Site Name</th>
    <th scope="col">Site url</th>
    <th scope="col">Owner</th>
    <th scope="col">Edit</th>
  </tr>';

		while($row=$this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->utils->highlight_search_text($row['name'], $this->rknclass->session->data['acp_search_phrase']);
			$this->rknclass->utils->highlight_search_text($row['url'], $this->rknclass->session->data['acp_search_phrase']);
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['name']}</td>
    <td>{$row['url']}</td>
    <td>{$row['username']}</td>
    <td><a href=\"index.php?ctr=management&amp;act=edit_site&amp;id={$row['site_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=search_results_sites&amp;per_page=' . $this->rknclass->pager->limit . '&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=search_results_sites&amp;per_page=' . $this->rknclass->pager->limit . '&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function search_results_content()
	{
		if(empty($this->rknclass->session->data['acp_search_phrase']))
		{
			exit($this->rknclass->global_tpl->admin_error('No search phrase was entered'));
		}

		$this->rknclass->page_title='Search Results';
		$this->rknclass->load_object('pager');

		if(!isset($this->rknclass->get['per_page']))
		{
			$this->rknclass->pager->limit=$this->rknclass->post['search_pp'];
		}
		else
		{
			$this->rknclass->pager->limit=$this->rknclass->get['per_page'];
		}

		$this->rknclass->pager->set_page();

		$this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE (url LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' OR title LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' OR description LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%')");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

  		$result = $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE (url LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' OR title LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' OR description LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%') ORDER BY title ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

  		if($this->rknclass->db->num_rows() < 1)
  		{
			exit($this->rknclass->global_tpl->admin_error('0 results returned for <em>' . $this->rknclass->session->data['acp_search_phrase'] . '</em>'));
		}

  		$this->rknclass->global_tpl->admin_header();
		echo '<div class="page-title">Search Results</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Original Url</th>
    <th scope="col">Poster</th>
    <th scope="col">Edit</th>
  </tr>';

		while($row=$this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->utils->highlight_search_text($row['title'], $this->rknclass->session->data['acp_search_phrase']);
			$this->rknclass->utils->highlight_search_text($row['url'], $this->rknclass->session->data['acp_search_phrase']);

		    switch($row['type'])
		    {
		    	case '1':
		    		$edit = "<td><a href=\"index.php?ctr=content&amp;act=update_plug&amp;id={$row['plug_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>";
		    		$type = 'link';
		    		break;
		    	case '2':
		    		$edit = "<td><a href=\"index.php?ctr=content&amp;act=update_hosted_video&amp;id={$row['plug_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>";
		    		$type = 'video';
		    		$row['url'] = $this->rknclass->settings['site_url'] . "/?ctr=view&amp;id={$row['plug_id']}";
		  			break;
		    	case '3':
		    		$edit = "<td><a href=\"index.php?ctr=content&amp;act=update_embedded_video&amp;id={$row['plug_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>";
		    		$type = 'video';
		    		$row['url'] = $this->rknclass->settings['site_url'] . "/?ctr=view&amp;id={$row['plug_id']}";
		  			break;
		    	case '5':
		    		$edit = "<td><a href=\"index.php?ctr=content&amp;act=edit_blog_entry&amp;id={$row['plug_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>";
					$type = 'link';
					$row['url'] = $this->rknclass->settings['site_url'] . "/?ctr=view&amp;id={$row['plug_id']}";
		  			break;
		  	}

			echo "<tr id=\"rows\">
    <td id=\"title\"><img src=\"images/type-$type.jpg\" id=\"content-icon\" />{$row['title']}</td>
    <td>{$row['url']}</td>
    <td>{$row['poster']}</td>
    $edit
    ";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=search_results_content&amp;per_page=' . $this->rknclass->pager->limit . '&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=search_results_content&amp;per_page=' . $this->rknclass->pager->limit . '&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function search_results_users()
	{
		if(empty($this->rknclass->session->data['acp_search_phrase']))
		{
			exit($this->rknclass->global_tpl->admin_error('No search phrase was entered'));
		}

		$this->rknclass->page_title='Search Results';
		$this->rknclass->load_object('pager');

		if(!isset($this->rknclass->get['per_page']))
		{
			$this->rknclass->pager->limit=$this->rknclass->post['search_pp'];
		}
		else
		{
			$this->rknclass->pager->limit=$this->rknclass->get['per_page'];
		}

		$this->rknclass->pager->set_page();

		$this->rknclass->db->query("SELECT count(user_id) FROM " . TBLPRE . "users WHERE username LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%'");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

  		$result = $this->rknclass->db->query("SELECT user_id,username,joined,total_plugs FROM " . TBLPRE . "users WHERE username LIKE '%{$this->rknclass->session->data['acp_search_phrase']}%' ORDER BY username ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

  		if($this->rknclass->db->num_rows() < 1)
  		{
			exit($this->rknclass->global_tpl->admin_error('0 results returned for <em>' . $this->rknclass->session->data['acp_search_phrase'] . '</em>'));
		}

  		$this->rknclass->global_tpl->admin_header();
		echo '<div class="page-title">Search Results</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Username</th>
    <th scope="col">Joined</th>
    <th scope="col">Plugs Submitted</th>
    <th scope="col">Edit</th>
  </tr>';

		while($row=$this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->utils->highlight_search_text($row['username'], $this->rknclass->session->data['acp_search_phrase']);
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['username']}</td>
    <td>" . $this->rknclass->utils->make_date($row['joined']) . "</td>
    <td>{$row['total_plugs']}</td>
    <td><a href=\"index.php?ctr=management&amp;act=edit_user&amp;id={$row['user_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    ";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=search_results_users&amp;per_page=' . $this->rknclass->pager->limit . '&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=search_results_users&amp;per_page=' . $this->rknclass->pager->limit . '&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function flagged_countries()
	{
		$this->rknclass->page_title='Flagged GeoIP Countries';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=100; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT count(country_id) FROM " . TBLPRE . "countries WHERE flagged='1'");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		if($this->rknclass->pager->total < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('You have not set any countries as <em>Flagged</em> yet'));
		}

		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Flagged GeoIP Countries</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Country Name</th>
    <th scope="col">Country Code</th>
    <th scope="col">Country Flag</th>
    <th scope="col">Flagged</th>
  </tr>';

  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "countries WHERE flagged='1' ORDER BY country_name ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['country_name']}</td>
    <td>{$row['country_code']}</td>
    <td><img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" width=\"30\" height=\"18\"/></td>
    <td id=\"country-$row[country_id]\"><strong>" . ($row['flagged'] == '1' ? "<a href=\"#\" class=\"yes-ajax\" onclick=\"ajax_update('country', '$row[country_id]', 'no'); return false;\">Yes</a>" : "<a href=\"#\" class=\"no-ajax\" onclick=\"ajax_update('country', '$row[country_id]', 'yes'); return false;\">No</a>") . "</strong></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=flagged_countries&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=flagged_countries&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function manage_sponsor_ads()
	{
		$this->rknclass->page_title='Manage Sponsor Ads';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT count(ad_id) FROM " . TBLPRE . "sponsors_ads");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();

		echo '
        <div class="page-title">Manage Sponsor Ads</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Advertisement Title</th>
    <th scope="col">Clicks</th>
    <th scope="col">Type</th>
    <th scope="col">Flagged</th>
    <th scope="col">View</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';

  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_ads ORDER BY ad_title ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array())
		{
			if($row['ad_type'] == 'banner')
			{
				$data = unserialize($row['ad_data']);
				$view = "<a href=\"{$this->rknclass->settings['site_url']}/banner_ads/{$data['name']}\" onclick=\"window.open ('{$this->rknclass->settings['site_url']}/banner_ads/{$data['name']}',
'Predator :: Banner Ad','menubar=0,resizable=0,width={$data['width']},height={$data['height']}'); return false;\">View Banner</a>";
			}
			else
			{
				$view = 'N/A';
			}
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['ad_title']}</td>
    <td>" . ($row['ad_type'] == 'banner' ? $row['ad_clicks'] : 'N/A') . "</td>
    <td>{$row['ad_type']}</td>
    <td><strong>" . ($row['ad_flagged'] == '1' ? "<strong><font color=\"green\">Yes</font></strong>" : "<strong><font color=\"red\">No</font></strong>") . "</strong></td>
    <td>$view</td>
	<td><a href=\"index.php?ctr=content&amp;act=edit_sponsor_ad&amp;id={$row['ad_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
	<td><a href=\"index.php?ctr=content&amp;act=delete_sponsor_ad&amp;id={$row['ad_id']}\" onclick=\"return confirm('Are you sure you want to permanently delete this advertisement?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=manage_sponsor_ads&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=manage_sponsor_ads&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function delete_sponsor_ad()
	{
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement id!'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_ads WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement id!'));
		}

		$row = $this->rknclass->db->fetch_array();

		if($row['ad_type'] == 'banner')
		{
			$data = @unserialize($row['ad_data']);

			$location = RKN__fullpath . 'banner_ads/' . $data['name'];

			@unlink($location);

			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sponsors_ads WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

			$this->rknclass->global_tpl->exec_redirect('Successfully removed banner advertisement!', '?ctr=content&act=manage_sponsor_ads');
		}
		else
		{
			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sponsors_ads WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");
			$this->rknclass->global_tpl->exec_redirect('Successfully removed HTML advertisement!', '?ctr=content&act=manage_sponsor_ads');
		}
	}

	public function new_banner_link()
	{
		$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites");

		if($this->rknclass->db->result() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('You have not added any sponsor child sites yet'));
		}

		$this->rknclass->page_title='New banner link';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add new banner link');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_new_banner_link');

		$this->rknclass->form->add_input('banner_link_title', 'input', 'Banner Link Title', 'Please enter a <strong>unique</strong> title for the banner link, so it can be identified in the admin cp');
		$this->rknclass->form->add_input('banner_link_url', 'input', 'Banner Link', 'Please enter the full address to this banner link', 'http://');
		$this->rknclass->form->add_input('banner_link_flagged', 'dropdown', 'Banner Link Flagged', 'Choose whether or not you want this banner link to appear only to users from flagged countries', '<option value="0" SELECTED>No</option><option value="1">Yes</option>');
		$this->rknclass->form->add_input('sponsor_site_id', 'dropdown', 'Sponsor Site', 'Please select which sponsor child-site you\'d like this banner link to be associated with', $this->get_sponsor_dropdown_list());
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_new_banner_link()
	{
		if(empty($this->rknclass->post['banner_link_title']) || empty($this->rknclass->post['banner_link_url']) ||!isset($this->rknclass->post['banner_link_flagged']))
		{
			exit($this->rknclass->form->ajax_error('One or more fields where left blank!'));
		}

		if(!ctype_digit((string)$this->rknclass->post['sponsor_site_id']))
		{
			exit($this->rknclass->form->ajax_error('Invalid sponsor child site specified'));
		}

		if($this->rknclass->post['banner_link_flagged'] !== '0' AND $this->rknclass->post['banner_link_flagged'] !== '1')
		{
			exit($this->rknclass->form->ajax_error('One or more fields where left blank!'));
		}

		if(strpos($this->rknclass->post['banner_link_url'], '&amp;') !== false)
		{
		    $this->rknclass->post['banner_link_url'] = str_replace('&amp;', '&', $this->rknclass->post['banner_link_url']);
		}

		$this->rknclass->post['banner_link_title'] = trim($this->rknclass->post['banner_link_title']);

		$this->rknclass->db->query("SELECT count(banner_link_id) FROM " . TBLPRE . "sponsors_banner_links WHERE banner_link_title='{$this->rknclass->post['banner_link_title']}' LIMIT 1");

		if($this->rknclass->db->result() > 0)
		{
			exit($this->rknclass->form->ajax_error('Another banner link with this name already exists!'));
		}

		$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' LIMIT 1");

		if((int)$this->rknclass->db->result() !== 1)
		{
			exit($this->rknclass->form->ajax_error('Invalid sponsor child site specified'));
		}
		else
		{
			$this->rknclass->db->query("INSERT INTO " . TBLPRE . "sponsors_banner_links SET banner_link_title='{$this->rknclass->post['banner_link_title']}', banner_link_url='{$this->rknclass->post['banner_link_url']}', banner_link_flagged='{$this->rknclass->post['banner_link_flagged']}', sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}'");

			$this->rknclass->form->ajax_success('Successfully added new banner link!');
		}
	}

	public function manage_banner_links()
	{
		$this->rknclass->page_title='Manage Banner Links';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page();
		$this->rknclass->pager->limit=150;

		$this->rknclass->db->query("SELECT count(banner_link_id) FROM " . TBLPRE . "sponsors_banner_links");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();

		echo '
        <div class="page-title">Manage Banner Links</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Link Name</th>
    <th scope="col">View</th>
    <th scope="col">Flagged</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';

  		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_banner_links ORDER BY banner_link_title ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['banner_link_title']}</td>
    <td><a href=\"{$row['banner_link_url']}\" target=\"_blank\">{$row['banner_link_url']}</a></td>
    <td>" . ($row['banner_link_flagged'] == '1' ? '<strong><font color="green">Yes</font></strong>' : '<strong><font color="red">No</font></strong>') . "</td>
	<td><a href=\"index.php?ctr=content&amp;act=edit_banner_link&amp;id={$row['banner_link_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
	<td><a href=\"index.php?ctr=content&amp;act=delete_banner_link&amp;id={$row['banner_link_id']}\" onclick=\"return confirm('Are you sure you want to permanently delete this banner link?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=flagged_countries&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=flagged_countries&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}


	public function edit_banner_link()
	{

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_banner_links WHERE banner_link_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid banner link id specified!'));
		}

		$row = $this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Manage banner link';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Manage banner link');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_banner_link&amp;id=' . $this->rknclass->get['id']);

		$flagged = null;
		$possible = array('No' => '0', 'Yes' => '1');
		foreach($possible as $word => $value)
		{
			$flagged .= "<option value=\"$value\"" . ($row['banner_link_flagged'] == $value ? ' SELECTED' : '') . ">$word</option>";
		}
		$this->rknclass->form->add_input('banner_link_title', 'input', 'Banner Link Title', 'Please enter a <strong>unique</strong> title for the banner link, so it can be identified in the admin cp', $row['banner_link_title']);
		$this->rknclass->form->add_input('banner_link_url', 'input', 'Banner Link', 'Please enter the full address to this banner link', $row['banner_link_url']);
		$this->rknclass->form->add_input('banner_link_flagged', 'dropdown', 'Banner Link Flagged', 'Choose whether or not you want this banner link to appear only to users from flagged countries', $flagged);
		$this->rknclass->form->add_input('sponsor_site_id', 'dropdown', 'Sponsor Site', 'Please select which sponsor child-site you\'d like this banner link to be associated with', $this->get_sponsor_dropdown_list($row['sponsor_site_id']));
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_banner_link()
	{
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->form->ajax_error('No banner id was specified'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_banner_links WHERE banner_link_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->form->ajax_error('Invalid banner link id specified!'));
		}

		$row = $this->rknclass->db->fetch_array();

		if(empty($this->rknclass->post['banner_link_title']) || empty($this->rknclass->post['banner_link_url']) ||!isset($this->rknclass->post['banner_link_flagged']))
		{
			exit($this->rknclass->form->ajax_error('One or more fields where left blank!'));
		}

		if(!ctype_digit((string)$this->rknclass->post['sponsor_site_id']))
		{
			exit($this->rknclass->form->ajax_error('Invalid sponsor child site specified'));
		}

		if($this->rknclass->post['banner_link_flagged'] !== '0' AND $this->rknclass->post['banner_link_flagged'] !== '1')
		{
			exit($this->rknclass->form->ajax_error('One or more fields where left blank!'));
		}

		if(strpos($this->rknclass->post['banner_link_url'], '&amp;') !== false)
		{
		    $this->rknclass->post['banner_link_url'] = str_replace('&amp;', '&', $this->rknclass->post['banner_link_url']);
		}

		$this->rknclass->post['banner_link_title'] = trim($this->rknclass->post['banner_link_title']);

		if($this->rknclass->post['banner_link_title'] !== $row['banner_link_title'])
		{

			$this->rknclass->db->query("SELECT count(banner_link_id) FROM " . TBLPRE . "sponsors_banner_links WHERE banner_link_title='{$this->rknclass->post['banner_link_title']}' LIMIT 1");

			if($this->rknclass->db->result() > 0)
			{
				exit($this->rknclass->form->ajax_error('Another banner link with this name already exists!'));
			}

		}

		$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' LIMIT 1");

		if((int)$this->rknclass->db->result() !== 1)
		{
			exit($this->rknclass->form->ajax_error('Invalid sponsor child site specified'));
		}
		else
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_banner_links SET banner_link_title='{$this->rknclass->post['banner_link_title']}', banner_link_url='{$this->rknclass->post['banner_link_url']}', banner_link_flagged='{$this->rknclass->post['banner_link_flagged']}', sponsor_site_id='{$this->rknclass->post['sponsor_site_id']}' WHERE banner_link_id='{$this->rknclass->get['id']}' LIMIT 1");

			$this->rknclass->form->ajax_success('Successfully updated banner link!');
		}
	}

	public function delete_banner_link()
	{
		$this->rknclass->db->query("SELECT count(banner_link_id) FROM " . TBLPRE . "sponsors_banner_links WHERE banner_link_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->result() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid banner link id specified!'));
		}

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sponsors_banner_links WHERE banner_link_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully removed banner link', '?ctr=content&act=manage_banner_links');
	}

	public function delete_sponsor()
	{
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor id'));
		}

		$this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('The sponsor could not be found in the database'));
		}

		$this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_parent='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->result() < 1)
		{
			$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->get['id']}' LIMIT 1");

			exit($this->rknclass->global_tpl->exec_redirect('Successfully removed content provider', '?ctr=content&amp;act=manage_sponsors'));
		}

		$this->rknclass->db->query("SELECT sponsor_name,sponsor_id FROM " . TBLPRE . "sponsors WHERE sponsor_id!='{$this->rknclass->get['id']}'");
		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('There must be at least two sponsors, to allow you to<br /> move the data from this sponsors into another'));
		}

		$options = null;

		while($row = $this->rknclass->db->fetch_array())
		{
			$options .= "<option value=\"{$row['sponsor_id']}\">{$row['sponsor_name']}</a>";
		}

		$this->rknclass->page_title='Remove Content Sponsor';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Remove Content Sponsor');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=remove_content_sponsor&amp;id=' . $this->rknclass->get['id']);

		$this->rknclass->form->add_input('move_to', 'dropdown', 'Move Data To', 'Please select the sponsor you would like all advertisements, child sites and banner links moved to', $options);
		$this->rknclass->form->add_input('user_password', 'password', 'Password', 'Please enter your password to confirm this action.<br /><br /><strong>You are about to delete an entire content sponsor!<br /><font color="red">This action cannot be undone</font></strong>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function remove_content_sponsor()
	{
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid content sponsor id'));
		}

		$this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('The sponsor could not be found in the database'));
		}

		$hash = $this->rknclass->utils->pass_hash($this->rknclass->post['user_password'], $this->rknclass->user['salt']);

		if($hash !== $this->rknclass->user['password'])
		{
			exit($this->rknclass->global_tpl->admin_error('Incorrect password entered'));
		}

		$this->rknclass->db->query("SELECT count(sponsor_id) FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->post['move_to']}' LIMIT 1");

		if($this->rknclass->db->result() < 1 || $this->rknclass->post['move_to'] == $this->rknclass->get['id'])
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid move-to sponsor id'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_sites SET sponsor_site_parent='{$this->rknclass->post['move_to']}' WHERE sponsor_site_parent='{$this->rknclass->get['id']}'");

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sponsors WHERE sponsor_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully removed content provider', '?ctr=content&amp;act=manage_sponsors');
	}

	public function tags()
	{
		$this->rknclass->page_title='Content Tags';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=100; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT count(tag_id) FROM " . TBLPRE . "tags");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();

		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Content Tags</div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Tag Word</th>
    <th scope="col">Tag Views</th>
    <th scope="col">Original Poster</th>
    <th scope="col">Date added</th>
    <th scope="col">Edit</th>
    <th scope="col">Delete</th>
  </tr>';

  		$this->rknclass->db->query("SELECT " . TBLPRE . "tags.*, " . TBLPRE . "users.username FROM " . TBLPRE . "tags LEFT JOIN " . TBLPRE . "users ON " . TBLPRE . "users.user_id = " . TBLPRE . "tags.user_id ORDER BY " . TBLPRE . "tags.tag_views DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);

		while($row=$this->rknclass->db->fetch_array())
		{
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['tag_word']}</td>
    <td>{$row['tag_views']}</td>
    <td><a href=\"index.php?ctr=management&amp;act=edit_user&amp;id={$row['user_id']}\">{$row['username']}</a></td>
    <td>" . $this->rknclass->utils->make_date($row['added']) . "</td>
    <td><a href=\"index.php?ctr=content&amp;act=edit_tag&amp;id={$row['tag_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
	<td><a href=\"index.php?ctr=content&amp;act=delete_tag&amp;id={$row['tag_id']}\" onclick=\"return confirm('Are you sure you want to permanently delete this tag?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}

		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=tags&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=tags&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function edit_tag()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid tag id!'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "tags WHERE tag_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Tag not found!'));
		}

		$row = $this->rknclass->db->fetch_array();

		$this->rknclass->page_title='Edit tag';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit tag');
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_tag&amp;id=' . $row['tag_id']);
		$this->rknclass->form->add_input('tag_word', 'input', 'Tag Word', 'Enter the full tag. This should not contain any spaces, and should contain only alpha-numeric characters', $row['tag_word']);
		$this->rknclass->form->add_input('tag_views', 'input', 'Tag Views', 'Please enter the amount of views this tag has received', $row['tag_views']);

		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_tag()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit($this->rknclass->form->ajax_error('Invalid tag id!'));
		}

		$this->rknclass->db->query("SELECT COUNT(tag_id) FROM " . TBLPRE . "tags WHERE tag_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->form->ajax_error('Invalid tag id!'));
		}

		if(!isset($this->rknclass->post['tag_word']) || empty($this->rknclass->post['tag_word']) || !isset($this->rknclass->post['tag_views']) || !ctype_digit((string)$this->rknclass->post['tag_views']))
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "tags SET tag_word='{$this->rknclass->post['tag_word']}', tag_views='{$this->rknclass->post['tag_views']}' WHERE tag_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->form->ajax_success('Successfully updated tag!');
	}

	public function delete_tag()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid tag id!'));
		}

		$this->rknclass->db->query("SELECT COUNT(tag_id) FROM " . TBLPRE . "tags WHERE tag_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid tag id!'));
		}

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "tags WHERE tag_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully removed tag!', '?ctr=content&act=tags');
		exit;
	}

	public function edit_sponsor_ad()
	{

		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement id!'));
		}

		$ad_types=array('html', 'banner');

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_ads WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Sponsor advertisement not found!'));
		}

		$row  = $this->rknclass->db->fetch_array();
		$type = $row['ad_type'];

		if($type === 'html')
		{
			define('TINYMCE', true);
		}
		$this->rknclass->page_title='Edit advertisement';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit advertisement');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=update_' . $type . '_ad&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('ad_title', 'input', 'Advertisement Title', 'Please enter a name for your advertisement', $row['ad_title']);
		if($type === 'banner')
		{
			$this->rknclass->form->add_input('ad_banner', 'image', 'Advertisement Banner', 'Please upload a banner for this advertisement. Please ensure banners are in either jpg/jpeg, gif or png format<br /><br /><strong>Leave blank if you do not wish to change the image</str');
		}
		else
		{
			$this->rknclass->form->add_input('ad_html', 'tinymce', 'Advertisement HTML', 'Please enter the html for your advertisement', $row['ad_data']);
		}
		$this->rknclass->form->add_input('ad_flagged', 'dropdown', 'Advertisement Flagged?', 'Please select whether or not you want this advertisement to be flagged. <br /><br />Flagged advertisements will only appear to users from <a href="index.php?ctr=content&amp;act=flagged_countries" target="_blank">Flagged countries</a>', '<option value="0">No</option><option value="1"' . ($row['ad_flagged'] == '1' ? ' SELECTED' : '') . '>Yes</option>');
		$this->rknclass->form->add_input('ad_sponsor_site', 'dropdown', 'Advertisement Sponsor Site', 'Please select the sponsor site you wish to assign this advertisement to', $this->get_sponsor_dropdown_list($row['ad_sponsor_site_id']));
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function update_banner_ad()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(!is_numeric($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_ads WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit('Invalid advertisement id!');
		}

		$row = $this->rknclass->db->fetch_array();

		$count = $this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['ad_sponsor_site']}' LIMIT 1"));

		if(intval($count) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		if(empty($this->rknclass->post['ad_title']))
		{
			exit($this->rknclass->global_tpl->admin_error('Please enter a valid title for the advertisement'));
		}

		$org_data = @unserialize($row['ad_data']);

		if(is_uploaded_file($_FILES['ad_banner']['tmp_name']))
		{
			$allowed_types = array('image/jpeg', 'image/gif', 'image/png');

			$old_path = RKN__fullpath . 'banner_ads/' . $org_data['name'];

			$info=@getimagesize($_FILES['ad_banner']['tmp_name']);

			if(in_array($info['mime'], $allowed_types) === false)
			{
				exit($this->rknclass->global_tpl->admin_error('Banner images must be either either jpg/jpeg, gif or png!'));
			}

			$new_name=$this->rknclass->utils->rand_chars(5);

			switch($info['mime'])
			{
				case 'image/jpeg':
					$new_name.='.jpg';
					break;

				case 'image/gif':
					$new_name.='.gif';
					break;

				case 'image/png':
					$new_name.='.png';
					break;

				default:
					$new_name.='.jpg';
			}

			@move_uploaded_file($_FILES['ad_banner']['tmp_name'], RKN__fullpath . 'banner_ads/' . $new_name) or exit($this->rknclass->global_tpl->admin_error('Unable to save uploaded banner ad. <br />Please check CHMOD permissions on the banner_ads folder (should be set to 0777)'));

			$image_data=array('width'  => $info['0'],
			                  'height' => $info['1'],
							  'mime'   => $info['mime'],
							  'name'   => $new_name);
			@unlink($old_path);

			$data = @serialize($image_data);
		}
		else
		{
			$data = $row['ad_data'];
		}
		if($this->rknclass->post['ad_flagged'] == '1')
		{
			$flagged = '1';
		}
		else
		{
			$flagged = '0';
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_ads SET ad_sponsor_site_id='{$this->rknclass->post['ad_sponsor_site']}', ad_title='{$this->rknclass->post['ad_title']}', ad_type='banner', ad_data='{$data}', ad_flagged='$flagged' WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully updated <strong>banner ad</strong>', '?ctr=content&act=manage_sponsor_ads');
	}

	public function update_html_ad()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }

		if(!is_numeric($this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid advertisement id'));
		}

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sponsors_ads WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit('Invalid advertisement id!');
		}

		$row = $this->rknclass->db->fetch_array();

		$count = $this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['ad_sponsor_site']}' LIMIT 1"));

		if(intval($count) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor id'));
		}

		if(empty($this->rknclass->post['ad_title']) || empty($_POST['ad_html']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		$html=$this->rknclass->db->escape($_POST['ad_html']);

		if($this->rknclass->post['ad_flagged'] == '1')
		{
			$flagged = '1';
		}
		else
		{
			$flagged = '0';
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_ads SET ad_sponsor_site_id='{$this->rknclass->post['ad_sponsor_site']}', ad_title='{$this->rknclass->post['ad_title']}', ad_type='html', ad_data='" . $html . "', ad_flagged='$flagged' WHERE ad_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully updated <strong>html ad</strong>', '?ctr=content&act=manage_sponsor_ads');
	}

	public function delete_sponsor_site()
	{
		if(empty($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site id!'));
		}

		$this->rknclass->db->query("SELECT count(*) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id!='{$this->rknclass->get['id']}'");

		if($this->rknclass->db->result() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('There must be at least one other sponsor site<br />to move this site\'s data into!'));
		}

		$this->rknclass->db->query("SELECT count(*) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->result() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Sponsor site not found!'));
		}

		$this->rknclass->page_title='Delete Content Sponsor Site';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Delete Content Sponsor Site');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=content&amp;act=process_sponsor_site_deletion&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('move_to', 'dropdown', 'Move Data To?', 'Please select the sponsor site you\'d like to move this site\'s data to.', $this->get_sponsor_dropdown_list(null, $this->rknclass->get['id']));
		$this->rknclass->form->add_input('user_password', 'password', 'Your Password', 'Please enter your password to confirm this action.<br /><br /><font color="red"><strong>This action <u>cannot</u> be undone!</strong></font>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function process_sponsor_site_deletion()
	{
		if(empty($this->rknclass->post['move_to']) || empty($this->rknclass->post['user_password']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}

		$password = $this->rknclass->utils->pass_hash($this->rknclass->post['user_password'], $this->rknclass->user['salt']);

		if($password !== $this->rknclass->user['password'])
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid password supplied!'));
		}

		$this->rknclass->db->query("SELECT sponsor_site_parent FROM " .TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}' LIMIT 1");

		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site specified!'));
		}

		$sponsor_id = $this->rknclass->db->result();

		$this->rknclass->db->query("SELECT COUNT(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->post['move_to']}' LIMIT 1");

		if($this->rknclass->db->result() < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site specified!'));
		}

		if($this->rknclass->post['move_to'] == $this->rknclass->get['id'])
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid sponsor site specified!'));
		}

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_ads SET ad_sponsor_site_id='{$this->rknclass->post['move_to']}' WHERE ad_sponsor_site_id='{$this->rknclass->get['id']}'");
		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors_banner_links SET sponsor_site_id='{$this->rknclass->post['move_to']}' WHERE sponsor_site_id='{$this->rknclass->get['id']}'");

		$this->rknclass->db->query("DELETE FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_id='{$this->rknclass->get['id']}' LIMIT 1");

		$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors SET sponsor_site_count=sponsor_site_count-1 WHERE sponsor_id='$sponsor_id' LIMIT 1");

		$this->rknclass->global_tpl->exec_redirect('Successfully removed content sponsor site!', '?ctr=content&act=manage_sponsors_sites');
	}

	public function conversion_queue()
	{
		$this->rknclass->page_title = 'FFMPEG Video Conversion Queue';
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=100; //TODO: Add option in ACP

		$this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "ffmpeg_queue");

		$this->rknclass->pager->total=$this->rknclass->db->result();

		$this->pager_data=$this->rknclass->pager->paging_data();
		$this->rknclass->global_tpl->admin_header();
		echo '<div class="page-title">FFMPEG Video Conversion Queue <a href="#" onclick="var runq = document.createElement(\'script\'); runq.src=\'' . $this->rknclass->settings['site_url'] . '/?ctr=video_cron&cron_key=' . sha1($this->rknclass->settings['license_key']) . '\'; document.body.appendChild(runq); alert(\'The queue has been started.\'); return false;"><img src="images/start_now.png" alt="Start Now"></a></div>
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Video Id</th>
    <th scope="col">Grab Thumb</th>
    <th scope="col">Conversion Started</th>
    <th scope="col">Time Elapsed</th>
    <th scope="col">Conversion Finished</th>
    <th scope="col">Failed</th>
    <th scope="col">Delete</th>
  </tr>';

		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "ffmpeg_queue ORDER BY queue_id DESC LIMIT {$this->pager_data['offset']},{$this->pager_data['limit']}");
		while($row=$this->rknclass->db->fetch_array())
		{

		    if($row['time_started'] > 0 AND $row['time_end'] == 0)
		    {
		        $elapsed = gmdate('H:i:s', (time() - $row['time_started']));
		    }
		    elseif($row['time_end'] > 0)
		    {
		        $elapsed = gmdate('H:i:s', ($row['time_end'] - $row['time_started']));
		    }
		    else
		    {
		        $elapsed = '00:00:00';
		    }

		    if($row['thumb'] == 1)
		    {
		        $thumb = 'yes';
		    }
		    else
		    {
		        $thumb = 'no';
		    }

			if($row['time_started'] > 0)
		    {
		        $started = 'yes';
		    }
		    else
		    {
		        $started = 'no';
		    }

		    if($row['time_end'] > 0)
		    {
		        $finished = 'yes';
		    }
		    else
		    {
		        $finished = 'no';
		    }

			if($row['failed'] == 1)
		    {
		        $failed = 'yes';
		    }
		    else
		    {
		        $failed = 'no';
		    }

		    $color['yes'] = "#136f01";
		    $color['no']  = "#e32c00";
			echo "<tr id=\"rows\">
    <td id=\"title\">{$row['plug_id']}</td>
    <td><strong><font color=\"{$color[$thumb]}\">" . ucfirst($thumb) . "</strong></td>
    <td><strong><font color=\"{$color[$started]}\">" . ucfirst($started) . "</strong></td>
    <td>{$elapsed}</td>
    <td><strong><font color=\"{$color[$finished]}\">" . ucfirst($finished) . "</strong></td>
    <td><strong><font color=\"{$color[$failed]}\">" . ucfirst($failed) . "</strong></td>
	<td><a href=\"index.php?ctr=content&amp;act=delete_ffmpeg_queue_entry&amp;id={$row['queue_id']}\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}

		echo '</table>';
		echo '<div id="pagination">';

		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=conversion_queue&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=content&amp;act=conversion_queue&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function delete_ffmpeg_queue_entry()
	{
	    if(empty($this->rknclass->get['id']) || !ctype_digit($this->rknclass->get['id']))
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid queue entry!'));
	    }

	    $this->rknclass->db->query("SELECT time_end FROM " . TBLPRE . "ffmpeg_queue WHERE queue_id='{$this->rknclass->get['id']}'");
	    if($this->rknclass->db->num_rows() < 1)
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid queue entry!'));
	    }

	    if($this->rknclass->db->result() == 0)
	    {
	        exit($this->rknclass->global_tpl->admin_error('Video has not finished converting!'));
	    }

	    $this->rknclass->db->query("DELETE FROM " . TBLPRE . "ffmpeg_queue WHERE queue_id='{$this->rknclass->get['id']}' LIMIT 1");
	    $this->rknclass->global_tpl->exec_redirect('Successfully removed log entry', '?ctr=content&act=conversion_queue');
	}

	private function order_by($field_name, $text = false)
	{
	    $order = 'asc';
	    if($text === false)
	    {
	        $text = ucfirst($field_name);
	    }

	    $url = $this->rknclass->utils->page_url(false, false);

	    if(($pos = strpos($url, '?')) !== false)
	    {
	        $query_string = substr($url, ($pos + 1));
	        parse_str($query_string, $get);

	        if(isset($get['order_by']))
	        {
	            $segments = explode(',', $get['order_by']);
	            if($segments[1] == 'asc')
	            {
	                $order = 'desc';
	            }
	            else
	            {
	                $order = 'asc';
	            }
	            $url = str_replace('order_by=' . $segments[0] . ',' . $segments[1], 'order_by=' . $field_name . ',' . $order, $url);
	        }
	        else
	        {
	            $url .= '&order_by=' . $field_name . ',' . $order;
	        }
	    }
	    else
	    {
	        $url .= '?order_by=' . $field_name . ',' . $order;
	    }

	    $html = "<a href=\"$url\">$text</a>";

	    return $html;
	}

	private function fetch_order(&$order, &$order_url)
	{
	    if(isset($this->rknclass->get['order_by']) AND !empty($this->rknclass->get['order_by']))
		{
		    $segments = explode(',', $this->rknclass->get['order_by']);
		    if(count($segments) == 2)
		    {
		        $field = $segments[0];
		        $order = strtoupper($segments[1]);

		        if(preg_match('/([^a-z0-9|_|-]+)/', $field) || in_array($order, array('ASC', 'DESC'), true) === false)
		        {
		            exit($this->rknclass->global_tpl->admin_error('Invalid field ordering data!'));
		        }

		        $this->rknclass->db->query("SHOW COLUMNS FROM " . TBLPRE . "plugs");
		        $exists = false;
		        while($row = $this->rknclass->db->fetch_array())
		        {
		            if($row['Field'] == $field)
		            {
		                $exists = true;
		                break;
		            }
		        }

		        if($exists === false)
		        {
		            exit($this->rknclass->global_tpl->admin_error('Invalid field ordering data!'));
		        }

		        $order = strtolower($order);
		        $order_url = "&amp;order_by={$field},{$order}";
		        $order = "{$field} {$order}";
		    }
		}
	}

	public function all_chosen() {
		$this->rknclass->page_title='External Plugs';
		$this->rknclass->global_tpl->admin_header();
		$limit = 96;

		$external_reorder = file_exists('all_chosen.php');

		if($external_reorder) {
			include 'all_chosen.php';
		} else {
			$this->rknclass->db->query("SELECT count(*) as total FROM " . TBLPRE . "plugs WHERE chosen = '1' and approved = '1' ORDER BY posted DESC");
			$total = $this->rknclass->db->fetch_array();
			$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE chosen = '1' and approved = '1' ORDER BY posted DESC LIMIT $limit OFFSET " . ($_GET['page'] ? (int) $_GET['page'] * $limit : 0));
			echo '<ul id="plugs-by-six" class="normal">';
			while($row=$this->rknclass->db->fetch_array()) {
				echo '<li id="plug-' . $row['plug_id'] . '"><a href="/admin/index.php?ctr=content&act=update_plug&id=' . $row['plug_id'] .'&return_url=?ctr=content[and]act=view_plugs" onclick="window.open(this.href+\'&popup=true\', \'popupeditor\', \'width=750,height=600,resizable=1,scrollbars=yes\'); return false;"><img src="../thumbs/' . $row['thumb'] . '"></a>' . $row['title'] . '</li>';
			}
			echo '</ul>';
			if($total['total'] > $limit) {
				echo '<div id="pagination">';
				if((int) $_GET['page'] != 0) {
					echo '<a href="?ctr=content&amp;act=all_chosen&amp;page=' . ((int) $_GET['page']-1) . '" id="previous"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
				}
				if((int) $_GET['page'] * $limit + $limit < $total['total']) {
					echo '<a href="?ctr=content&amp;act=all_chosen&amp;page=' . ((int) $_GET['page']+1) . '" id="next"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
				}
				echo '</div>';
			}
		}
		$this->rknclass->global_tpl->admin_footer();
	}
}

?>