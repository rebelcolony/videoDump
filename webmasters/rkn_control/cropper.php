<?php

/*

Predator CMS Webmasters Cropper Controller
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
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
		}
	}
	//END INIT FUNCTION
	
	public function idx()
	{
		if(!isset($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit($this->rknclass->global_tpl->webmasters_error('Invalid plug id!'));
		}
		
		$this->rknclass->db->query("SELECT plug_id,cropped,thumb FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			if($this->rknclass->debug === true)
			{
				exit('Plug not found');
			}
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/"));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['cropped'] == '1')
		{
			exit($this->rknclass->global_tpl->webmasters_error('Image already cropped!'));
		}
		
		$prop=$this->rknclass->cropper->prop_fit_in_canvas($row['thumb']); //Resizes large images so that they fit inside the canvas		
		define('IN_CROPPER', true); //Fixes bug affecting IE7 (and possibly, IE6,5,4,3,2,1 and 8)
		$this->rknclass->global_tpl->webmasters_header();
		$this->rknclass->cropper->load_interface($row['thumb'], $row['plug_id'], $prop['width'], $prop['height'], '1');
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function crop_it()
	{
		$this->rknclass->db->query("SELECT plug_id,cropped,thumb,posted FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->post['plug_id']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			if($this->rknclass->debug === true)
			{
				exit('Plug not found');
			}
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/"));
		}
		
		$row=$this->rknclass->db->fetch_array();
		
		if($row['cropped'] == '1')
		{
			if($this->rknclass->debug === true)
			{
				exit('Plug already cropped');
			}
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/"));
		}
		
		$thumb=RKN__fullpath . 'tmp/' . $row['thumb'];
		$info=getimagesize($thumb);
		
		if(intval($this->rknclass->post['width'])<$this->rknclass->settings['thumb_width'] || intval($this->rknclass->post['height'])<$this->rknclass->settings['thumb_height'])
		{
			exit($this->rknclass->global_tpl->webmasters_error('Selected image cropper dimensions are too small!'));
		}
		
		$sizes=array('x1' => $this->rknclass->post['x1'], 'y1' => $this->rknclass->post['y1'], 'x2' => $this->rknclass->post['x2'], 'y2' => $this->rknclass->post['y2'], 'width' => $this->rknclass->post['width'], 'height' => $this->rknclass->post['height']);
		$this->rknclass->cropper->crop_image($thumb, $info['mime'], $sizes, '1');
		
		if($this->rknclass->user['group']['require_validation'] == '1')
		{
			$is_approved='0';
		}
		else
		{
			$is_approved='1';
		}
		
		$queue = false;
		
		if($row['posted'] > time() AND $this->rknclass->settings['queue_time'] > 0)
		{
		    $queue = true;
		}
		
		$message = 'Successfully cropped image!';
		
		if($queue)
		{
		    $message .= '<br />Your plug is scheduled for release at: ' . date('jS M Y g:i:sa<\b\r />(\T\i\m\e\z\o\n\e: e)', $row['posted']);
		}
		
		$message .= '<br /><br /><a href="' . $this->rknclass->settings['site_url'] . '/webmasters/index.php?ctr=content&amp;act=my_content">Click here</a> to return!';
		
		$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET cropped='1', approved='$is_approved' WHERE plug_id='{$this->rknclass->post['plug_id']}' LIMIT 1");
		
		$this->rknclass->global_tpl->webmasters_message($message);
	}
}

?>