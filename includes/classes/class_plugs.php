<?php
class rkn_plugs
{
	public $pager_data;
	public $page_nav;
	public $rknclass;
	
	private $ad_counter=0;
	private $url_parsed;
	
	public function listem($where)
	{
		$this->rknclass->load_object('pager');
		
		$this->rknclass->pager->limit=$this->rknclass->settings['plugs_per_page'];
		$this->rknclass->pager->run('plug_id', TBLPRE . "plugs", $where, 300);
		
		$this->pager_data=$this->rknclass->pager->paging_data();
		
		$plugs='';
		
		foreach($this->rknclass->db->rkn_cache_query("SELECT * FROM " . TBLPRE . "plugs WHERE $where LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']) as $row)
		{
			$this->rknclass->tpl->parse('url', $row['url'], 'plugs');
			if($this->rknclass->settings['seo_urls'] == '1')
			{
				$this->rknclass->tpl->parse('outurl', $this->rknclass->settings['site_url'] . "/{$row['seo_url']}.html", 'plugs');
			}
			else
			{
				$this->rknclass->tpl->parse('outurl', $this->rknclass->settings['site_url'] . "/index.php?ctr=view&amp;id={$row['plug_id']}", 'plugs');
			}
			
			$this->rknclass->tpl->parse('title', $row['title'], 'plugs');
			$this->rknclass->tpl->parse('description', $row['description'], 'plugs');
			$this->rknclass->tpl->parse('tags', $this->rknclass->utils->get_tags($row['tags']), 'plugs');
			$this->rknclass->tpl->parse('views', $row['views'], 'plugs');
			$this->rknclass->tpl->parse('posted', $this->rknclass->utils->make_date($row['posted']), 'plugs');
			$this->rknclass->tpl->parse('poster', $row['poster'], 'plugs');
			$this->rknclass->tpl->parse('poster_id', $row['poster_id'], 'plugs');
			$this->rknclass->tpl->parse('thumb', THUMB_DIR . $row['thumb'], 'plugs');
			$this->rknclass->tpl->parse('category', $row['category'], 'plugs');
			$this->rknclass->tpl->parse('category_id', $row['category_id'], 'plugs');
			
			if($this->rknclass->settings['seo_urls'] == '1')
			{
				$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/cat/' . $this->rknclass->utils->url_ready($row['category']) . '/', 'plugs');
			}
			else
			{
				$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/index.php?ctr=filter&amp;act=category&amp;cat_name=' . $this->rknclass->utils->url_ready($row['category']), 'plugs');
			}

			if($this->rknclass->settings['seo_urls'] == '1')
			{
				$this->rknclass->tpl->parse('comments_url', $this->rknclass->settings['site_url'] . "/comments/$row[plug_id]/" . $this->rknclass->utils->url_ready($row['title']) . ".html", 'plugs');
			}
			else
			{
				$this->rknclass->tpl->parse('comments_url', $this->rknclass->settings['site_url'] . "/index.php?ctr=comments&amp;id=$row[plug_id]", 'plugs');
			}
			
			$this->rknclass->tpl->parse('comments_num', $row['total_comments'], 'plugs');

			switch($row['type'])
			{
			    case '1':
			        $this->rknclass->tpl->parse('target', '_blank', 'plugs');
			        break;
			    case '2':
			    case '3':
			    case '5':
			        $this->rknclass->tpl->parse('target', '_self', 'plugs');
			        break;
			    default:
			        $this->rknclass->tpl->parse('target', '_blank', 'plugs');
			}
			
			$rating="<a href=\"#\" class=\"rating-pos\" onclick=\"update_rating('{$row['plug_id']}', 'yes'); return false;\"><img src=\"images/plus.gif\" /></a> <span id=\"cur-rating-{$row['plug_id']}\">{$row['rating']}</span> <a href=\"#\" class=\"rating-neg\" onclick=\"update_rating('{$row['plug_id']}', 'no'); return false;\"><img src=\"images/minus.gif\" /></a>";
						
			$this->rknclass->tpl->parse('rating', $rating, 'plugs');	
			if($this->rknclass->settings['ads_between_plugs_enabled'] == '1')
			{
				$ads=@unserialize($this->rknclass->settings['ads_between_plugs']);
				$this->ad_counter++;
				if( (int) $this->ad_counter === (int) $this->rknclass->settings['ads_between_plugs_count'])
				{
					$total_ads=(count($ads) - 1);
					$this->rknclass->tpl->parsed['plugs']=$this->rknclass->tpl->parsed['plugs'] . base64_decode($ads['' . rand(0, $total_ads) . '']['code']);
					$this->ad_counter=0;
				}
			}					
			$plugs.=$this->rknclass->tpl->return_parsed('plugs');
			$this->rknclass->tpl->reset_parse('plugs');
		}
		return $plugs;
	}
	
	public function render_plug($where)
	{		
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "plugs WHERE $where LIMIT 1");
		while($row=$this->rknclass->db->fetch_array())
		{
			$this->rknclass->tpl->parse('url', $row['url'], 'plugs');
			if($this->rknclass->settings['seo_urls'] == '1')
			{
				$this->rknclass->tpl->parse('outurl', $this->rknclass->settings['site_url'] . "/{$row['seo_url']}.html", 'plugs');
			}
			else
			{
				$this->rknclass->tpl->parse('outurl', $this->rknclass->settings['site_url'] . "/index.php?ctr=view&amp;id=$row[plug_id]", 'plugs');
			}
			$this->rknclass->tpl->parse('title', $row['title'], 'plugs');
			$this->rknclass->tpl->parse('description', $row['description'], 'plugs');
			$this->rknclass->tpl->parse('tags', $this->rknclass->utils->get_tags($row['tags']), 'plugs');
			$this->rknclass->tpl->parse('views', $row['views'], 'plugs');
			$this->rknclass->tpl->parse('posted', $this->rknclass->utils->make_date($row['posted']), 'plugs');
			$this->rknclass->tpl->parse('poster', $row['poster'], 'plugs');
			$this->rknclass->tpl->parse('poster_id', $row['poster_id'], 'plugs');
			$this->rknclass->tpl->parse('thumb', THUMB_DIR . $row['thumb'], 'plugs');
			$this->rknclass->tpl->parse('category', $row['category'], 'plugs');
			$this->rknclass->tpl->parse('category_id', $row['category_id'], 'plugs');
			
			if($this->rknclass->settings['seo_urls'] == '1')
			{
				$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/cat/' . $this->rknclass->utils->url_ready($row['category']) . '/', 'plugs');
			}
			else
			{
				$this->rknclass->tpl->parse('category_url', $this->rknclass->settings['site_url'] . '/index.php?ctr=filter&amp;act=category&amp;name=' . $row['category'], 'plugs');
			}

			if($this->rknclass->settings['seo_urls'] == '1')
			{
				$this->rknclass->tpl->parse('comments_url', $this->rknclass->settings['site_url'] . "/comments/$row[plug_id]/" . $this->rknclass->utils->url_ready($row['title']) . ".html", 'plugs');
			}
			else
			{
				$this->rknclass->tpl->parse('comments_url', $this->rknclass->settings['site_url'] . "/index.php?ctr=comments&amp;id=$row[plug_id]", 'plugs');
			}
			
			$this->rknclass->tpl->parse('comments_num', $row['total_comments'], 'plugs');

			if($row['type'] == '1')
			{
				$this->rknclass->tpl->parse('target', '_blank', 'plugs');
			}
			elseif($row['type'] == '2' || $row['type'] == '3')
			{
				$this->rknclass->tpl->parse('target', '_self', 'plugs');
			}
			
			$rating="<a href=\"#\" class=\"rating-pos\" onclick=\"update_rating('{$row['plug_id']}', 'yes'); return false;\"><img src=\"images/plus.gif\" /></a> <span id=\"cur-rating-{$row['plug_id']}\">{$row['rating']}</span> <a href=\"#\" class=\"rating-neg\" onclick=\"update_rating('{$row['plug_id']}', 'no'); return false;\"><img src=\"images/minus.gif\" /></a>";
						
			$this->rknclass->tpl->parse('rating', $rating, 'plugs');	
						
			$plug=$this->rknclass->tpl->return_parsed('plugs');
			$this->rknclass->tpl->reset_parse('plugs');
		}
		return $plug;
	}
	
	public function page_nav()
	{
		/**
		 * @since 1.1.0
		 * 
		 * Pagination urls are now automatically
		 * generated via the regex below.
		 */
		
		if(!defined('PAGER_URL'))
		{
			$page_url = $this->rknclass->utils->page_url(false, false);
		}
		else
		{
			$page_url = PAGER_URL;
		}
		
		if(strpos($page_url, 'index.php') !== false)
		{
			$page_url = str_replace('index.php', '', $page_url);
		}
		
		if(isset($_GET['page']))
		{
			if($this->rknclass->settings['seo_urls'] == '1')
			{
				if(!strpos($page_url, '.html') AND defined('PAGER_URL'))
				{
					if(!ctype_digit((string)$this->rknclass->get['page']))
					{
						$this->rknclass->get['page'] = 1;
					}
					$page_url .= "page_{$this->rknclass->get['page']}.html";
				}
				
				$this->url_parsed = preg_replace('@page_([0-9]+)\.html@i', 'page_{PAGE_NUM}.html', $page_url);

				if(strpos($this->url_parsed, 'page=') !== false)
				{
					$this->url_parsed = preg_replace('@([\?|&])page=([0-9]+)@i', '$1page={PAGE_NUM}', $this->url_parsed);
				}
			}
			else
			{
				$this->url_parsed = preg_replace('@([\?|&])page=([0-9]+)@i', '$1page={PAGE_NUM}', $page_url);
			}
		}
		else
		{
			if($this->rknclass->settings['seo_urls'] == '1' AND strpos($page_url, '?') === false)
			{
				$this->url_parsed = $page_url . 'page_{PAGE_NUM}.html';
			}
			else
			{
				if(count($_GET) > 0)
				{
					$this->url_parsed = $page_url . '&page={PAGE_NUM}';
				}
				else
				{
					$this->url_parsed = $page_url . '?page={PAGE_NUM}';
				}
			}
		}
		
		$this->rknclass->tpl->parse('IF_PREV', '<?php if($this->rknclass->plugs->pager_data[\'previous\'] !== false){?>', 'pagination');
		$this->rknclass->tpl->parse('IF_NEXT', '<?php if($this->rknclass->plugs->pager_data[\'next\'] !== false)' . "\n" . '{?>', 'pagination');
		$this->rknclass->tpl->parse('END_IF', '<?php } ?>', 'pagination');
		
		$this->rknclass->tpl->parse('PREV', $this->gen_url($this->pager_data['previous']), 'pagination');
		$this->rknclass->tpl->parse('NEXT', $this->gen_url($this->pager_data['next']), 'pagination');
		
		$nav = $this->rknclass->tpl->return_parsed('pagination');
		
		$this->rknclass->tpl->reset_parse('pagination');
		
		return $nav;
	}
	
  /**
   * rkn_plugs::gen_url()
   *
   * @param int $num
   * @return string
   * @since 1.1.0
   *
   * Generates the full pagination url
   * for the page number supplied by the
   * $num parameter
   */
   
	private function gen_url($num)
	{
		$parsed = str_replace('{PAGE_NUM}', $num, $this->url_parsed);
		return $parsed;
	}
}
?>