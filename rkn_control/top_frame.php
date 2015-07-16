<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}

class top_frame
{
	public function init()
	{
		if($this->rknclass->get['id'] == '' || $this->rknclass->get['id'] === false)
		{
			exit('<strong>Invalid Content id!</strong>');
		}
		
		$this->rknclass->tpl->preload(array('top_frame'));
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		
		if($this->rknclass->db->num_rows()<1)
		{
			exit('<strong>Invalid Content id!</strong>');
		}
	}
	
	public function idx()
	{
		$row=$this->rknclass->db->fetch_array();
		$this->rknclass->tpl->parse('url', $row['url'], 'top_frame');
		if($this->rknclass->settings['seo_urls'] == '1')
		{
			$this->rknclass->tpl->parse('outurl', $this->rknclass->settings['site_url'] . "/$row[plug_id]/" . $this->rknclass->utils->url_ready($row['title']) . ".html", 'top_frame');
		}
		else
		{
			$this->rknclass->tpl->parse('outurl', $this->rknclass->settings['site_url'] . "/index.php?ctr=view&amp;id=$row[plug_id]", 'top_frame');
		}
		$this->rknclass->tpl->parse('site url', $this->rknclass->settings['site_url'], 'top_frame');	
		$this->rknclass->tpl->parse('title', $row['title'], 'top_frame');
		$this->rknclass->tpl->parse('description', $row['description'], 'top_frame');
		$this->rknclass->tpl->parse('tags', $this->rknclass->utils->get_tags($row['tags']), 'top_frame');
		$this->rknclass->tpl->parse('views', $row['views'], 'top_frame');
		$this->rknclass->tpl->parse('posted', $this->rknclass->utils->make_date($row['posted']), 'top_frame');
		$this->rknclass->tpl->parse('poster', $row['poster'], 'top_frame');
		$this->rknclass->tpl->parse('poster_id', $row['poster_id'], 'top_frame');
		$this->rknclass->tpl->parse('thumb', THUMB_DIR . $row['thumb'], 'top_frame');
		$this->rknclass->tpl->parse('category', $row['category'], 'top_frame');
		$this->rknclass->tpl->parse('category_id', $row['category_id'], 'top_frame');

		if($this->rknclass->settings['seo_urls'] == '1')
		{
			$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/cat/' . $this->rknclass->utils->url_ready($row['category']) . '/', 'top_frame');
		}
		else
		{
			$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/index.php?ctr=filter&amp;act=category&amp;name=' . $row['category'], 'top_frame');
		}

		if($this->rknclass->settings['seo_urls'] == '1')
		{
			$this->rknclass->tpl->parse('comments_url', $this->rknclass->settings['site_url'] . "/comments/$row[plug_id]/" . $this->rknclass->utils->url_ready($row['title']) . ".html", 'top_frame');
		}
		else
		{
			$this->rknclass->tpl->parse('comments_url', $this->rknclass->settings['site_url'] . "/index.php?ctr=comments&amp;id=$row[plug_id]", 'top_frame');
		}
		
		$this->rknclass->tpl->parse('comments_num', $row['total_comments'], 'top_frame');
		
		$this->rknclass->tpl->process('top_frame');
	}
}
?>