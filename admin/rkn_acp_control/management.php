<?php
define('RKN__admintab', 'management');
class management extends rkn_render
{
	//BEGIN INIT FUNCTION	
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
	//END INIT FUNCTION
	
	public function idx()
	{
		$this->core_settings();
	}
	
	public function core_settings()
	{
		$listing_types = unserialize($this->rknclass->settings['listing_types']);
		
		if(!is_array($listing_types))
		{
			$listing_types = array('');
		}
		
		$this->rknclass->page_title='Manage Core Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Manage Core System');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=core_settings');
		$this->rknclass->form->add_input('site_url', 'input', 'Site Address', 'Enter the full url/address to yoursite, <strong>excluding</strong> trailing slash ( / )', $row['site_url']);
		$this->rknclass->form->add_input('site_name', 'input', 'Site Name', 'Enter a short name for your site, used on many Predator pages', $row['site_name']);
		$this->rknclass->form->add_input('site_description', 'textarea', 'Site Description', 'Enter the description of your website', $row['site_description']);
		$this->rknclass->form->add_input('meta_keywords', 'textarea', 'Meta Keywords', 'Please enter your site\'s meta keywords', $row['meta_keywords']);
		$this->rknclass->form->add_input('admin_email', 'input', 'Admin Email', 'Used for sending emails, and for receiving imported emails sent out by Predator. It is important that this value is valid', $row['admin_email']);
		$this->rknclass->form->add_input('plugs_per_page', 'input', 'Content Per Page', 'Enter the number of plugs you want displayed per page', $row['plugs_per_page']);

		$types="<div align=\"center\" style=\"font-size:12px; color:#213447;\">All <input name=\"all\" type=\"checkbox\" align=\"center\"/></div>
<div align=\"center\" style=\"font-size:12px; color:#213447;\">Plugs <input name=\"plugs\" type=\"checkbox\" align=\"center\"" . (in_array('1', $listing_types) ? ' CHECKED' : '') . "/></div>
<div align=\"center\" style=\"font-size:12px; color:#213447;\">Videos <input name=\"videos\" type=\"checkbox\" align=\"center\"" . (in_array('2', $listing_types) ? ' CHECKED' : '') . "/></div>
<div align=\"center\" style=\"font-size:12px; color:#213447;\">Blog Entries <input name=\"blogs\" type=\"checkbox\" align=\"center\"" . (in_array('5', $listing_types) ? ' CHECKED' : '') . "/></div>";
		
		$this->rknclass->form->add_input('listing_types', 'custom', 'Listing Filter', 'Please select the type\'s of content / media you want to display on your homepage, and category pages.', $types);
		$this->rknclass->form->add_input('next_hourly_cron', 'input', 'Next Hourly Cron', 'This value allows you to change when your next hourly cron will occur.<br /><br ><strong>Current Time:</strong> ' . date('jS M Y g:i:sa (e)'), date('j M Y g:i:sa', $row['next_hourly_cron']));
		$this->rknclass->form->add_input('next_daily_cron', 'input', 'Next Daily Cron', 'This value allows you to change when your next daily cron will occur.<br /><br ><strong>Current Time:</strong> ' . date('jS M Y g:i:sa (e)'), date('j M Y g:i:sa', $row['next_daily_cron']));
		$this->rknclass->form->add_input('queue_time', 'input', 'Content Queue Interval', 'Enter the time <strong>in minutes</strong> between each plug release in the queue system.<br /><br /><strong>Set to 0 to disable content queuing</strong>', $row['queue_time']);
		$this->rknclass->form->add_input('optimise_db', 'dropdown', 'Optimise Db', 'Please select whether or not you\'d like Predator to automatically optimise your database tables every 24 hours. If set to yes, this option may cause table locking issues on very large sites', '<option value="0">No</option><option value="1"' . ($row['optimise_db'] == '1' ? ' SELECTED' : '') . '>Yes</option>');
		
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function session_settings()
	{
		$this->rknclass->page_title='Manage Session Engine Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Session Engine Settings');
		
		if($row['cache_sessions'] == '1')
		{
			$cache_sessions='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$cache_sessions='<option value="0" SELECTED>No</option><option value="1">Yes</option>';
		}
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=session_settings');
		$this->rknclass->form->add_input('session_length', 'input', 'Session Length', 'This is the length of your sessions, in seconds. If a user is inactive for more than this time, their session will expire. Default is 15 minutes', $row['session_length']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function trade_settings()
	{
		$this->rknclass->page_title = 'Manage Traffic Trade Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Traffic Trade Settings');
		
		if($row['trade_type'] == 'ratio')
		{
			$trade_type='<option value="credits">Credits</option><option value="ratio" SELECTED>Ratio</option>';
		}
		else
		{
			$trade_type='<option value="credits" SELECTED">Credits</option><option value="ratio">Ratio</option>';
		}
		
		if($row['trade_default_status'] == '1')
		{
			$tds='<option value="0">Automatic</option><option value="1" SELECTED>Approved</option>';
		}
		else
		{
			$tds='<option value="0" SELECTED">Automatic</option><option value="1">Approved</option>';
		}
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=trade_settings');
		$this->rknclass->form->add_input('trade_type', 'dropdown', 'Trade Type', 'Select which trade type you wish to use for webmasters. This value can be changed at any time, and your stats will be converted automatically.<br /><br />Credits force an equal trade, whereas ratio can be configured to allow a lower/higher amount to have been sent by the webmaster', $trade_type);
		$this->rknclass->form->add_input('trade_min_ratio', 'input', 'Ratio trade percentage', 'If you are using Ratio as the trade method, enter the required percentage of return hits the user must send back.', $row['trade_min_ratio']);
		$this->rknclass->form->add_input('trade_default_status', 'dropdown', 'New trade\'s default status', 'Select the default approval status of new sites in the ratio. When set to approved, sites do not need to send in the initial hits in defined below.<br /><br />We recommend <strong>automatic</strong>', $tds);
		$this->rknclass->form->add_input('trade_min_in', 'input', 'Initial hits in requirement', 'Enter the number of hits a <strong>site</strong> must send before being approved in the trade system. <br /><br />Use with the above option', $row['trade_min_in']);
		$this->rknclass->form->add_input('trade_calc_method', 'dropdown', 'Trade Calculation Method', 'Please select how you\'d like Predator to calculate the ratio / credits of your trade partners, when they are submitting', '<option value="0">Per Site</option><option value="1"' . ($row['trade_calc_method'] == '1' ? ' SELECTED' : '') . '>Per User</option>');
		$this->rknclass->form->add_input('trade_24_method', 'dropdown', 'Daily Limit Configuration', 'Please select how you\'d like the trade system to limit the amount of plugs that can be submitted within 24 hours', '<option value="0">Per Site</option><option value="1"' . ($row['trade_24_method'] == '1' ? ' SELECTED' : '') . '>Per User</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function add_group()
	{
		$this->rknclass->page_title='Add new usergroup';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add group');
		$this->rknclass->form->ajax=false; //Better having this section more responsive...
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=add_group');
		$this->rknclass->form->add_input('name', 'input', 'Group Name', 'Enter a name for this usergroup. This value appears in the admin cp, and to users who are in the group.');
		$this->rknclass->form->add_input('plugs_approved', 'dropdown', 'Plugs Approved', 'If set to yes, users in this group will have their plugs approved, regardless of their sites ratio.', '<option value="0">No</option><option value="1">Yes</option>');
		$this->rknclass->form->add_input('submit_limit', 'input', 'Daily Submit Limit', 'This limits the amount of plugs that users in this group can submit within a 24 hour basis.<br /><br /><strong>Set to -1 to allow unlimited plugs to be submitted</strong>', '3');
		$this->rknclass->form->add_input('max_avatar_width', 'input', 'Maximum Avatar Width', 'Enter, in pixels, the Maximum width of a users avatar', '100');
		$this->rknclass->form->add_input('max_avatar_height', 'input', 'Maximum Avatar Height', 'Enter, in pixels, the Maximum height of a users avatar', '100');
		$this->rknclass->form->add_input('max_avatar_size', 'input', 'Maximum Avatar Size', 'Enter, in bytes, the Maximum filesize of a users uploaded avatar.<br /><br />Default is equal to 100kb / 0.1 mb', '102400');
		$this->rknclass->form->add_input('can_comment', 'dropdown', 'Post comments', 'Choose whether or not you want users in this group to be able to comment on posts. If you have the comments system disabled globally, they won\'t be able to comment, regardless of this permission setting.<br /><br />' . ($this->rknclass->settings['comments_enabled'] == '1' ? '<strong><font color="green">Comments are enabled</font></strong>' : '<strong><font color="red">Comments are disabled</font></strong>'), '<option value="1">Yes</option><option value="0">No</option>');
		$this->rknclass->form->add_input('captcha_enabled', 'dropdown', 'Enable Captcha System', 'If set to yes, the captcha image verification system will be enabled for this usergroup, for example, when they wish to post a comment', '<option value="1">Yes</option><option value="0">No</option>');
		$this->rknclass->form->add_input('can_search', 'dropdown', 'Can use search', 'Select whether or not you want users in this group to be able to use the Predator search engine feature.', '<option value="1">Yes</option><option value="0">No</option>');
		$this->rknclass->form->add_input('search_flood_control', 'input', 'Search flood control', 'Enter, in seconds, the amount of time that must pass in between each unique search made by users in this group<br /><br /><strong>Set to -1 if you do not wish to use flood control</strong>', '20');
		$this->rknclass->form->add_input('plug_edit_time', 'input', 'Allowed plug management time', 'Enter, in seconds, the amount of a time a user will have management powers over their submitted plug (edit/delete). <br /><br /><strong>Set to -1 for no limit, set to 0 to disallow any editing</strong>', '300');
		$this->rknclass->form->add_input('is_public', 'dropdown', 'Public group', 'When set to yes, registered users can select this group when editing their profile, or when new users are registering on your site.', '<option value="0">No</option><option value="1">Yes</option>');
		$this->rknclass->form->add_input('is_admin', 'dropdown', 'Admin group', 'This option allows you to grant admin control panel access to this group. <br /><br /><strong>Be careful with this option!</strong>', '<option value="0" SELECTED>No</option><option value="1">Yes</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function manage_groups()
	{
		$return_url='?' . $_SERVER['QUERY_STRING'];
		$return_url=str_replace('&', '[and]', $return_url);
		
		$this->rknclass->page_title='Manage Usergroups';
		
		$this->rknclass->global_tpl->admin_header();
		echo "<div class=\"page-title\">Manage Usergroups</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">Name</th>
    <th scope=\"col\">Type</th>
    <th scope=\"col\">Public</th>
    <th scope=\"col\">Edit</th>
    <th scope=\"col\">Delete</th>
  </tr>";
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups ORDER BY name ASC");
		$default_groups=array('1', '2', '3', '4');
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "\n<tr id=\"rows\">
    <td id=\"title\">$row[name]</td>
    <td>" . (in_array($row['group_id'], $default_groups, true) === true ? "Default" : "Custom") . "</td>
    <td>" . ($row['is_public'] == '1' ? "Yes" : "No") . "</td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_group&amp;id=$row[group_id]\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_group&amp;id=$row[group_id]&amp;return_url=$return_url\" onclick=\"" . (in_array($row['group_id'], $default_groups, true) === true ? "alert('You cannot delete this group as it is a default group'); return false;" : "return confirm('Are you sure you want to delete this usergroup? All users will be moved to the members usergroup');") . "\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function edit_group()
	{
	
		if($this->rknclass->get['id'] === false || $this->rknclass->get['id'] == '')
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid usergroup specified.', $this->rknclass->get['return_url']));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE group_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The usergroup could not be found in the database!', $this->rknclass->get['return_url']));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		$this->rknclass->page_title='Edit usergroup';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit group');
		$this->rknclass->form->ajax=false; //Better having this section more responsive...
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=edit_group&amp;id=' . $this->rknclass->get['id']);
		
		/*=============================
		Start variables for drop-down
		selection boxes.
		==============================*/
		
		if($row['plugs_approved'] == '1')
		{
			$plugs_approved='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$plugs_approved='<option value="0">No</option><option value="1">Yes</option>';
		}
		if($row['can_comment'] == '1')
		{
			$can_comment='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$can_comment='<option value="0">No</option><option value="1">Yes</option>';
		}
		if($row['captcha_enabled'] == '1')
		{
			$captcha_enabled='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$captcha_enabled='<option value="0">No</option><option value="1">Yes</option>';
		}
		if($row['is_public'] == '1')
		{
			$is_public='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$is_public='<option value="0">No</option><option value="1">Yes</option>';
		}
		
		if($row['can_search'] == '1')
		{
			$can_search='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$can_search='<option value="0">No</option><option value="1">Yes</option>';
		}
		if($row['require_validation'] == '1')
		{
			$require_validation='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
		}
		else
		{
			$require_validation='<option value="0">No</option><option value="1">Yes</option>';
		}
		
		
		/*=====================================
		The dropdown box is configured with a
		few safety features to help prevent
		admins from removing their permissions,
		or giving guests/members acp access.
		You'd be suprised how many support tickets
		this will save...
		======================================*/
		
		
		if($row['group_id'] !== '4')
		{
			if($row['is_admin'] == '1')
			{
				$is_admin='<option value="0">No</option><option value="1" SELECTED>Yes</option>';
			}
			else
			{
				$is_admin='<option value="0">No</option><option value="1">Yes</option>';
			}
		}
		else
		{
			$is_admin='<option value="1">Yes</option>'; //Prevents admins from fucking up their usergroup permissions
			$is_public='<option value="0">No</option>'; //Also Prevents admins from fucking up their usergroup permissions
		}
		
		if($row['group_id'] == '1' || $row['group_id'] == '2' || $row['group_id'] == '3')
		{
			$is_admin='<option value="0">No</option>'; //Prevents admins from setting public groups as admins
		}
		
		if($row['group_id'] == '1')
		{
			$is_public='<option value="0">No</option>'; //Prevents guests from being set as a joinable group lol
		}
		
		elseif($row['group_id'] == '2')
		{
			$is_public='<option value="1">Yes</option>'; //Ensures the members' group is set as joinable
		}
		
		$this->rknclass->form->add_input('name', 'input', 'Group Name', 'Enter a name for this usergroup. This value appears in the admin cp, and to users who are in the group.', $row['name']);
		$this->rknclass->form->add_input('plugs_approved', 'dropdown', 'Plugs Approved', 'If set to yes, users in this group will have their plugs approved, regardless of their sites ratio.', $plugs_approved);
		$this->rknclass->form->add_input('require_validation', 'dropdown', 'Validate Content Submission', 'If set to yes, you will need to manually approve any content submitted by users in this group', $require_validation);
		$this->rknclass->form->add_input('submit_limit', 'input', 'Daily Submit Limit', 'This limits the amount of plugs that users in this group can submit within a 24 hour basis.<br /><br /><strong>Set to -1 to allow unlimited plugs to be submitted</strong>', $row['submit_limit']);
		$this->rknclass->form->add_input('max_avatar_width', 'input', 'Maximum Avatar Width', 'Enter, in pixels, the Maximum width of a users avatar', $row['max_avatar_width']);
		$this->rknclass->form->add_input('max_avatar_height', 'input', 'Maximum Avatar Height', 'Enter, in pixels, the Maximum height of a users avatar', $row['max_avatar_height']);
		$this->rknclass->form->add_input('max_avatar_size', 'input', 'Maximum Avatar Size', 'Enter, in bytes, the Maximum filesize of a users uploaded avatar.<br /><br />Default is equal to 100kb / 0.1 mb', $row['max_avatar_size']);
		$this->rknclass->form->add_input('can_comment', 'dropdown', 'Post comments', 'Choose whether or not you want users in this group to be able to comment on posts. If you have the comments system disabled globally, they won\'t be able to comment, regardless of this permission setting.<br /><br />' . ($this->rknclass->settings['comments_enabled'] == '1' ? '<strong><font color="green">Comments are enabled</font></strong>' : '<strong><font color="red">Comments are disabled</font></strong>'), $can_comment);
		$this->rknclass->form->add_input('captcha_enabled', 'dropdown', 'Enable Captcha System', 'If set to yes, the captcha image verification system will be enabled for this usergroup, for example, when they wish to post a comment', $captcha_enabled);
		$this->rknclass->form->add_input('can_search', 'dropdown', 'Can use search', 'Select whether or not you want users in this group to be able to use the Predator search engine feature.', $can_search);
		$this->rknclass->form->add_input('search_flood_control', 'input', 'Search flood control', 'Enter, in seconds, the amount of time that must pass in between each unique search made by users in this group<br /><br /><strong>Set to -1 if you do not wish to use flood control</strong>', $row['search_flood_control']);
		$this->rknclass->form->add_input('plug_edit_time', 'input', 'Allowed plug management time', 'Enter, in seconds, the amount of a time a user will have management powers over their submitted plug (edit/delete). <br /><br /><strong>Set to -1 for no limit, set to 0 to disallow any editing</strong>', $row['plug_edit_time']);
		$this->rknclass->form->add_input('is_public', 'dropdown', 'Public group', 'When set to yes, registered users can select this group when editing their profile, or when new users are registering on your site.', $is_public);
		$this->rknclass->form->add_input('is_admin', 'dropdown', 'Admin group', 'This option allows you to grant admin control panel access to this group. <br /><br /><strong>Be careful with this option!</strong>', $is_admin);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function view_sites()
	{
		$return_url='?' . $_SERVER['QUERY_STRING'];
		$return_url=str_replace('&', '[and]', $return_url);
		$pos=0;
		
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP
		
		if(!isset($this->rknclass->get['user_id']) AND empty($this->rknclass->get['user_id']))
		{
			$user = false;
		}
		else
		{
			$user = true;
		}
		
		/*========================
		Query below will set our
		own value for the pager
		=========================*/
		
		if($user === false)
		{
			$this->rknclass->db->query("SELECT count(site_id) FROM " . TBLPRE . "sites WHERE owner > 0");
			$count = $this->rknclass->db->result();
		}
		else
		{
			$this->rknclass->db->query("SELECT count(site_id) FROM " . TBLPRE . "sites WHERE owner='{$this->rknclass->get['user_id']}'");
			$count = $this->rknclass->db->result();
			if($count < 1)
			{
				exit($this->rknclass->global_tpl->admin_error('This user doesn\'t have any sites yet!'));
			}
		}
		
		$this->rknclass->pager->total=$count; //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->page_title='View Sites';
		$this->rknclass->global_tpl->admin_header();
		
		$this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
		
		echo "<div class=\"page-title\">All Sites</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">{$this->order_by('url', 'Site Url')}</th>
    <th scope=\"col\">{$this->order_by('u_todays_in', 'Unique<br />Today\'s In')}</th>
    <th scope=\"col\">{$this->order_by('u_todays_out', 'Unique<br />Today\'s Out')}</th>
    <th scope=\"col\">{$this->order_by('r_todays_in', 'Raw<br />Today\'s In')}</th>
    <th scope=\"col\">{$this->order_by('r_todays_out', 'Raw<br />Today\'s Out')}</th>
    <th scope=\"col\">{$this->order_by('u_total_in', 'Unique<br />Total In')}</th>
    <th scope=\"col\">{$this->order_by('u_total_out', 'Unique<br />Total Out')}</th>
    <th scope=\"col\">{$this->order_by('r_total_in', 'Raw<br />Total In')}</th>
    <th scope=\"col\">{$this->order_by('r_total_out', 'Raw<br />Total Out')}</th>
    <th scope=\"col\">Unique<br />{$type}</th>
    <th scope=\"col\">{$this->order_by('approved', 'Apr')}</th>
    <th scope=\"col\">Edit</th>
    <th scope=\"col\">Del</th>
  </tr>";
    
		$order     = 'url ASC';
		$order_url = '';
		
		$this->fetch_order($order, $order_url);
		
		$this->rknclass->db->query("SELECT *, Ceil(((u_total_in/u_total_out)*100)) AS ratio, (u_total_in - u_total_out) AS credits FROM " . TBLPRE . "sites " . ($user === true ? "WHERE owner='{$this->rknclass->get['user_id']}'" : 'WHERE owner > 0 ') . "ORDER BY {$order} LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array())
		{
			++$pos;
			
			$ratio=$this->rknclass->utils->get_trade_by_in_out($row['u_total_in'], $row['u_total_out']);
			
			if($this->rknclass->utils->trade_check($row['u_total_in'], $row['u_total_out']) === false)
			{
				$ratio="<font color=\"#e32c00\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
			}
			else
			{
				$ratio="<font color=\"#136f01\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
			}
			
			if(strlen($row['url']) >= 10)
			{
				$url = substr($row['url'], 0, 7) . '...';
			}
			else
			{
				$url = $row['url'];
			}
			
			echo "\n<tr id=\"rows\">
    <td id=\"title\"><a href=\"http://www.{$row['url']}\" target=\"_blank\" title=\"{$row['name']}\">$url</a></td>
    <td>{$row['u_todays_in']}</td>
    <td>{$row['u_todays_out']}</td>
    <td>{$row['r_todays_in']}</td>
    <td>{$row['r_todays_out']}</td>
    <td>{$row['u_total_in']}</td>
    <td>{$row['u_total_out']}</td>
    <td>{$row['r_total_in']}</td>
    <td>{$row['r_total_out']}</td>
    <td><strong>$ratio</strong></td>
    <td><strong>" . ($row['approved'] == '0' ? "<font color=\"#e32c00\">No" : "<font color=\"#136f01\">Yes") . "</font></strong></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_site&amp;id={$row['site_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_site&amp;id={$row['site_id']}&amp;return_url=$return_url\" onclick=\"return confirm('Are you sure you want to delete this site?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		echo '<div id="pagination">';
		if($user === false)
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=view_sites&amp;page=' . $this->pager_data['previous'] . $order_url . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=view_sites&amp;page=' . $this->pager_data['next'] . $order_url . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
			echo '</div>';
			$this->rknclass->global_tpl->admin_footer();
		}
		else
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=view_sites&amp;page=' . $this->pager_data['previous'] . '&amp;user_id=' . $this->rknclass->get['user_id'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=view_sites&amp;page=' . $this->pager_data['next'] . '&amp;user_id=' . $this->rknclass->get['user_id'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
			echo '</div>';
			$this->rknclass->global_tpl->admin_footer();
		}
	}

	public function edit_site()
	{
		if($this->rknclass->get['id']=='')
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}"));
		}
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sites WHERE site_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid Site - This site does not exist in the trade system!'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if(intval($row['owner']) < 1)
		{
			exit($this->rknclass->global_tpl->admin_error('This site was added automatically by Predator - please wait until a <br />user adds this site before attempting to manage it!'));
		}
		$this->rknclass->page_title='Edit site ' . $row['name'] . '';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit site ' . $row['name'] . '');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=update_site&amp;id=' . $this->rknclass->get['id'] . '');
		$this->rknclass->form->add_input('site_name', 'input', 'Website Title', 'Enter the name/title of the website. This value must be unique', $row['name']);
		$this->rknclass->form->add_input('site_url', 'input', 'Website url', 'Enter the url of the website. This <u>must</u> start with <strong>http://</strong>', 'http://' . $row['url']);
		$this->rknclass->form->add_input('u_total_in', 'input', 'Unique Total In', 'This field allows you to modify the stats for the total hits sent in by this site', $row['u_total_in']);
		$this->rknclass->form->add_input('u_total_out', 'input', 'Unique Total Out', 'This field allows you to modify the stats for the total hits sent back to this site', $row['u_total_out']);
		$this->rknclass->form->add_input('r_total_in', 'input', 'Raw Total In', 'This field allows you to modify the stats for the total hits sent in by this site', $row['r_total_in']);
		$this->rknclass->form->add_input('r_total_out', 'input', 'Raw Total Out', 'This field allows you to modify the stats for the total hits sent back to this site', $row['r_total_out']);
		$approved="<option value=\"1\">Yes</option><option value=\"0\"" . ($row['approved'] == '0' ? "SELECTED" : "") . ">No</option>";
		$banned="<option value=\"1\">Yes</option><option value=\"0\"" . ($row['banned'] == '0' ? "SELECTED" : "") . ">No</option>";
		$this->rknclass->form->add_input('approved', 'dropdown', 'Approved', 'Select whether this site is approved or not', $approved);
		$this->rknclass->form->add_input('banned', 'dropdown', 'Banned', 'If you wish to ban this site, the user will not be allowed to submit plugs from it anymore', $banned);
		
		$users = "\n";
		$this->rknclass->db->query("SELECT user_id,username FROM " . TBLPRE . "users ORDER BY username ASC");
		while($row2 = $this->rknclass->db->fetch_array())
		{
			$users .= "<option value=\"{$row2['user_id']}\"" . ($row2['user_id'] == $row['owner'] ? ' SELECTED' : '') . ">{$row2['username']}</option>\n";
		}
		
		$this->rknclass->form->add_input('owner', 'dropdown', 'Owner', 'Please select which user id you would like to assign ownership rights to for this site', $users);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function image_cropper()
	{
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings");
		$row=$this->rknclass->db->fetch_array();
		
		$this->rknclass->page_title='Image cropper';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Image cropper settings');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=image_cropper');
		$this->rknclass->form->add_input('thumb_dir', 'input', 'Thumbnail Directory', 'Enter the name of the folder/directory where you wish to store your thumbnails. This is relative to your sites root dir<br /><br /><strong>DO NOT include a trailing slash (/)</strong>', $row['thumb_dir']);
		$this->rknclass->form->add_input('thumb_width', 'input', 'Thumbnail Width', 'Enter, in pixels, the width you wish uploaded thumbnails to be cropped to. This is the value they\'ll display as on your site', $row['thumb_width']);
		$this->rknclass->form->add_input('thumb_height', 'input', 'Thumbnail Height', 'Enter, in pixels, the height you wish uploaded thumbnails to be cropped to. This is the value they\'ll display as on your site', $row['thumb_height']);
		$this->rknclass->form->add_input('thumb_quality', 'input', 'Thumbnail Quality', 'Enter the quality you want thumbnails to be cropped to. Values range from 0-100. <br /><br /><strong>We recommend somewhere between 70 and 85</strong>', $row['thumb_quality']);
		$this->rknclass->form->add_input('v_thumb_width', 'input', 'Video Thumbnail Width', 'Enter, in pixels, the width you wish video thumbnails to be cropped to. This is the value they\'ll display as on your site', $row['v_thumb_width']);
		$this->rknclass->form->add_input('v_thumb_height', 'input', 'Video Thumbnail Height', 'Enter, in pixels, the height you wish video thumbnails to be cropped to. This is the value they\'ll display as on your site', $row['v_thumb_height']);
		$this->rknclass->form->add_input('v_thumb_quality', 'input', 'Video Thumbnail Quality', 'Enter the quality you want video to be cropped to. Values range from 0-100. <br /><br /><strong>We recommend somewhere between 70 and 85</strong>', $row['v_thumb_quality']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function add_user()
	{	
		$this->rknclass->page_title='Add User';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add a new account');
		$this->rknclass->form->ajax=false;//We want it to be a bit more responsive for this page
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=add_user');
		$this->rknclass->form->add_input('username', 'input', 'Username<strong><font color="#FF0000">*</strong></font>', 'Enter the name of the user you wish to add');
		$this->rknclass->form->add_input('password', 'input', 'Password<strong><font color="#FF0000">*</strong></font>', 'Enter their desired password');
		$this->rknclass->form->add_input('email', 'input', 'Email<strong><font color="#FF0000">*</strong></font>', 'Enter a valid email address for the user');
		$this->rknclass->form->add_input('firstname', 'input', 'Firstname', 'If the user has a name (most people do), enter it here');
		$this->rknclass->form->add_input('surname', 'input', 'Surname', 'If the user has a surname (most people have that too), enter it here');
		$this->rknclass->form->add_input('icq', 'input', 'ICQ', 'If the user has an ICQ account, enter their <strong>number</strong> here');
		$this->rknclass->form->add_input('aim', 'input', 'Aol Instant Messenger', 'Enter the users AIM username if they have one');
		$this->rknclass->form->add_input('gtalk', 'input', 'Google Talk Address', 'If the user has gmail/google talk, enter their address here');
		
		/*==============================
		Create the usergroup drop-down
		for the select box
		===============================*/
		
		$groups='';
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE group_id!='1'");
		while($row=$this->rknclass->db->fetch_array())
		{
			$groups.="\n<option value=\"$row[group_id]\"" . ($row['group_id'] == '2' ? ' SELECTED' : '') . ">$row[name]" . ($row['is_public'] == '0' ? ' [Private]' : '') . "</option>";
		}
		
		$this->rknclass->form->add_input('group', 'dropdown', 'Usergroup<strong><font color="#FF0000">*</strong></font>', 'Select a usergroup for the member from the dropdown', $groups);
		
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();		
	}
	public function manage_users()
	{
		$return_url='?' . $_SERVER['QUERY_STRING'];
		$return_url=str_replace('&', '[and]', $return_url);
		
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP
		
		
		/*========================
		Query below will set our
		own value for the pager
		=========================*/
		
		$this->rknclass->db->query("SELECT count(user_id) FROM " . TBLPRE . "users");
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->page_title='Manage Users';
		
		$this->rknclass->global_tpl->admin_header();
		echo "<div class=\"page-title\">Manage Users</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\"><strong>Username</strong></th>
	<th scope=\"col\">E-Mail</th>
	<th scope=\"col\">Aim</th>
	<th scope=\"col\">ICQ</th>
	<th scope=\"col\">Total Sites</th>
	<th scope=\"col\">Total " . ucfirst($this->rknclass->settings['trade_type']) . "</th>
    <th scope=\"col\">Edit</th>
    <th scope=\"col\">Delete</th>
  </tr>";
		$result_users = $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users ORDER BY username ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		while($row=$this->rknclass->db->fetch_array($result_users))
		{
			$result = $this->rknclass->db->query("SELECT u_total_in,u_total_out FROM " . TBLPRE . "sites WHERE owner='{$row['user_id']}'");
			
			if($this->rknclass->db->num_rows($result) < 1)
			{
				$ratio = 'N/A';
			}
			else
			{
				$total_in  = 0;
				$total_out = 0;
				
				while($row2 = $this->rknclass->db->fetch_array($result))
				{
					$total_in  += $row2['u_total_in'];
					$total_out += $row2['u_total_out'];
				}
				
				$ratio = $this->rknclass->utils->get_trade_by_in_out($total_in,$total_out);
				
				if($this->rknclass->utils->trade_check($total_in,$total_out) === false)
				{
					$ratio = '<strong><font color="red">' . $ratio . ($this->rknclass->settings['trade_type'] == 'ratio' ? '%' : '') . '</font></strong>';
				}
				else
				{
					$ratio = '<strong><font color="green">' . $ratio . ($this->rknclass->settings['trade_type'] == 'ratio' ? '%' : '') . '</font></strong>';
				}
			}
			echo "\n<tr id=\"rows\">
    <td id=\"title\">$row[username]</td>
	<td>$row[email]</td>
	<td>" . ($row['aim'] == '' ? "---" : $row['aim']) . "</td>
	<td>" . ($row['icq'] == '' ? "---" : $row['icq']) . "</td>
	<td><a href=\"index.php?ctr=management&amp;act=view_sites&amp;user_id={$row['user_id']}\">{$row['total_sites']}</a></td>
	<td>$ratio</td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_user&amp;id={$row['user_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_user&amp;id={$row['user_id']}&amp;return_url=$return_url\" onclick=\"return confirm('Are you sure you want to delete this user? Any sites that they own will also be deleted!');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=management&amp;act=manage_users&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=management&amp;act=manage_users&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function edit_user()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid user id specified'));
		}
		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "users WHERE user_id='" . $this->rknclass->get['id'] . "' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('The user was not found in the database'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->page_title='Edit User';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Edit member account');
		$this->rknclass->form->ajax=false;//We want it to be a bit more responsive for this page
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=edit_user&amp;id=' . $this->rknclass->get['id']);
		$this->rknclass->form->add_input('username', 'input', 'Username<strong><font color="#FF0000">*</strong></font>', 'Enter the name of the user you wish to add', $row['username']);
		$this->rknclass->form->add_input('password', 'input', 'Password', '<strong>Leave blank if you do not wish to change it</strong>');
		$this->rknclass->form->add_input('email', 'input', 'Email<strong><font color="#FF0000">*</strong></font>', 'Enter a valid email address for the user', $row['email']);
		$this->rknclass->form->add_input('firstname', 'input', 'Firstname', 'If the user has a name (most people do), enter it here', $row['firstname']);
		$this->rknclass->form->add_input('surname', 'input', 'Surname', 'If the user has a surname (most people have that too), enter it here', $row['surname']);
		$this->rknclass->form->add_input('icq', 'input', 'ICQ', 'If the user has an ICQ account, enter their <strong>number</strong> here', $row['icq']);
		$this->rknclass->form->add_input('aim', 'input', 'Aol Instant Messenger', 'Enter the users AIM username if they have one', $row['aim']);
		$this->rknclass->form->add_input('gtalk', 'input', 'Google Talk Address', 'If the user has gmail/google talk, enter their address here', $row['gtalk']);
		
		/*==============================
		Create the usergroup drop-down
		for the select box
		===============================*/
		
		$groups='';
		
		$result2=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "groups WHERE group_id!='1'");
		while($row2=$this->rknclass->db->fetch_array($result2))
		{
			$groups.="\n<option value=\"$row2[group_id]\"" . ($row2['group_id'] == $row['group_id'] ? ' SELECTED' : '') . ">$row2[name]" . ($row2['is_public'] == '0' ? ' [Private]' : '') . "</option>";
		}
		
		$this->rknclass->form->add_input('group', 'dropdown', 'Usergroup<strong><font color="#FF0000">*</strong></font>', 'Select a usergroup for the member from the dropdown', $groups);
		
		$validated='<option value="0">No</option><option value="1"' . ($row['validated'] == '1' ? ' SELECTED' : '') . '>Yes</option>';
		$this->rknclass->form->add_input('validated', 'dropdown', 'Account Validated', 'This determines whether or not the user\'s account is verified', $validated);
		
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();		
	}
	
	public function compose_mail()
	{
		$this->rknclass->page_title='Compose Mail';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Compose Mass Mail');
		$this->rknclass->form->ajax=false;//We want it to be a bit more responsive for this page
		$this->rknclass->form->set_action('index.php?ctr=management&amp;act=send_email');
		$this->rknclass->form->add_input('mail_title', 'input', 'Message Title', 'Enter the title of the email you wish to send');
		$this->rknclass->form->add_input('mail_body', 'textarea', 'Message Body', 'Enter the main body/message of the email you wish to send to your users<br /><br />
		<strong>{user[username]}</strong> will be replaced by the user\'s username<br />
		<strong>{user[email]}</strong> will be replaced by the user\'s email address<br />
		<strong>{user[user_id]}</strong> will be replaced by the user\'s user_id<br />
		<strong>{predator[site_name]}</strong> will be replaced by the your site\'s name<br />
		<strong>{predator[site_url]}</strong> will be replaced by your site\'s address/url<br />
		');
		$this->rknclass->form->add_input('mail_batch', 'dropdown', 'Batch Count', 'Select the number of emails you wish to send per batch.<br /><br /><strong>WARNING</strong>: Selecting a value of over 100 may seriously affect your server load, even after predator has processed the email queue. Only choose 100 or higher if you are confident your server can handle high MySQL and mail processes...its better being safe than sorry :-)', '<option value="25">25</option><option value="50" SELECTED>50</option><option value="100">100</option><option value="150">150</option><option value="200">200</option value>');
		
		$groups="<div align=\"center\" style=\"font-size:12px; color:#213447;\">All Usergroups<input name=\"all\" type=\"checkbox\" align=\"center\" CHECKED/></div>";
		
		$this->rknclass->db->query("SELECT group_id, name FROM " . TBLPRE . "groups WHERE group_id!='1' ORDER BY name ASC");
		
		while($row=$this->rknclass->db->fetch_array())
		{
			$groups.="\n<div align=\"center\" style=\"font-size:12px; color:#213447;\">{$row['name']}<input name=\"groups[]\" value=\"{$row['group_id']}\" type=\"checkbox\" align=\"center\"/></div>";
		}
		
		$this->rknclass->form->add_input('send_to', 'custom', 'Send To', 'Select the groups you want to send this message to', $groups);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function send_email()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
		if(!empty($this->rknclass->post['all']))
		{
			$groups=array();
			$this->rknclass->db->query("SELECT group_id FROM " . TBLPRE . "groups WHERE group_id!='1'");
			while($row=$this->rknclass->db->fetch_array())
			{
				array_push($groups, $row['group_id']);
			}
		}
		else
		{
			if($_POST['groups'] == '')
			{
				exit($this->rknclass->global_tpl->admin_error('You must select at least one group!'));
			}
			
			$groups=array();
			foreach($_POST['groups'] as $key => $value)
			{
				$value=$this->rknclass->cleaner->clean($value, 'int');
				if($value === false)
				{
					exit($this->rknclass->global_tpl->admin_error('Invalid group(s) specified'));
				}
				array_push($groups, $value);
			}
		}
		
		if(empty($this->rknclass->post['mail_title']) || empty($this->rknclass->post['mail_body']))
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}
		
		if(intval($this->rknclass->post['mail_batch']) == '')
		{
			exit($this->rknclass->global_tpl->admin_error('One or more fields were left blank!'));
		}
		
		$this->rknclass->post['mail_title']=$this->rknclass->db->escape($_POST['mail_title']);
		$this->rknclass->post['mail_body']=$this->rknclass->db->escape($_POST['mail_body']);
		$this->rknclass->db->query("INSERT INTO " . TBLPRE . "mail SET title='{$this->rknclass->post['mail_title']}', body='{$this->rknclass->post['mail_body']}', batch_size='{$this->rknclass->post['mail_batch']}', author_id='{$this->rknclass->user['user_id']}', init='" . time() . "'");
		
		$insert_id=$this->rknclass->db->insert_id();
		
		$count=0;
		
		foreach($groups as $key)
		{
			$result=$this->rknclass->db->query("SELECT user_id FROM " . TBLPRE . "users WHERE group_id='$key'");
			while($row=$this->rknclass->db->fetch_array($result))
			{
				++$count;
				$this->rknclass->db->query("INSERT INTO " . TBLPRE . "mail_queue SET mail_id='$insert_id', member_id='{$row['user_id']}'");
			}
		}
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "mail SET total_mails='$count' WHERE mail_id='$insert_id' LIMIT 1");
		$this->rknclass->cache->update_settings_and_cache(array('mail_in_progress' => 1));
		$this->rknclass->global_tpl->exec_redirect('Successfully added ' . $count . ' messages to the mail queue!', '?ctr=management&act=mail_man');
	}
	
	public function mail_man()
	{
		$this->rknclass->page_title='Mail Man';
		$this->rknclass->global_tpl->admin_header();
		echo '
        <div class="page-title">Mail Man Statistics</div>
        
 <table id="listings" cellpadding="1" cellspacing="0">
  <tr id="columns">
    <th scope="col" id="title">Title</th>
    <th scope="col">Author</th>
    <th scope="col">Date Sent</th>
	<th scope="col">Time Taken</th>
	<th scope="col">No. Emails</th>
    <th scope="col">Batch Size</th>
    <th scope="col">Completed</th>
    <th scope="col">Delete</th>
  </tr>';
		$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "mail ORDER BY mail_id DESC");
		while($row=$this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->db->query("SELECT username FROM " . TBLPRE . "users WHERE user_id='$row[author_id]' LIMIT 1");
			$author_id=$this->rknclass->db->result();
			echo "<tr id=\"rows\">
    <td id=\"title\">$row[title]</td>
    <td>$author_id</td>
	<td>" . $this->rknclass->utils->timetostr($row['init']) . "</td>
	<td>" . (!empty($row['end']) ? date('G:i:s',($row['end'] - $row['init'])) : date('G:i:s',(time() - $row['init']))). "</td>
	<td>$row[total_mails]</td>
	<td>$row[batch_size]</td>
	<td>" . (intval($row['end']) > 0 ? "<font style=\"color:#136f01; font-weight:bold\">Yes</font>" : "<font style=\"color:#e32c00; font-weight:bold\">" . @ceil(($row['total_sent']/$row['total_mails'])*100) . "%</font>") . "</td>
    <td><a href=\"index.php?ctr=management_update&amp;act=delete_mail&amp;id=$row[mail_id]\" onclick=\"return confirm('Are you sure you want to permanently remove this email log?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo '</table>';	
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function rebuild_cache()
	{
		$this->rknclass->cache->rebuild_settings_cache();
		$this->rknclass->cache->rebuild_groups_cache();
		$this->rknclass->global_tpl->exec_redirect('Successfully rebuilt Predator core cache system', '?ctr=management');
	}
	
	public function rebuild_stats()
	{
		switch ($this->rknclass->get['type'])
		{
			case 'cats_count':
				$this->rebuild_cats_count();
				break;
				
			case 'plug_cats':
				$this->rebuild_plug_cats();
				break;

			case 'user_plug_count':
				$this->rebuild_user_plug_count();
				break;
			
			case 'rebuild_sponsor_child_site_count':
				$this->rebuild_sponsor_child_site_count();
				break;
				
			case 'rebuild_output_cache':
			    $this->rebuild_output_cache();
			    break;
			    			
			default:
				$this->rebuild_site_count();
		}
	}
	
	private function rebuild_site_count()
	{
		$result = $this->rknclass->db->query("SELECT user_id FROM " . TBLPRE . "users");
		
		while($row = $this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites='" . $this->rknclass->db->result($this->rknclass->db->query("SELECT count(site_id) FROM " . TBLPRE . "sites WHERE owner='{$row['user_id']}'")) . "' WHERE user_id='{$row['user_id']}' LIMIT 1");
		}
		
		$this->rknclass->global_tpl->exec_redirect('Successfully rebuilt user site count', '?ctr=management&act=rebuild_stats&type=user_plug_count');		
	}
	
	private function rebuild_user_plug_count()
	{
		$result = $this->rknclass->db->query("SELECT user_id FROM " . TBLPRE . "users");
		
		while($row = $this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_plugs='" . $this->rknclass->db->result($this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE poster_id='{$row['user_id']}'")) . "' WHERE user_id='{$row['user_id']}' LIMIT 1");
		}
		
		$this->rknclass->global_tpl->exec_redirect('Successfully rebuilt user plug count', '?ctr=management&act=rebuild_stats&type=cats_count');		
	}
	
	private function rebuild_cats_count()
	{
		$result = $this->rknclass->db->query("SELECT cat_id FROM " . TBLPRE . "cats");
		
		while($row = $this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "cats SET total_plugs='" . $this->rknclass->db->result($this->rknclass->db->query("SELECT count(plug_id) FROM " . TBLPRE . "plugs WHERE category_id='{$row['cat_id']}'")) . "' WHERE cat_id='{$row['cat_id']}' LIMIT 1");
		}
		
		$this->rknclass->global_tpl->exec_redirect('Successfully rebuilt category content count', '?ctr=management&act=rebuild_stats&type=plug_cats');
	}
	
	private function rebuild_plug_cats()
	{
		$result = $this->rknclass->db->query("SELECT cat_id,cat_name FROM " . TBLPRE . "cats");
		while($row = $this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET category='" . $this->rknclass->db->escape($row['cat_name']) . "' WHERE category_id='{$row['cat_id']}'");
		}
		
			$this->rknclass->global_tpl->exec_redirect('Successfully rebuilt content category names', '?ctr=management&act=rebuild_stats&type=rebuild_sponsor_child_site_count');
	}
	
	private function rebuild_sponsor_child_site_count()
	{
		$result = $this->rknclass->db->query("SELECT sponsor_id FROM " . TBLPRE . "sponsors");
		
		while($row = $this->rknclass->db->fetch_array($result))
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "sponsors SET sponsor_site_count='" . $this->rknclass->db->result($this->rknclass->db->query("SELECT count(sponsor_site_id) FROM " . TBLPRE . "sponsors_sites WHERE sponsor_site_parent='{$row['sponsor_id']}'")) . "' WHERE sponsor_id='{$row['sponsor_id']}'  LIMIT 1");
		}
		
		$this->rknclass->global_tpl->exec_redirect('Successfully rebuilt content sponsors\' child site count', '?ctr=management&act=rebuild_stats&type=rebuild_output_cache');
	}
	
	private function rebuild_output_cache()
	{
	    $dir = RKN__fullpath . "cache/output/";
	    
	    if($handle = @opendir($dir))
	    {
	        while(($file = readdir($handle)) !== false)
	        {
	            if(end(explode('.', $file)) === 'php')
	            {
	                unlink($dir . $file);
	            }
	        }
	        
	        closedir($handle);
	    }
	    
	    if($this->rknclass->settings['memcache_server'] == '1')
	    {
	        $this->rknclass->memcache->flush();
	    }
	    
	    $this->rknclass->global_tpl->exec_redirect('Successfully rebuilt output cache', '?ctr=management');
	}
	
	public function cluster_settings()
	{
		if(!is_array($this->rknclass->settings['cluster_settings']))
		{
			$this->rknclass->settings['cluster_settings'] = @unserialize($this->rknclass->settings['cluster_settings']);
		}
		$this->rknclass->page_title='Server Clustering Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Manage Server Clustering');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=cluster_settings');
		$this->rknclass->form->add_input('thumb_server', 'dropdown', 'Thumbnail Server', 'Please select where you would like predator to store its thumbnails', '<option value="local">Local</option><option value="external"' . ($this->rknclass->settings['thumb_server'] == '1' ? ' SELECTED' : '') . '>External</option>');
		$this->rknclass->form->add_input('thumb_server_address', 'input', 'Thumbnail Server FTP Address', 'Please enter the address where Predator can connect via FTP to your thumbnail server. <br /><br />Eg. ftp.example.com' . (!function_exists('ftp_connect') ? '<br /><br /><strong><font color="red">Your server does not support the FTP handler!</font></strong>' : '<br /><br /><strong><font color="green">Your server supports the FTP handler!</font></strong>'), $this->rknclass->settings['cluster_settings']['thumb_server_address']);
		$this->rknclass->form->add_input('thumb_server_username', 'input', 'Thumbnail Server Username', 'Please enter the username which Predator can use to authenticate with the server', $this->rknclass->settings['cluster_settings']['thumb_server_username']);
		$this->rknclass->form->add_input('thumb_server_password', 'password', 'Thumbnail Server Password', 'Please enter the password Predator can use to authenticate with the server', $this->rknclass->settings['cluster_settings']['thumb_server_password']);
		$this->rknclass->form->add_input('thumb_server_http', 'input', 'Thumbnail Server Url', 'Please enter the full url to where your thumbnails will appear, <strong>excluding trailing-slash</strong><br /><br />Eg. http://thumbs.mysite.com', $this->rknclass->settings['cluster_settings']['thumb_server_http']);
		
		$this->rknclass->form->add_input('video_server', 'dropdown', 'Streaming Server', 'Please select where you would like predator to store your hosted videos', '<option value="local">Local</option><option value="external"' . ($this->rknclass->settings['video_server'] == '1' ? ' SELECTED' : '') . '>External</option>');
		$this->rknclass->form->add_input('video_server_address', 'input', 'Streaming Server FTP Address', 'Please enter the address where Predator can connect via FTP to your streaming server. <br /><br />Eg. ftp.example.com' . (!function_exists('ftp_connect') ? '<br /><br /><strong><font color="red">Your server does not support the FTP handler!</font></strong>' : '<br /><br /><strong><font color="green">Your server supports the FTP handler!</font></strong>'), $this->rknclass->settings['cluster_settings']['video_server_address']);
		$this->rknclass->form->add_input('video_server_username', 'input', 'Streaming Server Username', 'Please enter the username which Predator can use to authenticate with the server', $this->rknclass->settings['cluster_settings']['video_server_username']);
		$this->rknclass->form->add_input('video_server_password', 'password', 'Streaming Server Password', 'Please enter the password Predator can use to authenticate with the server', $this->rknclass->settings['cluster_settings']['video_server_password']);
		$this->rknclass->form->add_input('video_server_http', 'input', 'Streaming Server Url', 'Please enter the full url to where your videos will appear, <strong>excluding trailing-slash</strong><br /><br />Eg. http://videos.mysite.com', $this->rknclass->settings['cluster_settings']['video_server_http']);
		$this->rknclass->form->add_input('memcache_server_addr', 'input', 'Memcache Server', 'If you wish to setup Predator to use a memcache server, please enter its address here. Otherwise, leave blank<br /><br />' . (!function_exists('memcache_connect') ? '<strong><font color="red">Your server currently does not support Memcache!</font></strong>' : '<strong><font color="green">Your server supports Memcache!</font></strong>'), $this->rknclass->settings['cluster_settings']['memcache_server_address']);
		$this->rknclass->form->add_input('memcache_server_port', 'input', 'Memcache Server Port', 'Please enter the port which Predator can use to connect to your memcache server', $this->rknclass->settings['cluster_settings']['memcache_server_port']);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}

	public function view_sites_productivity()
	{
		
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=100; //TODO: Add option in ACP
		
		
		/*========================
		Query below will set our
		own value for the pager
		=========================*/
		
		$this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "sites WHERE owner>0");
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->page_title='Productivity (Plugs and banners)';
		$this->rknclass->global_tpl->admin_header();
		echo "<div class=\"page-title\">All Sites Productivity</div>
        
 <table id=\"listings\" cellpadding=\"0\" cellspacing=\"0\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">Site Name</th>
    <th scope=\"col\">Site Url</th>
    <th scope=\"col\">Productivity</th>
    <th scope=\"col\">Ad Prod.</th>
    <th scope=\"col\">Plug Prod.</th>
    <th scope=\"col\">Detailed</th>
    <th scope=\"col\">Edit</th>
    <th scope=\"col\">Del</th>
  </tr>";
  		
  		$this->rknclass->db->query("SELECT site_id,url,name,ad_prod,plug_prod,u_total_in, (plug_prod+ad_prod) AS total_prod FROM " . TBLPRE . "sites WHERE owner>0 ORDER BY total_prod DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		
		while($row=$this->rknclass->db->fetch_array())
		{
			$perc = @Ceil(($row['total_prod']/$row['u_total_in'])*100);
			

			if($perc > 70)
			{
				$color = 'green';
			}
			elseif($perc > 50)
			{
				$color = 'orange';
			}
			elseif($perc > 25)
			{
				$color = 'red';
			}
			else
			{
				$color = 'purple';
			}
			
			$prod = "<strong><font color=\"$color\">$perc %</font></strong>";

			$perc = @Ceil(($row['plug_prod']/$row['u_total_in'])*100);
			
			if($perc > 70)
			{
				$color = 'green';
			}
			elseif($perc > 50)
			{
				$color = 'orange';
			}
			elseif($perc > 25)
			{
				$color = 'red';
			}
			else
			{
				$color = 'purple';
			}
			
			$pprod = "<strong><font color=\"$color\">$perc %</font></strong>";

			$perc = @Ceil(($row['ad_prod']/$row['u_total_in'])*100);
			
			if($perc > 70)
			{
				$color = 'green';
			}
			elseif($perc > 50)
			{
				$color = 'orange';
			}
			elseif($perc > 25)
			{
				$color = 'red';
			}
			else
			{
				$color = 'purple';
			}
			
			$bprod = "<strong><font color=\"$color\">$perc %</font></strong>";
			
			echo "\n<tr id=\"rows\">
    <td id=\"title\">{$row['name']}</td>
    <td><a href=\"http://{$row['url']}\">http://{$row['url']}</a></td>
    <td>$prod</td>
    <td>$bprod</td>
    <td>$pprod</td>
    <td><a href=\"index.php?ctr=management&amp;act=incoming_hits&amp;site_id={$row['site_id']}\">In</a> | <a href=\"index.php?ctr=management&amp;act=outgoing_hits&amp;site_id={$row['site_id']}\">Out</a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_site&amp;id=$row[site_id]\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_site&amp;id=$row[site_id]&amp;return_url=$return_url\" onclick=\"return confirm('Are you sure you want to delete this site?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=management&amp;act=view_sites_productivity&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=management&amp;act=view_sites_productivity&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function incoming_hits()
	{
		$this->rknclass->page_title='Incoming Hits';

		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page();
		$this->rknclass->pager->limit=100;
		
		if(empty($this->rknclass->get['site_id']) || !ctype_digit((string)$this->rknclass->get['site_id']))
		{
			$this->rknclass->db->query("SELECT count(hit_id) FROM " . TBLPRE . "incoming_hits");
		}
		else
		{
			$this->rknclass->db->query("SELECT url FROM " . TBLPRE . "sites WHERE site_id='{$this->rknclass->get['site_id']}' LIMIT 1");
			
			if($this->rknclass->db->num_rows() < 1)
			{
				exit($this->rknclass->global_tpl->webmasters_error('Invalid site id!'));
			}
			else
			{
				$site_url = $this->rknclass->db->result();
			}
			
			$this->rknclass->db->query("SELECT count(hit_id) FROM " . TBLPRE . "incoming_hits WHERE site_url='$site_url'");
		}
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->global_tpl->admin_header();
		
		echo '
        <div class="page-title">Today\'s Incoming Traffic</div>
        
 <table id="listings" cellpadding="1" cellspacing="1">
  <tr id="columns">
    <th scope="col">IP Address</th>
    <th scope="col">Country</th>
    <th scope="col">Referrer</th>
    <th scope="col">Entrance URL</th>
	<th scope="col">Time</th>
  </tr>';
  		if(empty($this->rknclass->get['site_id']) || !ctype_digit((string)$this->rknclass->get['site_id']))
  		{
			$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "incoming_hits ORDER BY hit_id DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		else
		{
			$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "incoming_hits WHERE site_url='$site_url' ORDER BY hit_id DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		while($row=$this->rknclass->db->fetch_array($result))
		{
			$countries = $this->rknclass->utils->get_geoip_data($row['user_ip']);
			
			$country = "<img src=\"{$this->rknclass->settings['site_url']}/flags/{$countries['country_code']}.gif\" alt=\"{$countries['country_name']}\" title=\"{$countries['country_name']}\" width=\"30\" height=\"18\"/>";
			echo "<tr id=\"rows\">
    <td>{$row['user_ip']}</td>
    <td>$country</td>
    <td><a href=\"{$row['ref_url']}\" target=\"_blank\">" . wordwrap($row['ref_url'], 35, '<br />', true) . "</a></td>
	<td>" . wordwrap($row['entrance_url'], 35, '<br />', true) . "</td>
	<td>" . date('H:i:s', $row['time']) . "</td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		
		if(!isset($site_url))
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=incoming_hits&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=incoming_hits&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		else
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=incoming_hits&amp;site_id=' . $this->rknclass->get['site_id'] . '&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=incoming_hits&amp;site_id=' . $this->rknclass->get['site_id'] . '&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}

	public function outgoing_hits()
	{
		$this->rknclass->page_title='Outgoing Hits';

		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page();
		$this->rknclass->pager->limit=100;
		
		if(empty($this->rknclass->get['site_id']) || !ctype_digit((string)$this->rknclass->get['site_id']))
		{
			$this->rknclass->db->query("SELECT count(hit_id) FROM " . TBLPRE . "outgoing_hits");
		}
		else
		{
			$this->rknclass->db->query("SELECT url FROM " . TBLPRE . "sites WHERE site_id='{$this->rknclass->get['site_id']}' LIMIT 1");
			
			if($this->rknclass->db->num_rows() < 1)
			{
				exit($this->rknclass->global_tpl->webmasters_error('Invalid site id!'));
			}
			else
			{
				$site_url = $this->rknclass->db->result();
			}
			$this->rknclass->db->query("SELECT count(hit_id) FROM " . TBLPRE . "outgoing_hits WHERE site_url='$site_url'");
		}
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$this->rknclass->global_tpl->admin_header();
		
		echo '
        <div class="page-title">Today\'s Outgoing Traffic</div>
        
 <table id="listings" cellpadding="1" cellspacing="1">
  <tr id="columns">
    <th scope="col">IP Address</th>
    <th scope="col">Country</th>
    <th scope="col">Referrer</th>
    <th scope="col">Plug Destination</th>
	<th scope="col">Time</th>
  </tr>';
  		if(empty($this->rknclass->get['site_id']) || !ctype_digit((string)$this->rknclass->get['site_id']))
  		{
			$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "outgoing_hits ORDER BY hit_id DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		else
		{
			$result=$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "outgoing_hits WHERE site_url='$site_url' ORDER BY hit_id DESC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
		}
		while($row=$this->rknclass->db->fetch_array($result))
		{
			$countries = $this->rknclass->utils->get_geoip_data($row['user_ip']);
			
			$country = "<img src=\"{$this->rknclass->settings['site_url']}/flags/{$countries['country_code']}.gif\" alt=\"{$countries['country_name']}\" title=\"{$countries['country_name']}\" width=\"30\" height=\"18\"/>";
			echo "<tr id=\"rows\">
    <td>{$row['user_ip']}</td>
    <td>$country</td>
    <td><a href=\"{$row['ref_url']}\" target=\"_blank\">" . wordwrap($row['ref_url'], 35, '<br />', true) . "</a></td>
	<td>" . wordwrap($row['exit_url'], 35, '<br />', true) . "</td>
	<td>" . date('H:i:s', $row['time']) . "</td>
  </tr>";
		}
		echo '</table>';
		echo '<div id="pagination">';
		
		if(!isset($site_url))
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=outgoing_hits&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=outgoing_hits&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		else
		{
			if($this->pager_data['previous'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=outgoing_hits&amp;site_id=' . $this->rknclass->get['site_id'] . '&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
			}
			if($this->pager_data['next'] !== false)
			{
				echo '<a href="index.php?ctr=management&amp;act=outgoing_hits&amp;site_id=' . $this->rknclass->get['site_id'] . '&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
			}
		}
		echo '</div>';
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function add_site()
	{
		$this->rknclass->page_title='Add a new site';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Add a new website');
		$this->rknclass->form->set_action('index.php?ctr=management&amp;act=process_new_site');
		$this->rknclass->form->add_input('site_name', 'input', 'Website Title', 'Enter the name/title of the website. This is displayed to other webmasters, so please enter something appropriate. This value must be unique');
		$this->rknclass->form->add_input('site_url', 'input', 'Website url', 'Enter the url of the website. This <u>must</u> start with <strong>http://</strong>', 'http://');

		$users = "\n";
		$this->rknclass->db->query("SELECT user_id,username FROM " . TBLPRE . "users ORDER BY username ASC");
		while($row = $this->rknclass->db->fetch_array())
		{
			$users .= "<option value=\"{$row['user_id']}\"" . ($row['user_id'] == $this->rknclass->user['user_id'] ? ' SELECTED' : '') . ">{$row['username']}</option>\n";
		}
		
		$this->rknclass->form->add_input('owner', 'dropdown', 'Owner', 'Please select which user id you would like to assign ownership rights to for this site', $users);
		
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function process_new_site()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->post['site_name'] == '' OR $this->rknclass->post['site_url'] == '')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		$url=$this->rknclass->utils->rkn_url_parser($this->rknclass->post['site_url']);
		
		if($url === false)
		{
			exit($this->rknclass->form->ajax_error('Invalid url: urls must start with http://!'));
		}
		
		if($this->rknclass->utils->valid_url_structure($this->rknclass->post['site_url']) !== true)
		{
			exit($this->rknclass->form->ajax_error('Invalid url: urls must start with http://!'));
		}
		
	    $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "banned_sites WHERE url='{$url}'");
		if($this->rknclass->db->num_rows() > 0 AND $this->rknclass->db->result() > 0)
		{
		    exit($this->rknclass->form->ajax_error('This site is banned from submitting!'));
		}
		
		$this->rknclass->db->query("SELECT url,owner FROM " . TBLPRE . "sites WHERE url='$url' OR name='{$this->rknclass->post['site_name']}'");
		
		$assign_owner = false;
		if($this->rknclass->db->num_rows()>0)
		{
			$row = $this->rknclass->db->fetch_array();
			
			if($row['owner'] > 0)
			{
				exit($this->rknclass->form->ajax_error('This site has already been added by a user to our trade system!'));
			}
			else
			{
				$assign_owner = true;
			}
		}
		
		$this->rknclass->db->query("SELECT user_id FROM " . TBLPRE . "users WHERE user_id='{$this->rknclass->post['owner']}' LIMIT 1");
		if($this->rknclass->db->num_rows() < 1)
		{
			exit($this->rknclass->form->ajax_error('User not found!'));
		}
		
		if($assign_owner === false)
		{
			$query=$this->rknclass->db->build_query(array('insert' => 'sites',
		                                              'set' => array('url' => $url, 
													                 'name' => $this->rknclass->post['site_name'],
											                         'owner' => $this->rknclass->post['owner'],
																	 'approved' => $this->rknclass->settings['trade_default_status'],
																	 'joined' => time())));
		}
		else
		{
			$query=$this->rknclass->db->build_query(array('update' => 'sites',
		                                              'set' => array('name' => $this->rknclass->post['site_name'],
											                         'owner' => $this->rknclass->post['owner'],
																	 'approved' => $this->rknclass->settings['trade_default_status'],
																	 'joined' => time()),
		                                              'where' => array('url' => $url),
													  'limit' => '1'));			
		}
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "users SET total_sites=total_sites+1 WHERE user_id='". $this->rknclass->post['owner'] . "'");
		$this->rknclass->db->query($query);
		$this->rknclass->form->ajax_success('Successfully added site!');
	}
	
	public function i18n_settings()
	{
		$listing_types = unserialize($this->rknclass->settings['listing_types']);
		
		if(!is_array($listing_types))
		{
			$listing_types = array('');
		}
		
		$translate_chars = unserialize($this->rknclass->settings['url_translate_chars']);
		$translate_chars_value = '';
		
		foreach($translate_chars as $original => $new)
		{
		    $translate_chars_value .= htmlentities($original, ENT_NOQUOTES, 'UTF-8') . "|{$new}\n";
		}

		$utf8 = '<option value="0">No</option><option value="1"' . ($this->rknclass->settings['utf8_support'] == '1' ? ' SELECTED' : '') . '>Yes</option>';
		$this->rknclass->page_title='Manage Internationalisation';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Manage Internationalisation');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=i18n_settings');
		$this->rknclass->form->add_input('default_timezone', 'input', 'Default Timezone', 'This setting controls Predators timezone, which affects all date and time related features of the script, including crons.<br /><br />See: <a href="http://php.net/timezones" target="_blank">http://php.net/timezones</a> for more information on supported timezones', $this->rknclass->settings['default_timezone']);
		$this->rknclass->form->add_input('date_format', 'input', 'Date Format', 'Please enter the format you would like your content dates to appear in.<br /><br />See: <a href="http://php.net/date" target="_blank">http://php.net/date</a> for more information', $this->rknclass->settings['date_format']);
		$this->rknclass->form->add_input('utf8_support', 'dropdown', 'Basic Unicode Support (UTF-8)', 'If enabled, this setting adds UTF-8 character support to your content titles and descriptions.<br /><br /><strong>If your site is primarily in English, it is not recommended to enable this setting.</strong><br /><br /><strong><font color="red">This setting will change some MySQL tables field collation!</font></strong>', $utf8);
		$this->rknclass->form->add_input('url_translate_chars', 'textarea', 'URL Character Translation', 'This feature allows you to translate certain characters in your SEO urls, seperated via |. If using unicode support, you will need to translate your language\'s characters to their equivilant English / ASCII character. To remove a character rather than translating it, do not enter a character after the |.<br /><br /><strong>Example:</strong><br />&#209;|n<br />&#214;|o', $translate_chars_value);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function view_banned_sites()
	{	
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "banned_sites ORDER BY url ASC");
		
		if($this->rknclass->db->num_rows() < 1)
		{
		    exit($this->rknclass->global_tpl->admin_error('No banned sites found!'));
		}

		$this->rknclass->page_title='Manage Banned Sites';
		
		$this->rknclass->global_tpl->admin_header();
		echo "<div class=\"page-title\">Manage Banned Sites</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">Site Url</th>
    <th scope=\"col\">Date of Ban</th>
    <th scope=\"col\">Delete</th>
  </tr>";
		
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "\n<tr id=\"rows\">
    <td id=\"title\">{$row['url']}</td>
    <td>" . date('jS M Y', $row['ban_date']) . "</td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_site_ban&amp;id={$row['ban_id']}\" onclick=\"return confirm('Are you sure you want to remove this ban?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function sitemap_generator()
	{
		$this->rknclass->page_title='Sitemap Generator';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form('Google Sitemap Generator');
		$this->rknclass->form->ajax=false;
		$this->rknclass->form->set_action('index.php?ctr=management&amp;act=create_sitemap');
				
		$range = '';
		foreach(range('0.0', '1.0', '0.1') as $number)
		{
		    if(count(explode('.', $number)) < 2)
		    {
		        $number .= '.0';
		    }
		    
		    $range .= "<option value=\"{$number}\"" . ($number == '0.5' ? ' SELECTED' : '') . ">{$number}</option>\n";
		}

		$this->rknclass->form->add_input('video_priority', 'dropdown', 'Video Priority', 'Please select the priority for video content.', $range);
		$this->rknclass->form->add_input('blog_priority', 'dropdown', 'Blog Entry Priority', 'Please select the priority for blog entries.', $range);
		$this->rknclass->form->add_input('include_plugs', 'dropdown', 'Include Plugs', 'Please select whether you would like plugs to be included in your sitemap. For most users, this should be set as "No"', '<option value="0">No</option><option value="1">Yes</option>');
		$this->rknclass->form->add_input('plug_priority', 'dropdown', 'Plugs Priority', 'If you selected yes to the previous setting, please select the priority for your plugs.', $range);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function create_sitemap()
	{
	    if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->global_tpl->admin_error('This feature is disabled in the demo!'));
	    }
	    
	    $range = range('0.0', '1.0', '0.1');
	    
	    foreach($range as $number)
	    {
	    	if(count(explode('.', $number)) < 2)
		    {
		        $number .= '.0';
		    }
		    
	        $new_range[] = (string) $number;
	    }
	    
	    $range = $new_range;
	    unset($new_range);
	    
	    foreach(array('plug', 'blog', 'video') as $type)
	    {
	        if(!isset($this->rknclass->post[$type . '_priority']) || in_array($this->rknclass->post[$type . '_priority'], $range) === false)
	        {
	            exit($this->rknclass->global_tpl->admin_error('Invalid form data supplied!'));
	        }
	    }
	    
	    if(!isset($this->rknclass->post['include_plugs']) || in_array($this->rknclass->post['include_plugs'], array(0,1)) === false)
	    {
	        exit;
	        exit($this->rknclass->global_tpl->admin_error('Invalid form data supplied!'));
	    }
	    
	    header("Content-Type: text/xml");
	    header("Content-Disposition: attachment; filename=\"predator_sitemap.xml\";");
	    
	    $latest_content = date('Y-m-d', $this->rknclass->db->result($this->rknclass->db->query("SELECT posted FROM " . TBLPRE . "plugs WHERE approved='1' AND posted<" . time() . " ORDER BY posted DESC LIMIT 1")));
	    echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{$this->rknclass->settings['site_url']}/</loc>
    <lastmod>{$latest_content}</lastmod>
    <changefreq>hourly</changefreq>
    <priority>0.8</priority>
  </url>
XML;

        if($this->rknclass->post['include_plugs'] == '1')
        {
            $types = "('1','2','3','5')";
        }
        else
        {
            $types = "('2','3','5')";
        }
        
        $this->rknclass->db->query("SELECT plug_id,posted,type,title FROM " . TBLPRE . "plugs WHERE approved='1' AND posted < " . time() . " AND type IN{$types} ORDER BY posted DESC");
        while($row = $this->rknclass->db->fetch_array())
        {
            switch($row['type'])
            {
                case '1':
                    $type = 'plug';
                    break;
                case '2':
                case '3':
                    $type = 'video';
                    break;
                case '5':
                    $type = 'blog';
                    break;
                default:
                    $type = 'video';
                    break;
            }
            $priority = $this->rknclass->post[$type . '_priority'];
            $lastmod  = date('Y-m-d', $row['posted']);
            $url      = htmlentities($this->rknclass->settings['site_url'] . '/' . $row['plug_id'] . '/' . $this->rknclass->utils->url_ready($row['title']) . '.html', ENT_QUOTES, 'UTF-8');
            echo "\n" . <<<XML
   <url>
    <loc>{$url}</loc>
    <lastmod>{$lastmod}</lastmod>
    <changefreq>never</changefreq>
    <priority>{$priority}</priority>
   </url>
XML;
        }
        
        echo "\n</urlset>";
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
		        
		        $this->rknclass->db->query("SHOW COLUMNS FROM " . TBLPRE . "sites");
		        $exists = false;
		        while($row = $this->rknclass->db->fetch_array())
		        {
		            if($row['Field'] == $field)
		            {
		                $exists = true;
		                break;
		            }
		        }
		        
		        if($exists === false AND in_array($field, array('ratio', 'credits')) === false)
		        {
		            exit($this->rknclass->global_tpl->admin_error('Invalid field ordering data!'));
		        }
		        
		        $order = strtolower($order);
		        $order_url = "&amp;order_by={$field},{$order}";
		        $order = "{$field} {$order}";
		    }
		}
	}
	
	public function ffmpeg_settings()
	{
		$settings = @unserialize($this->rknclass->settings['ffmpeg_settings']);
		
		if(!is_array($settings))
		{
			$settings = array('');
		}
		
		$this->rknclass->page_title='Manage FFMPEG Configuration';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->form->new_form('Manage FFMPEG Configuration');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=ffmpeg_settings');
		$this->rknclass->form->add_input('ffmpeg_enabled', 'dropdown', 'FFMPEG Enabled', 'Please select whether or not you would like to enable FFMPEG video conversion.', '<option value="0">No</option><option value="1"' . ($settings['enabled'] == '1' ? ' SELECTED' : '') . '>Yes</option>');
		$this->rknclass->form->add_input('ffmpeg_location', 'input', 'FFMPEG Location', 'Please enter the full/absolute path to the FFMPEG binary.<br /><br />Eg /usr/bin/ffmpeg<br /><br />If using an external media/video server, this path will apply to it.', $settings['binary_path']);
        $this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function add_acp_restrictions()
	{
	    $this->rknclass->db->query("SELECT group_id,name FROM " . TBLPRE . "groups WHERE group_id != '4' AND is_admin = '1' AND is_restricted = '0' ORDER BY name ASC");
	    
	    if($this->rknclass->db->num_rows() < 1)
	    {
	        exit($this->rknclass->global_tpl->admin_error('There are no admin groups available to restrict!'));
	    }
	    
	    $groups = '';
	    while($row = $this->rknclass->db->fetch_array())
	    {
	        $groups .= "<option value=\"{$row['group_id']}\">{$row['name']}</option>\n";
	    }
	    
		$this->rknclass->page_title='Add admin restrictions';
		
		$this->rknclass->global_tpl->admin_header();
		
		$this->rknclass->form->new_form('Add group restrictions');
		$this->rknclass->form->ajax = false;
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=add_acp_restrictions');
		
		$field = '<option value="0">No</option><option value="1">Yes</option>';
		
		$this->rknclass->form->add_input('group_id', 'dropdown', 'Group Name', 'Please select the group you wish to add acp restrictions to', $groups);
		$this->rknclass->form->add_input('add_plug', 'dropdown', 'Add Plugs', 'Please select whether or not users of this group can add plugs via the acp', $field);
		$this->rknclass->form->add_input('edit_plugs', 'dropdown', 'Edit Plugs', 'Please select whether or not users of this group can edit plugs via the acp', $field);
		$this->rknclass->form->add_input('add_hvideo', 'dropdown', 'Add Hosted Videos', 'Please select whether or not users of this group can add hosted videos via the acp', $field);
		$this->rknclass->form->add_input('add_evideo', 'dropdown', 'Add Embedded Videos', 'Please select whether or not users of this group can add embedded videos via the acp', $field);
		$this->rknclass->form->add_input('edit_videos', 'dropdown', 'Edit Videos', 'Please select whether or not users of this group can edit videos via the acp.<br /><br /><strong>Users of this group will only be able to edit videos of the type they are permitted to add</strong>', $field);
		$this->rknclass->form->add_input('add_blog', 'dropdown', 'Add Blog Articles', 'Please select whether or not users of this group can add blog articles via the acp', $field);
		$this->rknclass->form->add_input('edit_blogs', 'dropdown', 'Edit Blog Articles', 'Please select whether or not users of this group can edit blog articles via the acp', $field);
		$this->rknclass->form->add_input('own_content', 'dropdown', 'Management Restriction', 'If set to yes, users of this group can only edit/delete their own content, and not content submitted by other users/admins', $field);
		
		$this->rknclass->form->process();
		
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function edit_group_restrictions()
	{
	    if(empty($this->rknclass->get['id']) || !ctype_digit($this->rknclass->get['id']))
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid group id!'));
	    }
	    
	    $this->rknclass->db->query("SELECT * FROM " . TBLPRE . "acp_restrictions WHERE group_id='{$this->rknclass->get['id']}'");
	    
	    if($this->rknclass->db->num_rows() < 1)
	    {
	        exit($this->rknclass->global_tpl->admin_error('Invalid group id!'));
	    }
	    
	    $row = $this->rknclass->db->fetch_array();
	    
		$this->rknclass->page_title='Edit admin restrictions';
		
		$this->rknclass->global_tpl->admin_header();
		
		$this->rknclass->form->new_form('Edit group restrictions');
		$this->rknclass->form->ajax = false;
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=edit_group_restrictions&amp;id=' . $this->rknclass->get['id']);
		
		$field = array();
		
		$fields = array('add_plug', 'edit_plugs', 'add_hvideo', 'add_evideo', 'edit_videos', 'add_blog', 'edit_blogs', 'own_content');
		foreach($fields as $col)
		{
		    $field[$col] = '<option value="0">No</option><option value="1"' . ($row[$col] == '1' ? ' SELECTED' : '') . '>Yes</option>';
		}
		
		$this->rknclass->form->add_input('add_plug', 'dropdown', 'Add Plugs', 'Please select whether or not users of this group can add plugs via the acp', $field['add_plug']);
		$this->rknclass->form->add_input('edit_plugs', 'dropdown', 'Edit Plugs', 'Please select whether or not users of this group can edit plugs via the acp', $field['edit_plugs']);
		$this->rknclass->form->add_input('add_hvideo', 'dropdown', 'Add Hosted Videos', 'Please select whether or not users of this group can add hosted videos via the acp', $field['add_hvideo']);
		$this->rknclass->form->add_input('add_evideo', 'dropdown', 'Add Embedded Videos', 'Please select whether or not users of this group can add embedded videos via the acp', $field['add_evideo']);
		$this->rknclass->form->add_input('edit_videos', 'dropdown', 'Edit Videos', 'Please select whether or not users of this group can edit videos via the acp.<br /><br /><strong>Users of this group will only be able to edit videos of the type they are permitted to add</strong>', $field['edit_videos']);
		$this->rknclass->form->add_input('add_blog', 'dropdown', 'Add Blog Articles', 'Please select whether or not users of this group can add blog articles via the acp', $field['add_blog']);
		$this->rknclass->form->add_input('edit_blogs', 'dropdown', 'Edit Blog Articles', 'Please select whether or not users of this group can edit blog articles via the acp', $field['edit_blogs']);
		$this->rknclass->form->add_input('own_content', 'dropdown', 'Management Restriction', 'If set to yes, users of this group can only edit/delete their own content, and not content submitted by other users/admins', $field['own_content']);
		
		$this->rknclass->form->process();
		
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function view_acp_restrictions()
	{
		$this->rknclass->db->query("SELECT group_id,name FROM " . TBLPRE . "groups WHERE is_admin='1' AND is_restricted='1'");
		if($this->rknclass->db->num_rows() < 1)
		{
		    exit($this->rknclass->global_tpl->admin_error('There are currently no restricted admin groups!'));
		}
		
		$this->rknclass->page_title='Viewing Admin Restrictions';
		
		$this->rknclass->global_tpl->admin_header();
		echo "<div class=\"page-title\">Admin Restrictions</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">Group Name</th>
    <th scope=\"col\">Edit</th>
    <th scope=\"col\">Delete</th>
  </tr>";
		
		while($row=$this->rknclass->db->fetch_array())
		{
			echo "\n<tr id=\"rows\">
    <td id=\"title\">{$row['name']}</td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_group_restrictions&amp;id={$row['group_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_group_restrictions&amp;id={$row['group_id']}\" onclick=\"return confirm('Are you sure you want to remove this groups admin panel restrictions?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function seo_url_settings()
	{
		$this->rknclass->page_title='Manage SEO Url Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		
		$seo = $this->rknclass->settings['seo_url_settings'];
		
		$category = '<option value="0">No</option><option value="1"' . ($seo['cat'] == '1' ? ' SELECTED' : '') . ">Yes</option>";
		
		$wseperator = '';
		
		$seperators = array('Underscore' => '_', 'Hyphen' => '-', 'Tilde' => '~');
		foreach($seperators as $name => $seperator)
		{
		    $wseperator .= "<option value=\"{$seperator}\"" . ($seperator == $seo['seperator'] ? ' SELECTED' : '') . ">{$name} {$seperator}</option>\n";
		}
		
		$case_management = '';
		$methods         = array('No character case management', 'Lowercase all', 'Uppercase first', 'Uppercase first letter of each word');
		
		foreach($methods as $key => $method)
		{
		    $case_management .= "<option value=\"{$key}\"" . ($key == $seo['case_management'] ? ' SELECTED' : '') . ">{$method}</option>\n";
		}
		
		$blacklist = '';
		
		foreach($seo['blacklist'] as $word)
		{
		    $blacklist .= "{$word}\n";
		}
		
		$this->rknclass->form->new_form('Manage SEO Url Settings');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=seo_url_settings');
		$this->rknclass->form->add_input('max_len', 'input', 'Max Length', 'Please enter the maximum number of words you\'d like in your content seo urls.<br /><br /><strong>Set to 0 for no limit!</strong>', $seo['max_len']);
		$this->rknclass->form->add_input('cat', 'dropdown', 'Category in url', 'If set to yes, your seo urls will appear like:<br /><br /><strong>http://your-site.com/Category/1234/Title.html</strong>', $category);
		$this->rknclass->form->add_input('seperator', 'dropdown', 'Word Seperator', 'Please select the character you\'d like to use to seperate words in your urls', $wseperator);
		$this->rknclass->form->add_input('case_management', 'dropdown', 'Word character case management', 'Please select the method for managing url character casing.', $case_management);
		$this->rknclass->form->add_input('blacklist', 'textarea', 'Word/Phrase Blacklist', 'Please enter any words or phrases you\'d like removed from your content seo urls.<br /><br /><strong>Seperate by new lines</strong>', $blacklist);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function submission_settings()
	{
		$this->rknclass->page_title='Manage Webmaster Submission Settings';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "settings LIMIT 1");
		$row=$this->rknclass->db->fetch_array();
		
		$settings = $this->rknclass->settings['submit_settings'];
		
		$blacklist = '';
		foreach($settings['blacklist'] as $word)
		{
		    $blacklist .= "{$word}\n";
		}
		
		$this->rknclass->form->new_form('Manage Webmaster Submission Settings');
		$this->rknclass->form->set_action('index.php?ctr=management_update&amp;act=submission_settings');
		$this->rknclass->form->add_input('title_min_words', 'input', 'Title min words', 'Please enter the minimum amount of a words a webmaster must use in titles<br /><br /><strong>Set to 0 to allow any minimum amount of words</strong>', $settings['title_min_words']);
		$this->rknclass->form->add_input('title_max_words', 'input', 'Title max words', 'Please enter the maximum amount of a words a webmaster can use in titles<br /><br /><strong>Set to 0 to allow any amount of words</strong>', $settings['title_max_words']);
		$this->rknclass->form->add_input('descr_min_words', 'input', 'Description min words', 'Please enter the minimum amount of a words a webmaster can use in a plugs description<br /><br /><strong>Set to 0 to allow any minimum amount of words</strong>', $settings['descr_min_words']);
		$this->rknclass->form->add_input('descr_max_words', 'input', 'Description max words', 'Please enter the maximum amount of a words a webmaster can use in a plugs description<br /><br /><strong>Set to 0 to allow any amount of words</strong>', $settings['descr_max_words']);
		$this->rknclass->form->add_input('blacklist', 'textarea', 'Word/Phrase Blacklist', 'Please enter any words or phrases you\'d like to prohibit webmasters from using in their titles and/or descriptions<br /><br /><strong>Seperate by new lines</strong>', $blacklist);
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function all_stats()
	{
	    $stats_dir = RKN__fullpath . 'statsdata/';
	    $years = array();
	    
	    if(@$handle = opendir($stats_dir)) {
	        while(($file = @readdir($handle)) !== false) {
	            if($file === '.' || $file === '.' || !is_dir($stats_dir . $file)) {
	                continue;
	            }
	            
	            if(preg_match('/^\d{4}$/', $file)) {
	                $years[] = (int) $file;
	            }
	        }
	        
	        rsort($years, SORT_NUMERIC);
	    }
	    
	    if(empty($years)) {
	        exit($this->rknclass->global_tpl->admin_error('No stats data could be found.<br /> Please allow up to 24 hours for data to collect.'));
	    }
	    
		$this->rknclass->page_title = 'All-time traffic stats';
		$this->rknclass->global_tpl->admin_header();
		
		echo "<div class=\"page-title\">View All-time traffic stats</div>

        <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Year</th>
         </tr>";

       		foreach($years as $year) {
       			echo "\n<tr id=\"rows\">
           <td id=\"title\"><a href=\"index.php?ctr=management&amp;act=all_stats_yearly&amp;year={$year}\">{$year}</a></td>
         </tr>";
       		}
       	
       	echo "\n</table>";
		
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function all_stats_yearly()
	{
	    if(!isset($this->rknclass->get['year']) || !ctype_digit($this->rknclass->get['year'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid year'));
	    }
	    
	    $stats_dir = RKN__fullpath . 'statsdata/' . $this->rknclass->get['year'] . '/';
	    
	    if(!is_dir($stats_dir)) {
	        exit($this->rknclass->global_tpl->admin_error('Stats could not be found!'));
	    }
	    
	    $months = array();
	    
	    if(@$handle = opendir($stats_dir)) {
	        while(($file = @readdir($handle)) !== false) {
	            if($file === '.' || $file === '.' || !is_dir($stats_dir . $file)) {
	                continue;
	            }
	            
	            if(ctype_digit($file)) {
	                $months[] = (int) $file;
	            }
	        }
	        
	        rsort($months, SORT_NUMERIC);
	    }
	    
	    if(empty($months)) {
	        exit($this->rknclass->global_tpl->admin_error('Stats could not be found!'));
	    }
	    
	    $this->rknclass->page_title = "{$this->rknclass->get['year']} Monthly Stats";
	
	    ob_start();
	    
	    $this->rknclass->global_tpl->admin_header();
	    
		echo "<div class=\"page-title\">{$this->rknclass->get['year']} Monthly Stats</div>

        <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Year</th>
           <th>Overview</th>
           <th>Top Partner</th>
           <th>Top Country</th>
         </tr>";

       		foreach($months as $month) {
       		    $month_formatted = str_pad($month, 2, '0', STR_PAD_LEFT);
       		    $time = strtotime($month_formatted . '/13/' . $this->rknclass->get['year']);
       		    $date = date('F', $time);
       		    
       		    if(!file_exists($stats_dir . $month_formatted . '/month.sqlite')) {
       		        ob_end_clean();
        	        exit($this->rknclass->global_tpl->admin_error('Stats could not be found!'));
        	    }
       		    
       		    $conn = sqlite_open($stats_dir . $month_formatted . '/month.sqlite');
       		    if(!$conn) {
       		        ob_end_clean();
       		        exit($this->rknclass->global_tpl->admin_error('Monthly stats file is corrupt!'));
       		    }
       		    $top_domain = false;
        	    
        	    $result = sqlite_query($conn, "SELECT url FROM stats ORDER BY u_total_in DESC");
        	    if(sqlite_num_rows($result) > 0) {
        	        $first = false;
        	        while($row = sqlite_fetch_array($result)) {
        	            if(!$first) {
        	                $first = $row['url'];
        	            }
        	            
        	            $url = $this->rknclass->db->escape($row['url']);
        	            
        	            $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "sites WHERE url = '{$url}' AND approved = '1' AND banned = '0' AND owner > 0");
        	            if($this->rknclass->db->result() == 1) {
        	                $top_domain = $row['url'];
        	                break;
        	            }
        	        }
        	        
        	        if(!$top_domain) {
        	            $top_domain = $first; // No approved sites so display top un-approved site instead
        	        }
        	    } else {
        	        $top_domain = 'N/A';
        	    }
        	    
        	    $result = sqlite_query($conn, "SELECT country_code, SUM(uhits) AS tuhits FROM country_stats GROUP BY country_code ORDER BY tuhits DESC LIMIT 1");
        	    if(sqlite_num_rows($result) == 1) {
        	        $row = sqlite_fetch_array($result);
        	        if($row['country_code'] !== '--') {
        	            $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row['country_code']}' LIMIT 1");
        	            $country_name = $this->rknclass->db->result();
        	        } else {
        	            $country_name = 'Unknown';
        	        }
        	        $top_country = "<a href=\"index.php?ctr=management&amp;act=all_country_stats_month&amp;year={$this->rknclass->get['year']}&amp;month={$month_formatted}\"><img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" title=\"{$country_name}\" alt=\"{$country_name}\" width=\"30\" height=\"18\" border=\"0\"/></a>";
        	    } else {
        	        $top_country = 'N/A';
        	    }
        	    
        	    sqlite_close($conn);
       		    
       			echo "\n<tr id=\"rows\">
           <td id=\"title\"><a href=\"index.php?ctr=management&amp;act=all_stats_monthly&amp;month={$month_formatted}&amp;year={$this->rknclass->get['year']}\">{$date}</a></td>
           <td><a href=\"index.php?ctr=management&amp;act=month_stats_overall&amp;month={$month_formatted}&amp;year={$this->rknclass->get['year']}\">Month Overall</a></td>
           <td>{$top_domain}</td>
           <td>{$top_country}</td>
         </tr>";
       		}
       	
       	echo "\n</table>";
       	
       	$this->rknclass->global_tpl->admin_footer();
	}
	
	public function all_stats_monthly()
	{
	    if(!isset($this->rknclass->get['month']) || !ctype_digit($this->rknclass->get['month']) || !isset($this->rknclass->get['year']) || !ctype_digit($this->rknclass->get['year'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $stats_dir = RKN__fullpath . 'statsdata/' . $this->rknclass->get['year'] . '/' . $this->rknclass->get['month'] . '/';
	    
	    if(!is_dir($stats_dir)) {
	        exit($this->rknclass->global_tpl->admin_error('Stats could not be found!'));
	    }
	    
	    $time = strtotime($this->rknclass->get['month'] . '/13/' . $this->rknclass->get['year']);
	    $month = date('F', $time);
	    
	    $days = array();
	    
	    if(@$handle = opendir($stats_dir)) {
	        while(($file = @readdir($handle)) !== false) {
	            if($file === '.' || $file === '.' || !is_file($stats_dir . $file)) {
	                continue;
	            }
	            
	            if(preg_match('/^(\d+)\.sqlite$/', $file, $matches)) {
	                $days[] = (int) $matches[1];
	            }
	        }
	        
	        rsort($days, SORT_NUMERIC);
	    }
	    
	    
	    if(empty($days)) {
	        exit($this->rknclass->global_tpl->admin_error('Stats could not be found!'));
	    }
	    
	    $this->rknclass->page_title = "{$month} {$this->rknclass->get['year']} Stats";
	
	    ob_start();
	
	    $this->rknclass->global_tpl->admin_header();
	    
		echo "<div class=\"page-title\">{$month} {$this->rknclass->get['year']} Stats</div>

        <table id=\"listings\" cellpadding=\"1\" cellspacing=\"0\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Year</th>
           <th>Top Partner</th>
           <th>Top Country</th>
         </tr>";

       		foreach($days as $day) {
       		    $stats_file = $stats_dir . $day . '.sqlite';
        	    $conn = sqlite_open($stats_file);
        	    
       		    if(!$conn) {
            	    ob_end_clean();
        	        exit($this->rknclass->global_tpl->admin_error('Unable to open stats file<br /> ' . $stats_file));
        	    }
        	    
        	    $top_domain = false;
        	    
        	    $result = sqlite_query($conn, "SELECT url FROM stats ORDER BY u_todays_in DESC");
        	    if(sqlite_num_rows($result) > 0) {
        	        $first = false;
        	        while($row = sqlite_fetch_array($result)) {
        	            if(!$first) {
        	                $first = $row['url'];
        	            }
        	            
        	            $url = $this->rknclass->db->escape($row['url']);
        	            
        	            $this->rknclass->db->query("SELECT COUNT(*) FROM " . TBLPRE . "sites WHERE url = '{$url}' AND approved = '1' AND banned = '0' AND owner > 0");
        	            if($this->rknclass->db->result() == 1) {
        	                $top_domain = $row['url'];
        	                break;
        	            }
        	        }
        	        
        	        if(!$top_domain) {
        	            $top_domain = $first; // No approved sites so display top un-approved site instead
        	        }
        	    } else {
        	        $top_domain = 'N/A';
        	    }
        	    
        	    $result = sqlite_query($conn, "SELECT country_code, SUM(uhits) AS tuhits FROM country_stats GROUP BY country_code ORDER BY tuhits DESC LIMIT 1");
        	    if(sqlite_num_rows($result) == 1) {
        	        $row = sqlite_fetch_array($result);
        	        if($row['country_code'] !== '--') {
        	            $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row['country_code']}' LIMIT 1");
        	            $country_name = $this->rknclass->db->result();
        	        } else {
        	            $country_name = 'Unknown';
        	        }
        	        $top_country = "<a href=\"index.php?ctr=management&amp;act=all_country_stats_day&amp;day={$day}\"><img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" title=\"{$country_name}\" alt=\"{$country_name}\" width=\"30\" height=\"18\" border=\"0\"/>";
        	    } else {
        	        $top_country = 'N/A';
        	    }
        	    
       		    $date = date('l jS F', $day);
       		    
       			echo "\n<tr id=\"rows\">
           <td id=\"title\"><a href=\"index.php?ctr=management&amp;act=all_stats_daily&amp;day={$day}\">{$date}</a></td>
           <td>{$top_domain}</td>
           <td>{$top_country}</td>
         </tr>";
       		}
       	
       	echo "\n</table>";
       	
       	$this->rknclass->global_tpl->admin_footer();
	}
	
	public function all_stats_daily()
	{
	    if(!isset($this->rknclass->get['day']) || !ctype_digit($this->rknclass->get['day'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $date = @date('Y/m', $this->rknclass->get['day']);
	    if(!$date) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid day specified'));
	    }
	    
	    $stats_file = RKN__fullpath . 'statsdata/' . $date . '/' . $this->rknclass->get['day'] . '.sqlite';
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $page_title = 'Statistics for ' . date('l jS F Y', $this->rknclass->get['day']);
	    $this->rknclass->page_title = $page_title;
	    $this->rknclass->global_tpl->admin_header();
	    
	    $this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
	    
	    echo "<div class=\"page-title\">{$page_title}</div>";
	    
        echo "<table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Site Url</th>
           <th scope=\"col\">Unique In</th>
           <th scope=\"col\">Unique Out</th>
           <th scope=\"col\">Raw In</th>
           <th scope=\"col\">Raw Out</th>
           <th scope=\"col\">Unique<br />{$type}</th>
           <th scope=\"col\">Plug Prod.</th>
           <th scope=\"col\">Ad Prod.</th>
           <th scope=\"col\">Top Country</th>
           <th scope=\"col\">Apr</th>
           <th scope=\"col\">Edit</th>
           <th scope=\"col\">Del</th>
         </tr>";
         
       		$result = sqlite_query($conn, "SELECT *, (u_total_in - u_total_out) AS credits FROM stats ORDER BY u_todays_in DESC");
       		while($row=sqlite_fetch_array($result))
       		{
       		    $url_clean = $this->rknclass->db->escape($row['url']);
       		    $this->rknclass->db->query("SELECT approved, owner FROM " . TBLPRE . "sites WHERE url = '{$url_clean}'");
       		    if($this->rknclass->db->num_rows() == 0) {
       		        continue;
       		    } else {
       		        $row2 = $this->rknclass->db->fetch_array();
       		        
       		        if($row2['owner'] < 1) {
       		            continue;
       		        }
       		        
       		        $row['approved'] = $row2['approved'];
       		    }
       			$ratio=$this->rknclass->utils->get_trade_by_in_out($row['u_todays_in'], $row['u_todays_out']);

       			if($this->rknclass->utils->trade_check($row['u_todays_in'], $row['u_todays_out']) === false)
       			{
       				$ratio="<font color=\"#e32c00\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
       			}
       			else
       			{
       				$ratio="<font color=\"#136f01\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
       			}

       			if(strlen($row['url']) >= 20)
       			{
       				$url = substr($row['url'], 0, 17) . '...';
       			}
       			else
       			{
       				$url = $row['url'];
       			}
       			
       			//echo $row['plug_prod'], '-', $row['u_todays_in'];
       			//exit;
       			
    			$perc = @Ceil(($row['plug_prod']/$row['u_todays_in'])*100);

    			if($perc > 70)
    			{
    				$color = 'green';
    			}
    			elseif($perc > 50)
    			{
    				$color = 'orange';
    			}
    			elseif($perc > 25)
    			{
    				$color = 'red';
    			}
    			else
    			{
    				$color = 'purple';
    			}

    			$pprod = "<strong><font color=\"$color\">$perc %</font></strong>";

    			$perc = @Ceil(($row['ad_prod']/$row['u_todays_in'])*100);

    			if($perc > 70)
    			{
    				$color = 'green';
    			}
    			elseif($perc > 50)
    			{
    				$color = 'orange';
    			}
    			elseif($perc > 25)
    			{
    				$color = 'red';
    			}
    			else
    			{
    				$color = 'purple';
    			}

    			$bprod = "<strong><font color=\"$color\">$perc %</font></strong>";
                
                $this->rknclass->db->query("SELECT site_id FROM " . TBLPRE . "sites WHERE url = '{$url_clean}' LIMIT 1");
                $row['site_id'] = $this->rknclass->db->result();
                
                $result2 = sqlite_query($conn, "SELECT country_code FROM country_stats WHERE url = '{$url_clean}' ORDER BY uhits DESC LIMIT 1");
                if(sqlite_num_rows($result2) == 1) {
                    $row3 = sqlite_fetch_array($result2);
                    if($row3['country_code'] !== '--') {
                        $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row3['country_code']}' LIMIT 1");
        	            $country_name = $this->rknclass->db->result();
        	        } else {
        	            $country_name = 'Unknown';
        	        }
        	        
                    $top_country = "<a href=\"index.php?ctr=management&amp;act=country_stats_daily&amp;day={$this->rknclass->get['day']}&amp;id={$row['site_id']}\"><img src=\"{$this->rknclass->settings['site_url']}/flags/{$row3['country_code']}.gif\" alt=\"{$country_name}\" title=\"{$country_name}\" width=\"30\" height=\"18\" border=\"0\"/></a>";
                } else {
                    $top_country = 'N/A';
                }
                
       			echo "\n<tr id=\"rows\">
           <td id=\"title\"><a href=\"http://{$row['url']}\" target=\"_blank\" title=\"{$row['name']}\">$url</a></td>
           <td>{$row['u_todays_in']}</td>
           <td>{$row['u_todays_out']}</td>
           <td>{$row['r_todays_in']}</td>
           <td>{$row['r_todays_out']}</td>
           <td><strong>$ratio</strong></td>
           <td>{$pprod}</td>
           <td>{$bprod}</td>
           <td>{$top_country}</td>
           <td><strong>" . ($row['approved'] == '0' ? "<font color=\"#e32c00\">No" : "<font color=\"#136f01\">Yes") . "</font></strong></td>
           <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_site&amp;id={$row['site_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
           <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_site&amp;id={$row['site_id']}\" onclick=\"return confirm('Are you sure you want to delete this site?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
         </tr>";
       		}
       	
       	echo "\n</table>";
	    
	    sqlite_close($conn);
	    
	    $this->rknclass->global_tpl->admin_footer();
	}
	
	public function month_stats_overall()
	{
	    if(!isset($this->rknclass->get['month']) || !ctype_digit($this->rknclass->get['month']) || !isset($this->rknclass->get['year']) || !isset($this->rknclass->get['year'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $stats_file = RKN__fullpath . 'statsdata/' . $this->rknclass->get['year'] . '/' . $this->rknclass->get['month'] . '/month.sqlite';
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $time = strtotime($this->rknclass->get['month'] . '/13/' . $this->rknclass->get['year']);
	    $month = date('F', $time);
	    
	    $page_title = "Statistics for {$month} {$this->rknclass->get['year']}";
	    $this->rknclass->page_title = $page_title;
	    $this->rknclass->global_tpl->admin_header();
	    
	    $this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
	    
	    echo "<div class=\"page-title\">{$page_title}</div>";
	    
        echo "<table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Site Url</th>
           <th scope=\"col\">Unique In</th>
           <th scope=\"col\">Unique Out</th>
           <th scope=\"col\">Raw In</th>
           <th scope=\"col\">Raw Out</th>
           <th scope=\"col\">Unique<br />{$type}</th>
           <th scope=\"col\">Plug Prod.</th>
           <th scope=\"col\">Ad Prod.</th>
           <th scope=\"col\">Top Country</th>
           <th scope=\"col\">Apr</th>
           <th scope=\"col\">Edit</th>
           <th scope=\"col\">Del</th>
         </tr>";
         
       		$result = sqlite_query($conn, "SELECT *, (u_total_in - u_total_out) AS credits FROM stats ORDER BY u_total_in DESC");
       		while($row=sqlite_fetch_array($result))
       		{
       		    $url_clean = $this->rknclass->db->escape($row['url']);
       		    $this->rknclass->db->query("SELECT approved, owner FROM " . TBLPRE . "sites WHERE url = '{$url_clean}'");
       		    if($this->rknclass->db->num_rows() == 0) {
       		        continue;
       		    } else {
       		        $row2 = $this->rknclass->db->fetch_array();
       		        
       		        if($row2['owner'] < 1) {
       		            continue;
       		        }
       		        
       		        $row['approved'] = $row2['approved'];
       		    }
       			$ratio=$this->rknclass->utils->get_trade_by_in_out($row['u_total_in'], $row['u_total_out']);

       			if($this->rknclass->utils->trade_check($row['u_total_in'], $row['u_total_out']) === false)
       			{
       				$ratio="<font color=\"#e32c00\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
       			}
       			else
       			{
       				$ratio="<font color=\"#136f01\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
       			}

       			if(strlen($row['url']) >= 20)
       			{
       				$url = substr($row['url'], 0, 17) . '...';
       			}
       			else
       			{
       				$url = $row['url'];
       			}

    			$perc = @Ceil(($row['plug_prod']/$row['u_total_in'])*100);

    			if($perc > 70)
    			{
    				$color = 'green';
    			}
    			elseif($perc > 50)
    			{
    				$color = 'orange';
    			}
    			elseif($perc > 25)
    			{
    				$color = 'red';
    			}
    			else
    			{
    				$color = 'purple';
    			}

    			$pprod = "<strong><font color=\"$color\">$perc %</font></strong>";

    			$perc = @Ceil(($row['ad_prod']/$row['u_todays_in'])*100);

    			if($perc > 70)
    			{
    				$color = 'green';
    			}
    			elseif($perc > 50)
    			{
    				$color = 'orange';
    			}
    			elseif($perc > 25)
    			{
    				$color = 'red';
    			}
    			else
    			{
    				$color = 'purple';
    			}

    			$bprod = "<strong><font color=\"$color\">$perc %</font></strong>";
                
                $this->rknclass->db->query("SELECT site_id FROM " . TBLPRE . "sites WHERE url = '{$url_clean}' LIMIT 1");
                $row['site_id'] = $this->rknclass->db->result();
                
                $result2 = sqlite_query($conn, "SELECT country_code FROM country_stats WHERE url = '{$url_clean}' ORDER BY uhits DESC LIMIT 1");
                if(sqlite_num_rows($result2) == 1) {
                    $row3 = sqlite_fetch_array($result2);
                    if($row3['country_code'] !== '--') {
                        $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row3['country_code']}' LIMIT 1");
        	            $country_name = $this->rknclass->db->result();
        	        } else {
        	            $country_name = 'Unknown';
        	        }
        	        
                    $top_country = "<a href=\"index.php?ctr=management&amp;act=country_stats_month&amp;month={$this->rknclass->get['month']}&amp;year={$this->rknclass->get['year']}&amp;id={$row['site_id']}\"><img src=\"{$this->rknclass->settings['site_url']}/flags/{$row3['country_code']}.gif\" alt=\"{$country_name}\" title=\"{$country_name}\" width=\"30\" height=\"18\" border=\"0\"/></a>";
                } else {
                    $top_country = 'N/A';
                }
                
       			echo "\n<tr id=\"rows\">
           <td id=\"title\"><a href=\"http://{$row['url']}\" target=\"_blank\" title=\"{$row['name']}\">$url</a></td>
           <td>{$row['u_total_in']}</td>
           <td>{$row['u_total_out']}</td>
           <td>{$row['r_total_in']}</td>
           <td>{$row['r_total_out']}</td>
           <td><strong>$ratio</strong></td>
           <td>{$pprod}</td>
           <td>{$bprod}</td>
           <td>{$top_country}</td>
           <td><strong>" . ($row['approved'] == '0' ? "<font color=\"#e32c00\">No" : "<font color=\"#136f01\">Yes") . "</font></strong></td>
           <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management&amp;act=edit_site&amp;id={$row['site_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
           <td><a href=\"{$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?ctr=management_update&amp;act=del_site&amp;id={$row['site_id']}\" onclick=\"return confirm('Are you sure you want to delete this site?');\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
         </tr>";
       		}
       	
       	echo "\n</table>";
	    
	    sqlite_close($conn);
	    
	    $this->rknclass->global_tpl->admin_footer();
	}
	
	public function country_stats_daily()
	{
	    if(!isset($this->rknclass->get['day']) || !ctype_digit($this->rknclass->get['day']) || !isset($this->rknclass->get['id']) || !ctype_digit($this->rknclass->get['id'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $date = @date('Y/m', $this->rknclass->get['day']);
	    if(!$date) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid day specified'));
	    }
	    
	    $stats_file = RKN__fullpath . 'statsdata/' . $date . '/' . $this->rknclass->get['day'] . '.sqlite';
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $this->rknclass->db->query("SELECT url FROM " . TBLPRE . "sites WHERE site_id = '{$this->rknclass->get['id']}' LIMIT 1");
	    if($this->rknclass->db->num_rows() != 1) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $url = $this->rknclass->db->result();
	    
	    $page_title = $url . ' geo stats for ' . date('jS F Y');
	    $this->rknclass->page_title = $page_title;
	    $this->rknclass->global_tpl->admin_header();
	    
	    $this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
	    
	    echo "<div class=\"page-title\">{$page_title}</div>";
	    
        echo "<table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Flag</th>
           <th scope=\"col\">Country Name</th>
           <th scope=\"col\">Unique Hits</th>
           <th scope=\"col\">Perc. %</th>
         </tr>";
            $result = sqlite_query($conn, "SELECT SUM(uhits) FROM country_stats WHERE url = '{$url}'");
            $row = sqlite_fetch_array($result);
            $total = $row[0];
            
       		$result = sqlite_query($conn, "SELECT * FROM country_stats WHERE url = '{$url}' ORDER BY uhits DESC");
       		while($row = sqlite_fetch_array($result))
       		{
       		    if($row['country_code'] !== '--') {
       		        $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row['country_code']}' LIMIT 1");
    	            $country_name = $this->rknclass->db->result();
    	        } else {
    	            $country_name = 'Unknown';
    	        }
    	        
    	        $perc = sprintf('%.2f', ($row['uhits'] / $total) * 100);
    	        
                $country_flag = "<img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" alt=\"{$country_name}\" title=\"{$country_name}\" width=\"30\" height=\"18\"/>";            
       			echo "\n<tr id=\"rows\">
           <td id=\"title\">{$country_flag}</td>
           <td>{$country_name}</td>
           <td>{$row['uhits']}</td>
           <td>{$perc} %</td>
         </tr>";
       		}
       	
       	echo "\n</table>";
	    
	    sqlite_close($conn);
	    
	    $this->rknclass->global_tpl->admin_footer();
	}
	
	public function country_stats_month()
	{
	    if(!isset($this->rknclass->get['month']) || !ctype_digit($this->rknclass->get['month']) || !isset($this->rknclass->get['year']) || !ctype_digit($this->rknclass->get['year']) || !isset($this->rknclass->get['id']) || !ctype_digit($this->rknclass->get['id'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $stats_file = RKN__fullpath . 'statsdata/' . $this->rknclass->get['year'] . '/' . $this->rknclass->get['month'] . '/month.sqlite';
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $time = strtotime($this->rknclass->get['month'] . '/13/' . $this->rknclass->get['year']);
	    $month = date('F', $time);
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $this->rknclass->db->query("SELECT url FROM " . TBLPRE . "sites WHERE site_id = '{$this->rknclass->get['id']}' LIMIT 1");
	    if($this->rknclass->db->num_rows() != 1) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $url = $this->rknclass->db->result();
	    
	    $page_title = $url . ' geo stats for ' . date('F Y', $time);
	    $this->rknclass->page_title = $page_title;
	    $this->rknclass->global_tpl->admin_header();
	    
	    $this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
	    
	    echo "<div class=\"page-title\">{$page_title}</div>";
	    
        echo "<table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Flag</th>
           <th scope=\"col\">Country Name</th>
           <th scope=\"col\">Unique Hits</th>
           <th scope=\"col\">Perc. %</th>
         </tr>";
            $result = sqlite_query($conn, "SELECT SUM(uhits) FROM country_stats WHERE url = '{$url}'");
            $row = sqlite_fetch_array($result);
            $total = $row[0];
            
       		$result = sqlite_query($conn, "SELECT * FROM country_stats WHERE url = '{$url}' ORDER BY uhits DESC");
       		while($row = sqlite_fetch_array($result))
       		{
       		    if($row['country_code'] !== '--') {
       		        $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row['country_code']}' LIMIT 1");
    	            $country_name = $this->rknclass->db->result();
    	        } else {
    	            $country_name = 'Unknown';
    	        }
    	        
    	        $perc = sprintf('%.2f', ($row['uhits'] / $total) * 100);
    	        
                $country_flag = "<img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" alt=\"{$country_name}\" title=\"{$country_name}\" width=\"30\" height=\"18\"/>";            
       			echo "\n<tr id=\"rows\">
           <td id=\"title\">{$country_flag}</td>
           <td>{$country_name}</td>
           <td>{$row['uhits']}</td>
           <td>{$perc} %</td>
         </tr>";
       		}
       	
       	echo "\n</table>";
	    
	    sqlite_close($conn);
	    
	    $this->rknclass->global_tpl->admin_footer();
	}
	
	public function all_country_stats_month()
	{
	    if(!isset($this->rknclass->get['month']) || !ctype_digit($this->rknclass->get['month']) || !isset($this->rknclass->get['year']) || !ctype_digit($this->rknclass->get['year'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $stats_file = RKN__fullpath . 'statsdata/' . $this->rknclass->get['year'] . '/' . $this->rknclass->get['month'] . '/month.sqlite';
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $time = strtotime($this->rknclass->get['month'] . '/13/' . $this->rknclass->get['year']);
	    $month = date('F', $time);
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $page_title = 'Geo stats for ' . date('F Y', $time);
	    $this->rknclass->page_title = $page_title;
	    $this->rknclass->global_tpl->admin_header();
	    
	    $this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
	    
	    echo "<div class=\"page-title\">{$page_title}</div>";
	    
        echo "<table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Flag</th>
           <th scope=\"col\">Country Name</th>
           <th scope=\"col\">Unique Hits</th>
           <th scope=\"col\">Perc. %</th>
         </tr>";
            $result = sqlite_query($conn, "SELECT SUM(uhits) FROM country_stats");
            $row = sqlite_fetch_array($result);
            $total = $row[0];
            
       		$result = sqlite_query($conn, "SELECT *, SUM(uhits) AS tuhits FROM country_stats GROUP BY country_code ORDER BY tuhits DESC");
       		while($row = sqlite_fetch_array($result))
       		{
       		    if($row['country_code'] !== '--') {
       		        $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row['country_code']}' LIMIT 1");
    	            $country_name = $this->rknclass->db->result();
    	        } else {
    	            $country_name = 'Unknown';
    	        }
    	        
    	        $perc = sprintf('%.2f', ($row['tuhits'] / $total) * 100);
    	        
                $country_flag = "<img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" alt=\"{$country_name}\" title=\"{$country_name}\" width=\"30\" height=\"18\"/>";            
       			echo "\n<tr id=\"rows\">
           <td id=\"title\">{$country_flag}</td>
           <td>{$country_name}</td>
           <td>{$row['tuhits']}</td>
           <td>{$perc} %</td>
         </tr>";
       		}
       	
       	echo "\n</table>";
	    
	    sqlite_close($conn);
	    
	    $this->rknclass->global_tpl->admin_footer();
	}
	
	public function all_country_stats_day()
	{
	    if(!isset($this->rknclass->get['day']) || !ctype_digit($this->rknclass->get['day'])) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid data'));
	    }
	    
	    $date = @date('Y/m', $this->rknclass->get['day']);
	    if(!$date) {
	        exit($this->rknclass->global_tpl->admin_error('Invalid day specified'));
	    }
	    
	    $stats_file = RKN__fullpath . 'statsdata/' . $date . '/' . $this->rknclass->get['day'] . '.sqlite';
	    
	    $conn = @sqlite_open($stats_file);
	    if(!$conn) {
	        exit($this->rknclass->global_tpl->admin_error('The stats file is corrupt.'));
	    }
	    
	    $page_title = 'Geo stats for ' . date('jS F Y', $this->rknclass->get['day']);
	    $this->rknclass->page_title = $page_title;
	    $this->rknclass->global_tpl->admin_header();
	    
	    $this->rknclass->settings['trade_type'] === 'credits' ? $type = 'Credits' : $type = 'Ratio';
	    
	    echo "<div class=\"page-title\">{$page_title}</div>";
	    
        echo "<table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
         <tr id=\"columns\">
           <th scope=\"col\" id=\"title\">Flag</th>
           <th scope=\"col\">Country Name</th>
           <th scope=\"col\">Unique Hits</th>
           <th scope=\"col\">Perc. %</th>
         </tr>";
            $result = sqlite_query($conn, "SELECT SUM(uhits) FROM country_stats");
            $row = sqlite_fetch_array($result);
            $total = $row[0];
            
       		$result = sqlite_query($conn, "SELECT *, SUM(uhits) AS tuhits FROM country_stats GROUP BY country_code ORDER BY tuhits DESC");
       		while($row = sqlite_fetch_array($result))
       		{
       		    if($row['country_code'] !== '--') {
       		        $this->rknclass->db->query("SELECT country_name FROM " . TBLPRE . "countries WHERE country_code = '{$row['country_code']}' LIMIT 1");
    	            $country_name = $this->rknclass->db->result();
    	        } else {
    	            $country_name = 'Unknown';
    	        }
    	        
    	        $perc = sprintf('%.2f', ($row['tuhits'] / $total) * 100);
    	        
                $country_flag = "<img src=\"{$this->rknclass->settings['site_url']}/flags/{$row['country_code']}.gif\" alt=\"{$country_name}\" title=\"{$country_name}\" width=\"30\" height=\"18\"/>";            
       			echo "\n<tr id=\"rows\">
           <td id=\"title\">{$country_flag}</td>
           <td>{$country_name}</td>
           <td>{$row['tuhits']}</td>
           <td>{$perc} %</td>
         </tr>";
       		}
       	
       	echo "\n</table>";
	    
	    sqlite_close($conn);
	    
	    $this->rknclass->global_tpl->admin_footer();
	}
}
?>