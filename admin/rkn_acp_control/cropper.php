<?php
/*

Predator CMS Admin Cropper Controller
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

define('RKN__admintab', 'management');

class cropper extends rkn_render
{
	public $allowed;
	public $rknclass;

	//BEGIN INIT FUNCTION	
	public function init()
	{
		$this->rknclass->load_objects(array('cropper', 'global_tpl'));
		if($this->rknclass->session->is_guest === true)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/" . RKN__adminpath . "/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
		}
		
		if($this->rknclass->user['group']['is_admin'] !== '1')
		{
			exit($this->rknclass->global_tpl->admin_error('You must be an admin to access this area!'));	
		}
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		$this->rknclass->db->query("SELECT plug_id,cropped,thumb,type FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			if($this->rknclass->debug === true)
			{
				exit($this->rknclass->global_tpl->admin_error('Plug not found'));
			}
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['cropped'] == '1')
		{
			exit($this->rknclass->global_tpl->admin_error('Image already cropped!'));
		}
		
		$prop=$this->rknclass->cropper->prop_fit_in_canvas($row['thumb']); //Resizes large images so that they fit inside the canvas
		define('IN_CROPPER', true); //Fixes bug affecting IE7 (and possibly, IE6,5,4,3,2,1 and 8)
		$this->rknclass->global_tpl->admin_header();
		$this->rknclass->cropper->load_interface($row['thumb'], $row['plug_id'], $prop['width'], $prop['height'], $row['type']);
		$this->rknclass->global_tpl->admin_footer();
	}
	
	public function crop_it()
	{
		$this->rknclass->db->query("SELECT plug_id,cropped,thumb,type FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->post['plug_id']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit($this->rknclass->global_tpl->admin_error('Content not found'));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['cropped'] == '1')
		{
			exit($this->rknclass->global_tpl->admin_error('Content thumbnail already cropped'));
		}
		
		$thumb=RKN__fullpath . 'tmp/' . $row['thumb'];
		$info=getimagesize($thumb);
		
		$sizes=array('x1' => $this->rknclass->post['x1'], 'y1' => $this->rknclass->post['y1'], 'x2' => $this->rknclass->post['x2'], 'y2' => $this->rknclass->post['y2'], 'width' => $this->rknclass->post['width'], 'height' => $this->rknclass->post['height']);
		$this->rknclass->cropper->crop_image($thumb, $info['mime'], $sizes, $row['type']);
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET cropped='1' WHERE plug_id='{$this->rknclass->post['plug_id']}' LIMIT 1");
		
		switch($row['type'])
		{
		    case '1':
		        $submit_url  = "{$this->rknclass->settings['site_url']}/" . RKN__adminpath . '/?ctr=content&amp;act=add_plug';
		        $submit_type = 'Plug';
		        break;
		    case '2':
		        $submit_url  = "{$this->rknclass->settings['site_url']}/" . RKN__adminpath . '/?ctr=content&amp;act=add_hosted_video';
		        $submit_type = 'Video';
		        break;
		    case '3':
		        $submit_url  = "{$this->rknclass->settings['site_url']}/" . RKN__adminpath . '/?ctr=content&amp;act=add_embedded_video';
		        $submit_type = 'Video';
		        break;
		    case '5':
		        $submit_url  = "{$this->rknclass->settings['site_url']}/" . RKN__adminpath . '/?ctr=content&amp;act=create_blog_entry';
		        $submit_type = 'Blog Entry';
		        break;
		    default:
		        $submit_url  = "{$this->rknclass->settings['site_url']}/" . RKN__adminpath . '/?ctr=content&amp;act=add_plug';
		        $submit_type = 'Plug';
		        break;
		}
		$this->rknclass->global_tpl->admin_message("Successfully cropped image!<br /><br /><a href=\"{$submit_url}\">Click here</a> to submit another {$submit_type}!");
	}
}

?>