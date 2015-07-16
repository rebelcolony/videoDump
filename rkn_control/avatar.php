<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class avatar
{
	public function init()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
		}
	}
	
	public function idx()
	{
		if(@file_exists(RKN__fullpath . 'userdata/avatars/' . $this->rknclass->get['id'] . '.php'))
		{
			$data=file_get_contents(RKN__fullpath . 'userdata/avatars/' . $this->rknclass->get['id'] . '.php', false, NULL, 85);
			$data=@unserialize($data);
			
			if(is_array($data))
			{
				header("Content-type: {$data['image_mime']}");
				echo $data['image_data'];
			}
			else
			{
				echo "Invalid Image";
			}
		}
		else
		{
			header("Content-type: image/jpeg");
			echo @file_get_contents(RKN__fullpath . 'userdata/avatars/default.jpg');
		}
	}
}
?>