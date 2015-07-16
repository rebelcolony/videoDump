<?php

/*=====================================

Predator CMS 1.x Templates Admin
Backend Controller Class.

All of the methods below are used
by Predator to update the templates
section. All of the functions are
named after the appropriate template
name, so its pretty easy to extend
this class if you add any custom
templates to your Predator install

=======================================*/

define('RKN__admintab', 'edit_templates');

class edit_templates extends rkn_render
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
				exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
			}
		}
		
		if($this->rknclass->user['group']['is_admin'] !== '1')
		{
			exit('You must be an admin to access this area!');	
		}
		
		$this->rknclass->load_objects(array('global_tpl', 'form', 'tpl_admin'));
		
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit($this->rknclass->global_tpl->admin_error('Invalid template id'));
		}
		
		if(!is_dir(RKN__fullpath . 'cache/templates/' . $this->rknclass->get['id'] . '/'))
		{
			exit($this->rknclass->global_tpl->admin_error('The requested template cache directory could not be found! Please try rebuilding the cache from the main templates menu'));
		}

		if($this->rknclass->user['group']['is_restricted'] == '1')
		{
		    exit($this->rknclass->global_tpl->admin_error('You are not permitted to access this area!'));
		}
	}
	
	public function idx()
	{
		$this->rknclass->get['act']='header';
		$this->header();
	}
	
	public function header()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="<strong>{site url}</strong> will be replaced by your site's url<br />\n
		<strong>{site name}</strong> will be replaced by your site's name<br />\n
		<strong>{page title}</strong> will be replaced by the current page's title<br />\n
		<strong>{meta_title}</strong> will be replaced by the page's meta title<br />\n
		<strong>{meta_description}</strong> will be replaced by the page's meta description<br />\n
		<strong>{meta_keywords}</strong> will be replaced by the page's meta keywords<br />\n
		";
		
		//END REPLACEMENTS TEXT
		
		$this->rknclass->page_title='Manage Header';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit header template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data']['header'], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function index()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="<strong>{page title}</strong> will be replaced by the current page's title<br />\n
		<strong>{plugs}</strong> will be replaced by the content/plugs on your site<br />\n
		<strong>{page nav}</strong> will be replaced by the page navigation<br />\n";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Index';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit index template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function footer()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="<strong>{site url}</strong> will be replaced by your site's url<br />\n
		<strong>{site name}</strong> will be replaced by your site's name<br />\n
		<strong>{predator version}</strong> will be replaced by the current version of Predator CMS<br />\n";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Footer';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit footer template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function plugs()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="<strong>{title}</strong> will be replaced by the content's title<br />\n
		<strong>{outurl}</strong> will be replaced by the link to view the content<br />\n
		<strong>{description}</strong> will be replaced by the content's description<br />\n
		<strong>{tags}</strong> will be replaced by the content's tags<br />\n
		<strong>{views}</strong> will be replaced by the number of views the content has received<br />\n
		<strong>{posted}</strong> will be replaced by the data the plug was posted<br />\n
		<strong>{poster}</strong> will be replaced by the username of the content submitter<br />\n
		<strong>{poster_id}</strong> will be replaced by the user id of the content submitter<br />\n
		<strong>{thumb}</strong> will be replaced by the full url to the contents thumbnail<br />\n
		<strong>{category}</strong> will be replaced by the name of the contents category<br />\n
		<strong>{category_id}</strong> will be replaced by the id of the contents category<br />\n
		<strong>{category_url}</strong> will be replaced by the full url to the contents category<br />\n
		<strong>{category_num}</strong> will be replaced by the number of comments the content has received<br />\n
		<strong>{comments_url}</strong> will be replaced by the full url to the user commenting page<br />\n
		<strong>{target}</strong> can be used within the &lt;a&gt; attribute, and will be replaced by '_blank' when the content is a plug, and '_self' when the content is local (blank opens in new window, self in the same window)<br />\n
		<strong>{rating}</strong> will be replaced by the rating system being used on your site<br />\n
		<strong>{url}</strong> will be replaced by the content's original url<br />\n";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Plugs/Content Template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit plugs template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function video_page()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="<strong>{title}</strong> will be replaced by the video's title<br />\n
		<strong>{description}</strong> will be replaced by the video's description<br />\n
		<strong>{tags}</strong> will be replaced by the video's tags<br />\n
		<strong>{views}</strong> will be replaced by the number of views the video has received<br />\n
		<strong>{posted}</strong> will be replaced by the data the plug was posted<br />\n
		<strong>{poster}</strong> will be replaced by the username of the video submitter<br />\n
		<strong>{poster_id}</strong> will be replaced by the user id of the video submitter<br />\n
		<strong>{thumb}</strong> will be replaced by the full url to the videos thumbnail<br />\n
		<strong>{category}</strong> will be replaced by the name of the videos category<br />\n
		<strong>{category_id}</strong> will be replaced by the id of the videos category<br />\n
		<strong>{category_url}</strong> will be replaced by the full url to the videos category<br />\n
		<strong>{rating}</strong> will be replaced by the rating system being used on your site<br />\n
		<strong>{video}</strong> will be replaced by the video player<br />\n
		<strong>{plugs}</strong> will be replaced by your homepage plugs listings<br />\n
		<br /><br />
		<strong>{ad['html']}</strong> will be replaced by an HTML advertisement if available<br />\n
		<strong>{ad['banner']['url']}</strong> will be replaced by the full url to a banner link if available<br />\n
		<strong>{ad['banner']['image']}</strong> will be replaced by the full url to the location of a banner image if available<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Video Page Template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit video page template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function register()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{action}</strong> will be replaced by the &lt;form&gt; action attribute<font color=\"#FF0000\">*</font><br />\n
		<strong>{username}</strong> will be replaced by the username input box<font color=\"#FF0000\">*</font><br />\n
		<strong>{password}</strong> will be replaced by the password input box<font color=\"#FF0000\">*</font><br />\n
		<strong>{email}</strong> will be replaced by the email input box<font color=\"#FF0000\">*</font><br />\n
		<strong>{aim}</strong> will be replaced by the aim input box<br />\n
		<strong>{icq}</strong> will be replaced by the icq input box<br />\n
		<strong>{msn}</strong> will be replaced by the msn input box<br />\n
		<strong>{gtalk}</strong> will be replaced by the gtalk input box<br />\n
		<strong>{skype}</strong> will be replaced by the skype input box<br />\n
		<strong>{groups}</strong> will be replaced by the usergroup select box<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Registration Page';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit register template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function login()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{action}</strong> will be replaced by the &lt;form&gt; action attribute<font color=\"#FF0000\">*</font><br />\n
		<strong>{username}</strong> will be replaced by the username input box<font color=\"#FF0000\">*</font><br />\n
		<strong>{password}</strong> will be replaced by the password input box<font color=\"#FF0000\">*</font><br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Login Page';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit login template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function edit_profile()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{action}</strong> will be replaced by the &lt;form&gt; action attribute<font color=\"#FF0000\">*</font><br />\n
		<strong>{username}</strong> will be replaced by the username input box<font color=\"#FF0000\">*</font><br />\n
		<strong>{password}</strong> will be replaced by the password input box<br />\n
		<strong>{password2}</strong> will be replaced by the password confirmation input box<br />\n
		<strong>{email}</strong> will be replaced by the email input box<font color=\"#FF0000\">*</font><br />\n
		<strong>{aim}</strong> will be replaced by the aim input box<br />\n
		<strong>{icq}</strong> will be replaced by the icq input box<br />\n
		<strong>{msn}</strong> will be replaced by the msn input box<br />\n
		<strong>{gtalk}</strong> will be replaced by the gtalk input box<br />\n
		<strong>{skype}</strong> will be replaced by the skype input box<br />\n
		<strong>{groups}</strong> will be replaced by the usergroup select box<br />\n
		<strong>{avatar}</strong> will allow the user to upload an avatar<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Profile Management Page';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit profile template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
		
	public function forgot_password()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{action}</strong> will be replaced by the &lt;form&gt; action attribute<font color=\"#FF0000\">*</font><br />\n
		<strong>{username}</strong> will be replaced by the username input box<font color=\"#FF0000\">*</font><br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Forgot Password Page';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit forgot password template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function video_player_flv()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{video}</strong> will be replaced by the full url to the video location<br />\n
		<strong>{thumb}</strong> will be replaced by the full url to the video's thumbnail. Supported by some FLV players<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage .flv Player embed code';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit .flv player template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function video_player_wmv()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{video}</strong> will be replaced by the full url to the video location<br />\n";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage .wmv Player Code';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit Windows Media Player embed code");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function comments()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{title}</strong> will be replaced by the comment's title<br />\n
		<strong>{description}</strong> will be replaced by the comment's body/description<br />\n
		<strong>{poster}</strong> will be replaced by the comment poster's username<br />\n
		<strong>{poster_id}</strong> will be replaced by the comment poster's user id<br />\n
		<strong>{poster_avatar}</strong> will be replaced by the full url to the poster's avatar<br />\n
		<strong>{posted}</strong> will be replaced by the date the comment was posted<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Comments Template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit comments template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function comments_page()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{plug}</strong> will be replaced by the content's plug<br />\n
		<strong>{action}</strong> will be replaced by the &lt;form&gt; action attribute<br />\n
		<strong>{title}</strong> will allow the user to input the comment's title<br />\n
		<strong>{description}</strong> will allow the user to enter the comment's description<br />\n
		<strong>{poster}</strong> will be allow guests posters to enter a display name<br />
		<strong>{comments}</strong> will be replaced by the comments listings<br />
		<strong>{captcha[image]}</strong> will be replaced by the captcha image<br />\n
		<strong>{captcha[input]}</strong> will be replaced by the captcha input box<br />\n
		<br /><strong>Notice:</strong> If you are allowing guests to post, you must wrap the appropriate &lt;?php if{} ?&gt; blocks around the {poster} var to prevent logged-in members from seeing that field\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Comments Page Template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit comments page template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function message()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{message}</strong> will be replaced by the message/error text<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Message/Error Page';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit message page template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function search()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{action}</strong> will be replaced by the &lt;form&gt; action attribute<br />\n
		<strong>{search phrase}</strong> will be replaced by the search phrase input box<br />\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage search page template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit search page template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}

	public function blog_page()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{title}</strong> will be replaced by the blog's title<br />\n
		<strong>{poster}</strong> will be replaced by the blog poster's username<br />\n
		<strong>{poster_id}</strong> will be replaced by the blog poster's user id<br />\n
		<strong>{thumb}</strong> will be replaced by the full url to the blog's thumbnail<br />\n
		<strong>{posted}</strong> will be replaced by the date the blog entry was posted<br />\n
		<strong>{tags}</strong> will be replaced by the content's tags<br />\n
		<strong>{category}</strong> will be replaced by the name of the contents category<br />\n
		<strong>{category_id}</strong> will be replaced by the id of the contents category<br />\n
		<strong>{category_url}</strong> will be replaced by the full url to the contents category<br />\n
		<strong>{category_num}</strong> will be replaced by the number of comments the content has received<br />\n
		<strong>{comments_url}</strong> will be replaced by the full url to the user commenting page<br />\n
		<strong>{permalink}</strong> will be replaced by a permalink to your blog post<br />\n
		<strong>{entry}</strong> will be replaced by the blog's main content<br /><br />\n
		<strong>{IF_NEXT}</strong> (see below)<br />\n
		<strong>{IF_PREV}</strong> (see below) <br />\n
		<strong>{END_IF}</strong> (see below)<br />\n
		<strong>{NEXT}</strong> will be replaced by the full url to the next page<br />\n
		<strong>{PREV}</strong> will be replaced by the full url to the previous page<br />
		<br /><br /><strong>{IF} sytax:</strong><br /><br /><strong><font color=\"green\">{IF_NEXT}</font></strong><br /><em>CODE TO BE DISPLAYED WHEN THERE IS A NEXT PAGE</em><br /><strong><font color=\"green\">{END_IF}</font></strong>\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage blog page template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit blog page template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function top_frame()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="<strong>{title}</strong> will be replaced by the content's title<br />\n
		<strong>{outurl}</strong> will be replaced by the link to view the content<br />\n
		<strong>{description}</strong> will be replaced by the content's description<br />\n
		<strong>{tags}</strong> will be replaced by the content's tags<br />\n
		<strong>{views}</strong> will be replaced by the number of views the content has received<br />\n
		<strong>{posted}</strong> will be replaced by the data the plug was posted<br />\n
		<strong>{poster}</strong> will be replaced by the username of the content submitter<br />\n
		<strong>{poster_id}</strong> will be replaced by the user id of the content submitter<br />\n
		<strong>{thumb}</strong> will be replaced by the full url to the contents thumbnail<br />\n
		<strong>{category}</strong> will be replaced by the name of the contents category<br />\n
		<strong>{category_id}</strong> will be replaced by the id of the contents category<br />\n
		<strong>{category_url}</strong> will be replaced by the full url to the contents category<br />\n
		<strong>{category_num}</strong> will be replaced by the number of comments the content has received<br />\n
		<strong>{comments_url}</strong> will be replaced by the full url to the user commenting page<br />\n
		<strong>{url}</strong> will be replaced by the content's original url<br />\n";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage top-frame template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit frame template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
	
	public function pagination()
	{
		$tpl=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		
		//START REPLACEMENTS TEXT
		
		$replacements="
		<strong>{IF_NEXT}</strong> (see below)<br />\n
		<strong>{IF_PREV}</strong> (see below) <br />\n
		<strong>{END_IF}</strong> (see below)<br />\n
		<strong>{NEXT}</strong> will be replaced by the full url to the next page<br />\n
		<strong>{PREV}</strong> will be replaced by the full url to the previous page<br />
		<br /><br /><strong>{IF} sytax:</strong><br /><br /><strong><font color=\"green\">{IF_NEXT}</font></strong><br /><em>CODE TO BE DISPLAYED WHEN THERE IS A NEXT PAGE</em><br /><strong><font color=\"green\">{END_IF}</font></strong>\n
		";
		
		//END REPLACEMENTS TEXT
			
		$this->rknclass->page_title='Manage Pagination Template';
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->form->new_form("Edit pagination template");
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template&amp;tpl_name={$this->rknclass->get['act']}&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_data', 'textarea', 'Modify Template', 'The following template variables are supported in this template:<br /><br />' . $replacements, htmlspecialchars($tpl['data'][$this->rknclass->get['act']], ENT_NOQUOTES));
		$this->rknclass->form->add_input('remove_crap', 'dropdown', 'Template Compression', 'Select the template compression method to use on this template.<br /><br /><strong>Remove All Whitespace</strong> will remove excessive spaces, as well as all tabs, newlines, and unwanted data. This should only be used if PHP or javascript is not in this template, and you have a backup of the template.<br /><br />Use this feature at your own risk!', '<option value="none" SELECTED>Do not compress</option><option value="excessive">Remove Excessive Spaces</option><option value="all">Remove All Whitespace</option><option value="everything_but_new_lines">Remove All Whitespace except new lines</option>');
		$this->rknclass->form->process();
		$this->rknclass->global_tpl->admin_footer();	
	}
			
	/*=====================================================
	The method below is used for updating the templates
	author, name and description fields in the db and cache
	========================================================*/
	
	public function template_information()
	{
		$data=$this->rknclass->tpl_admin->grab_from_db($this->rknclass->get['id']);
		$info=$data['info'];
		
		$this->rknclass->pagetitle='Edit Template Information';
		
		$this->rknclass->global_tpl->admin_header();
		
		$this->rknclass->form->new_form('Edit Template Information');
		$this->rknclass->form->set_action("index.php?ctr=edit_templates&amp;act=update_template_info&amp;id={$this->rknclass->get['id']}");
		$this->rknclass->form->add_input('tpl_name', 'input', 'Template Name', 'Enter the author of this template', $info['tpl_name']);
		$this->rknclass->form->add_input('tpl_description', 'textarea', 'Template Description', 'Enter a short description for this template', $info['tpl_description']);
		$this->rknclass->form->add_input('tpl_author', 'input', 'Template Author', 'Enter the name of the templates author', $info['tpl_author']);
		$this->rknclass->form->process();
		
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function update_template_info()
	{
	    if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		if($this->rknclass->post['tpl_name'] == '' || $this->rknclass->post['tpl_description'] == '' || $this->rknclass->post['tpl_author'] == '')
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
		
		$updated['tpl_name']=$this->rknclass->post['tpl_name'];
		$updated['tpl_description']=$this->rknclass->post['tpl_description'];
		$updated['tpl_author']=$this->rknclass->post['tpl_author'];
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "templates SET tpl_name='{$this->rknclass->post['tpl_name']}', tpl_description='{$this->rknclass->post['tpl_description']}', tpl_author='{$this->rknclass->post['tpl_author']}' WHERE tpl_id='{$this->rknclass->get['id']}' LIMIT 1");
		
		$new=serialize($updated);
		
		$handle=fopen(RKN__fullpath . 'cache/templates/' . $this->rknclass->get['id'] . '/rkn_template_info.php', 'w+');
		fwrite($handle, $new) or exit($this->rknclass->form->ajax_error('Unable to write to rkn_template_info file'));
		fclose($handle);
		
		$this->rknclass->form->ajax_success('Successfully updated template information!');
	}
	
	public function update_template()
	{
		if(defined('RKN__demo') AND RKN__demo == '1')
	    {
	       exit($this->rknclass->form->ajax_error('This feature is disabled in the demo!'));
	    }
	    
		if(empty($this->rknclass->get['id']))
		{
			exit($this->rknclass->form->ajax_error('Invalid template id'));
		}
		
		if(empty($this->rknclass->get['tpl_name']))
		{
			exit($this->rknclass->form->ajax_error('Invalid template name'));
		}
		
		if(get_magic_quotes_gpc())
		{
			$_POST['tpl_data']=stripslashes($_POST['tpl_data']);
		}
		$this->rknclass->post['tpl_data']=$_POST['tpl_data'];
		
		if(empty($this->rknclass->post['tpl_data']))
		{
			exit($this->rknclass->form->ajax_error('One or more fields were left blank!'));
		}
								/*==============================================
									START TEMPLATE PROCESSING AND COMPRESSION
								================================================*/
		
		//This code removes any excessive whitespace from the template
		
		if($this->rknclass->post['remove_crap'] == 'excessive' || $this->rknclass->post['remove_crap'] == 'all' || $this->rknclass->post['remove_crap'] == 'everything_but_new_lines')
		{
			$this->rknclass->post['tpl_data']=ereg_replace("  +", '', $this->rknclass->post['tpl_data']);
		}
		
		//Todo: Update to PCRE / Re-write
		
		//This code removes excessive new lines, tabs, carriage returns, and vertical tabs
		//Best compression method as it shouldn't corrupt any code
		
		if($this->rknclass->post['remove_crap'] == 'everything_but_new_lines')
		{
			$this->rknclass->post['tpl_data']=ereg_replace("\n\n\n+", '', $this->rknclass->post['tpl_data']); //We'll keep one line only
			$this->rknclass->post['tpl_data']=ereg_replace("\t+", '', $this->rknclass->post['tpl_data']);
			$this->rknclass->post['tpl_data']=ereg_replace("\r+", '', $this->rknclass->post['tpl_data']);
			$this->rknclass->post['tpl_data']=ereg_replace("\x0B+", '', $this->rknclass->post['tpl_data']);
		}
		
		//This code removes all new lines, tabs, carriage returns and vertical tabs
		//Will make your code virtually uneditable so be careful with it!!!!
		//Highest compression, most dangerous however
		
		elseif($this->rknclass->post['remove_crap'] == 'all')
		{
			$this->rknclass->post['tpl_data']=ereg_replace("\n+", '', $this->rknclass->post['tpl_data']);
			$this->rknclass->post['tpl_data']=ereg_replace("\t+", '', $this->rknclass->post['tpl_data']);
			$this->rknclass->post['tpl_data']=ereg_replace("\r+", '', $this->rknclass->post['tpl_data']);
			$this->rknclass->post['tpl_data']=ereg_replace("\x0B+", '', $this->rknclass->post['tpl_data']);
		}
		
			
		$this->rknclass->tpl_admin->update_template($this->rknclass->get['tpl_name'], $this->rknclass->get['id'], $this->rknclass->post['tpl_data']);
		
		$this->rknclass->form->ajax_success('Updated template successfully!');
	}
	
	public function large_editor()
	{
		echo <<<HTML
<html>
<body>
<style type="text/css">
body
{
	margin:0;
	padding:0;
	background-color: #e0e0e0;
	background-image: url(images/bg.gif);
}
</style>
<form id="large_edit" action="#" align="center">
<textarea id="edit_box" style="width:100%;height:675px;" onchange="update_box();"></textarea>
<div align="right" style="float:right;font-weight:bold;" onclick="self.close();">Close Window</div>
</form>
<script type="text/javascript">

function update_box()
{
	window.opener.document.getElementById('tpl_data').value = document.getElementById('edit_box').value;
}
var box = document.getElementById('edit_box');
box.value = window.opener.document.getElementById('tpl_data').value
</script>
</body>
</html>
HTML;
	}
}
?>