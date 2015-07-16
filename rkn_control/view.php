<?php
/*======================================
Predator CMS 1.x
Copyright 2007 Ranakin Web Development
=======================================*/
if(!defined('IN_RKN_PREDATOR'))
{
	exit("<strong>Access to this file is prohibited</strong><br />\nPowered by Predator CMS<br />\nThis is not free software, and should not be redistributed or copied/cloned/reproduced in any way!");
}
class view
{
	public function init()
	{
		define('PAGER_URL', $this->rknclass->settings['site_url'] . '/');
	}
	
	public function idx()
	{
		if(empty($this->rknclass->get['id']) || !ctype_digit((string)$this->rknclass->get['id']))
		{
			exit(header('Location: ' . $this->rknclass->settings['site_url']));
		}
		
		foreach($this->rknclass->db->rkn_cache_query("SELECT type,framed,url,plug_id FROM " . TBLPRE . "plugs WHERE plug_id='{$this->rknclass->get['id']}'", 300) as $result)
		{
			$row = $result;
			break;
		}
		
		if($row['type'] == '1')
		{
			$ip = $this->rknclass->db->escape($_SERVER['REMOTE_ADDR']);
			$ref_url = $this->rknclass->db->escape($this->rknclass->session->data['ref_url']);
			$site_url = $this->rknclass->utils->rkn_url_parser($row['url']);
			
			$this->rknclass->tracker->track_out($this->rknclass->utils->rkn_url_parser($row['url']));
			
			$this->rknclass->db->query("INSERT INTO " . TBLPRE . "outgoing_hits SET user_ip='$ip', site_url='$site_url', ref_url='$ref_url', exit_url='" . $this->rknclass->db->escape($row['url']) . "', time='" . time() . "'");
		}
		
		if(empty($this->rknclass->get['page']))
		{
			$this->rknclass->db->query("UPDATE " . TBLPRE . "plugs SET views=views+1 WHERE plug_id='{$this->rknclass->get['id']}' LIMIT 1");
		}
		
		if($row['type'] == '1')
		{
			if($row['framed'] == '0')
			{
				exit(header("Location: $row[url]"));
			}
			else
			{
				echo "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
<title>{$row['title']}</title>
</head>

<frameset rows=\"80,*\" frameborder=\"NO\" border=\"0\" framespacing=\"0\">
  <frame src=\"{$this->rknclass->settings['site_url']}/index.php?ctr=top_frame&amp;id={$row['plug_id']}\" name=\"topFrame\" scrolling=\"NO\" noresize title=\"topFrame\">
  <frame src=\"{$row['url']}\" name=\"mainFrame\" title=\"mainFrame\">
</frameset>
<noframes><body>You must have a browser that supports frames to view this page. Please <a href=\"http://getfirefox.com\">Get one that does</a>!
</body></noframes>
</html>";
			}
		}
		
		elseif($row['type'] == '2')
		{
			$this->hosted_video($row['plug_id']);
		}
		
		elseif($row['type'] == '3')
		{
			$this->embedded_video($row['plug_id']);
		}
		
		elseif($row['type'] == '5')
		{
			$this->blog_entry($row['plug_id']);
		}
	}
	
	private function hosted_video($plug_id)
	{
		foreach($this->rknclass->db->rkn_cache_query("SELECT " . TBLPRE . "plugs.*, " . TBLPRE . "videos.player, " . TBLPRE . "videos.file_name, " . TBLPRE . "videos.sponsor_site_id FROM " . TBLPRE . "plugs LEFT JOIN " . TBLPRE . "videos ON " . TBLPRE . "plugs.plug_id = " . TBLPRE . "videos.plug_id WHERE " . TBLPRE . "plugs.plug_id='$plug_id' LIMIT 1", 300) as $value)
		{
			$row = $value;
			break;
		}
		
		/*=============================
		Does quick check to ensure the
		data is valid, and if not,
		will throw a message with a 
		link to your homepage.
		=============================*/
		
		if($row['player'] == '' || $row['file_name'] == '')
		{
			exit("Video data invalid!<br /><a href=\"{$this->rknclass->settings['site_url']}\">Click here</a> to return");
		}
		
		$this->rknclass->tpl->preload(array('header', 'footer', "video_player_{$row['player']}", 'video_page', 'plugs', 'pagination'));
		
		/*========================
		The rest of this function
		handles the templating
		and renders the output
		========================*/
		
		$this->rknclass->page_title="Viewing Media - {$row['title']}";
		
		$this->rknclass->meta['title']       = $row['title'];
		$this->rknclass->meta['description'] = $row['description'];
		$this->rknclass->meta['keywords']    = str_replace(' ', ',', $row['tags']);
		
		$this->rknclass->tpl->auto_parser('header');
		
		if($this->rknclass->settings['video_server'] == '0')
		{
			$video_location = $this->rknclass->settings['site_url'] . "/videos/{$row['file_name']}";
		}
		else
		{
			$video_location = $this->rknclass->settings['cluster_settings']['video_server_http'] . "/{$row['file_name']}";
		}
		if($row['player'] === 'wmv')
		{
			$this->rknclass->tpl->parse('video', $video_location, 'video_player_wmv');
			$video=$this->rknclass->tpl->return_parsed('video_player_wmv');
		}
		
		elseif($row['player'] === 'flv')
		{
			$this->rknclass->tpl->parse('video', $video_location, 'video_player_flv');
			$this->rknclass->tpl->parse('thumb', RKN__fullpath . $this->rknclass->settings['thumb_dir'] . '/' . $row['thumb'], 'video_page');
			$video=$this->rknclass->tpl->return_parsed('video_player_flv');
		}
		
		$this->rknclass->tpl->parse('video', $video, 'video_page');
		$this->rknclass->tpl->parse('title', $row['title'], 'video_page');
		$this->rknclass->tpl->parse('description', $row['description'], 'video_page');
		$this->rknclass->tpl->parse('tags', $this->rknclass->utils->get_tags($row['tags']), 'video_page');
		$this->rknclass->tpl->parse('views', $row['views'], 'video_page');
		$this->rknclass->tpl->parse('posted', $this->rknclass->utils->make_date($row['posted']), 'video_page');
		$this->rknclass->tpl->parse('poster', $row['poster'], 'video_page');
		$this->rknclass->tpl->parse('poster_id', $row['poster_id'], 'video_page');
		$this->rknclass->tpl->parse('thumb', THUMB_DIR . $row['thumb'], 'video_page');
		$this->rknclass->tpl->parse('category', $row['category'], 'video_page');
		$this->rknclass->tpl->parse('category_id', $row['category_id'], 'video_page');
		$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/index.php?ctr=filter&amp;act=category&amp;cat_name=' . $row['category'], 'video_page');

		$rating="<a href=\"#\" class=\"rating-pos\" onclick=\"update_rating('$row[plug_id]', 'yes'); return false;\">+</a> <span id=\"cur-rating-{$row['plug_id']}\">{$row['rating']}</span> <a href=\"#\" class=\"rating-neg\" onclick=\"update_rating('{$row['plug_id']}', 'no'); return false;\">-</a>";
		
		$this->rknclass->tpl->parse('rating', $rating, 'video_page');	
					
		if(strpos($this->rknclass->tpl->parsed['video_page'], '{plugs}') !== false)
		{
			$types = unserialize($this->rknclass->settings['listing_types']);
			
			$first = null;
			
			$type_list = null;
			
			foreach($types as $key => $value)
			{
				if($first === null)
				{
					$type_list .= "$value";
					$first      = true;
				}
				else
				{
					$type_list .= ",$value";
				}
			}
			
			$this->rknclass->load_object('plugs');
            $this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND posted<" . $this->rknclass->utils->content_schedule() . " AND plug_id!='{$this->rknclass->get['id']}' AND type IN($type_list) ORDER BY posted DESC"), 'video_page');			$this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'video_page');
		}
		
		if(strpos($this->rknclass->tpl->parsed['video_page'], '{ad[\'html\']}') !== false)
		{
		    $i = 0;
			foreach($this->rknclass->db->rkn_cache_query("SELECT ad_data FROM " . TBLPRE . "sponsors_ads WHERE ad_sponsor_site_id='{$row['sponsor_site_id']}'" . ($this->rknclass->session->data['country_flagged'] == '0' ? ' AND ad_flagged=\'0\'' : '') . " AND ad_type='html' ORDER BY RAND() LIMIT 1") as $row2)
			{
			    ++$i;
				$this->rknclass->tpl->parse('ad[\'html\']', $row2['ad_data'],'video_page');
				break; //100% ensures we only run this once
			}
			
			if($i < 1)
			{
			    $this->rknclass->tpl->parse('ad[\'html\']', '','video_page');
			}
		}
		
		if(strpos($this->rknclass->tpl->parsed['video_page'], '{ad[\'banner\'][\'image\']}') !== false)
		{
			foreach($this->rknclass->db->rkn_cache_query("SELECT ad_id,ad_data,ad_sponsor_site_id FROM " . TBLPRE . "sponsors_ads WHERE ad_sponsor_site_id='{$row['sponsor_site_id']}'" . ($this->rknclass->session->data['country_flagged'] == '0' ? ' AND ad_flagged=\'0\'' : '') . " AND ad_type='banner' ORDER BY RAND() LIMIT 1") as $row2)
			{
				$data  = @unserialize($row2['ad_data']);
				
				$image = $this->rknclass->settings['site_url'] . '/banner_ads/' . $data['name'];
				$url   = $this->rknclass->settings['site_url'] . "/?ctr=ad_banner&amp;banner_id={$row2['ad_id']}&amp;sponsor_id={$row2['ad_sponsor_site_id']}";
				
				$this->rknclass->tpl->parse('ad[\'banner\'][\'image\']', $image ,'video_page');
				$this->rknclass->tpl->parse('ad[\'banner\'][\'url\']', $url ,'video_page');
				break; //100% ensures we only run this once
			}			
		}
		
		$this->rknclass->tpl->process('video_page');
		
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	private function embedded_video($plug_id)
	{
		$this->rknclass->tpl->preload(array('header', 'footer', 'video_page', 'plugs', 'pagination'));
		
		
		foreach($this->rknclass->db->rkn_cache_query("SELECT " . TBLPRE . "plugs.*, " . TBLPRE . "videos.html_code, " . TBLPRE . "videos.sponsor_site_id FROM " . TBLPRE . "plugs LEFT JOIN " . TBLPRE . "videos ON " . TBLPRE . "plugs.plug_id = " . TBLPRE . "videos.plug_id WHERE " . TBLPRE . "plugs.plug_id='$plug_id' LIMIT 1") as $value)
		{
			$row=$value;
			break;
		}
		
		/*========================
		The rest of this function
		handles the templating
		and renders the output
		========================*/
		
		
		$this->rknclass->page_title="Viewing Media - {$row['title']}";
		$this->rknclass->meta['title']       = $row['title'];
		$this->rknclass->meta['description'] = $row['description'];
		$this->rknclass->meta['keywords']    = str_replace(' ', ',', $row['tags']);
		
		$this->rknclass->tpl->auto_parser('header');
		
		$video=$row['html_code'];
		$this->rknclass->tpl->parse('video', $video, 'video_page');
		$this->rknclass->tpl->parse('title', $row['title'], 'video_page');
		$this->rknclass->tpl->parse('description', $row['description'], 'video_page');
		$this->rknclass->tpl->parse('tags', $this->rknclass->utils->get_tags($row['tags']), 'video_page');
		$this->rknclass->tpl->parse('views', $row['views'], 'video_page');
		$this->rknclass->tpl->parse('posted', $this->rknclass->utils->make_date($row['posted']), 'video_page');
		$this->rknclass->tpl->parse('poster', $row['poster'], 'video_page');
		$this->rknclass->tpl->parse('poster_id', $row['poster_id'], 'video_page');
		$this->rknclass->tpl->parse('thumb', THUMB_DIR . $row['thumb'], 'video_page');
		$this->rknclass->tpl->parse('category', $row['category'], 'video_page');
		$this->rknclass->tpl->parse('category_id', $row['category_id'], 'video_page');
		$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/index.php?ctr=filter&amp;act=category&amp;cat_name=' . $row['category'], 'video_page');

		$rating="<a href=\"#\" class=\"rating-pos\" onclick=\"update_rating('$row[plug_id]', 'yes'); return false;\">+</a> <span id=\"cur-rating-{$row['plug_id']}\">{$row['rating']}</span> <a href=\"#\" class=\"rating-neg\" onclick=\"update_rating('{$row['plug_id']}', 'no'); return false;\">-</a>";
		
		$this->rknclass->tpl->parse('rating', $rating, 'video_page');
				
		if(strpos($this->rknclass->tpl->parsed['video_page'], '{plugs}') !== false)
		{
			
			$types = unserialize($this->rknclass->settings['listing_types']);
			
			$first = null;
			
			$type_list = null;
			
			foreach($types as $key => $value)
			{
				if($first === null)
				{
					$type_list .= "$value";
					$first      = true;
				}
				else
				{
					$type_list .= ",$value";
				}
			}
			
			$this->rknclass->load_object('plugs');
            $this->rknclass->tpl->parse('plugs', $this->rknclass->plugs->listem("approved='1' AND posted<" . $this->rknclass->utils->content_schedule() . " AND plug_id!='{$this->rknclass->get['id']}' AND type IN($type_list) ORDER BY posted DESC"), 'video_page');
            $this->rknclass->tpl->parse('page nav', $this->rknclass->plugs->page_nav(), 'video_page');
		}
		
		if(strpos($this->rknclass->tpl->parsed['video_page'], '{ad[\'html\']}') !== false)
		{
		    $i = 0;
			foreach($this->rknclass->db->rkn_cache_query("SELECT ad_data FROM " . TBLPRE . "sponsors_ads WHERE ad_sponsor_site_id='{$row['sponsor_site_id']}'" . ($this->rknclass->session->data['country_flagged'] == '0' ? ' AND ad_flagged=\'0\'' : '') . " AND ad_type='html' ORDER BY RAND() LIMIT 1") as $row2)
			{
			    ++$i;
				$this->rknclass->tpl->parse('ad[\'html\']', $row2['ad_data'],'video_page');
				break; //100% ensures we only run this once
			}
			
			if($i < 1)
			{
			    $this->rknclass->tpl->parse('ad[\'html\']', '','video_page');
			}
		}

		if(strpos($this->rknclass->tpl->parsed['video_page'], '{ad[\'banner\'][\'image\']}') !== false)
		{
		    $i = 0;
			foreach($this->rknclass->db->rkn_cache_query("SELECT ad_id,ad_data,ad_sponsor_site_id FROM " . TBLPRE . "sponsors_ads WHERE ad_sponsor_site_id='{$row['sponsor_site_id']}'" . ($this->rknclass->session->data['country_flagged'] == '0' ? ' AND ad_flagged=\'0\'' : '') . " AND ad_type='banner' ORDER BY RAND() LIMIT 1") as $row2)
			{
			    ++$i;
				$data  = @unserialize($row2['ad_data']);
				
				$image = $this->rknclass->settings['site_url'] . '/banner_ads/' . $data['name'];
				$url   = $this->rknclass->settings['site_url'] . "/?ctr=ad_banner&amp;banner_id={$row2['ad_id']}&amp;sponsor_id={$row2['ad_sponsor_site_id']}";
				
				$this->rknclass->tpl->parse('ad[\'banner\'][\'image\']', $image ,'video_page');
				$this->rknclass->tpl->parse('ad[\'banner\'][\'url\']', $url ,'video_page');
				break; //100% ensures we only run this once
			}
		}

		$this->rknclass->tpl->process('video_page');
		
		$this->rknclass->tpl->auto_parser('footer');
	}
	
	private function blog_entry($plug_id)
	{
		$this->rknclass->tpl->preload(array('header', 'footer', 'blog_page'));
		
		$this->rknclass->db->query("SELECT " . TBLPRE . "plugs.*, " . TBLPRE . "blog_articles.body FROM " . TBLPRE . "plugs LEFT JOIN " . TBLPRE . "blog_articles ON " . TBLPRE . "plugs.plug_id = " . TBLPRE . "blog_articles.plug_id WHERE " . TBLPRE . "plugs.plug_id='$plug_id' LIMIT 1");
		
		$row=$this->rknclass->db->fetch_array();
		
		/*=============================
		Does quick check to ensure the
		data is valid, and if not,
		will throw a message with a 
		link to your homepage.
		=============================*/
		
		if($row['body'] == '')
		{
			exit("Blog data invalid!<br /><a href=\"{$this->rknclass->settings['site_url']}\">Click here</a> to return");
		}
		
		$body=explode('{pagebreak}', $row['body']);
		$num_pages=count($body);
		
		if(intval($this->rknclass->get['page']) < 1)
		{
			$this->rknclass->get['page']='1';
		}
		
		$page=($this->rknclass->get['page'] - 1);
		
		$body=$body['' . $page . ''];
		
		/*========================
		The rest of this function
		handles the templating
		and renders the output
		========================*/
		
		$this->rknclass->page_title="Viewing Blog Entry - {$row['title']}";
		$this->rknclass->meta['title']       = $row['title'];
		$this->rknclass->meta['description'] = $row['description'];
		$this->rknclass->meta['keywords']    = str_replace(' ', ',', $row['tags']);
		
		$this->rknclass->tpl->auto_parser('header');
		
		$this->rknclass->tpl->parse('title', $row['title'], 'blog_page');
		$this->rknclass->tpl->parse('tags', $this->rknclass->utils->get_tags($row['tags']), 'blog_page');
		$this->rknclass->tpl->parse('views', $row['views'], 'blog_page');
		$this->rknclass->tpl->parse('posted', $this->rknclass->utils->make_date($row['posted']), 'blog_page');
		$this->rknclass->tpl->parse('poster', $row['poster'], 'blog_page');
		$this->rknclass->tpl->parse('poster_id', $row['poster_id'], 'blog_page');
		$this->rknclass->tpl->parse('thumb', THUMB_DIR . $row['thumb'], 'blog_page');
		$this->rknclass->tpl->parse('category', $row['category'], 'blog_page');
		$this->rknclass->tpl->parse('category_id', $row['category_id'], 'blog_page');
		$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/index.php?ctr=filter&amp;act=category&amp;name=' . $row['category'], 'blog_page');
		$this->rknclass->tpl->parse('entry', $body, 'blog_page');

		$page_url = $this->rknclass->utils->page_url();
		$this->url_parsed = $this->rknclass->settings['site_url'] . '/?ctr=view&id=' . $this->rknclass->get['id'];
		
		$this->page_exists = false;
		
		if(preg_match('@([\?|&])page=([0-9]+)@i', $page_url))
		{
			$this->url_parsed = preg_replace('@([\?|&])page=([0-9]+)@i', '$1page={PAGE_NUM}', $url);
			$this->page_exists = true;
		}
		
		$this->rknclass->tpl->parse('IF_PREV', '<?php if($this->rknclass->get[\'page\'] > 1){?>', 'blog_page');
		$this->rknclass->tpl->parse('IF_NEXT', '<?php if($this->rknclass->get[\'page\'] < ' . $num_pages . ')' . "\n" . '{?>', 'blog_page');
		$this->rknclass->tpl->parse('END_IF', '<?php } ?>', 'blog_page');
		
		$this->rknclass->tpl->parse('PREV', $this->gen_url($this->rknclass->get['page'] - 1), 'blog_page');
		$this->rknclass->tpl->parse('NEXT', $this->gen_url($this->rknclass->get['page'] + 1), 'blog_page');
		
		$this->rknclass->tpl->process('blog_page');
		
		$this->rknclass->tpl->auto_parser('footer');
	}

	private function gen_url($num)
	{
		if($this->page_exists === false)
		{
			$this->page_exists = true;
			$this->url_parsed .= '&page={PAGE_NUM}';
		}
		$parsed = str_replace('{PAGE_NUM}', $num, $this->url_parsed);
		return $parsed;
	}
}
?>