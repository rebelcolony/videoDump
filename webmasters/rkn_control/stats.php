<?php
class stats extends rkn_render
{
	public function init()
	{
		if($this->rknclass->session->is_guest === true)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?return_url=" . $this->rknclass->utils->page_url() . ""));
		}
		
		if($this->rknclass->user['total_sites']<1)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&act=add_site"));
		}
		
		$this->rknclass->load_objects(array('global_tpl'));
	}
	
	public function idx()
	{
		
		/*=====================
		We don't need an index
		page for this controller
		so lets load the users'
		"my stats" page instead
		======================*/
				
		$this->my_stats();
	}
	
	public function my_stats()
	{
		$this->rknclass->page_title='My Webmaster Statistics';
		$this->rknclass->global_tpl->webmasters_header();
		echo "<div class=\"page-title\">Your Sites &amp; Statistics</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">Name</th>
    <th scope=\"col\">Unique<br />Today's In</th>
    <th scope=\"col\">Unique<br />Today's Out</th>
    <th scope=\"col\">Raw<br />Today's In</th>
    <th scope=\"col\">Raw<br />Today's Out</th>
    <th scope=\"col\">Unique<br />Total In</th>
    <th scope=\"col\">Unique<br />Total Out</th>
    <th scope=\"col\">Raw<br />Total In</th>
    <th scope=\"col\">Raw<br />Total Out</th>
    <th scope=\"col\">Unique<br />" . ($this->rknclass->settings['trade_type'] === 'credits' ? "Credits" : "Ratio") . "</th>
    <th scope=\"col\">Apr</th>
    <th scope=\"col\">Edit</th>
    <th scope=\"col\">Del</th>
  </tr>";
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sites WHERE owner='{$this->rknclass->user['user_id']}' ORDER BY name ASC");
		while($row=$this->rknclass->db->fetch_array())
		{
			$ratio=$this->rknclass->utils->get_trade_by_in_out($row['u_total_in'], $row['u_total_out']);
			if($this->rknclass->utils->trade_check($row['u_total_in'], $row['u_total_out']) === false)
			{
				$ratio="<font color=\"#e32c00\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
			}
			else
			{
				$ratio="<font color=\"#136f01\">$ratio" . ($this->rknclass->settings['trade_type'] === 'ratio' ? " %" : "") . "</font>";
			}
			echo "\n<tr id=\"rows\">
    <td id=\"title\"><a href=\"http://www.{$row['url']}\" target=\"_blank\">{$row['name']}</a></td>
    <td>{$row['u_todays_in']}</td>
    <td>{$row['u_todays_out']}</td>
    <td>{$row['r_todays_in']}</td>
    <td>{$row['r_todays_out']}</td>
    <td>{$row['u_total_in']}</td>
    <td>{$row['u_total_out']}</td>
    <td>{$row['r_total_in']}</td>
    <td>{$row['r_total_out']}</td>
    <td><strong>$ratio</strong></td>
    <td><strong>" . ($row['approved'] == '0' ? "<font color=\"#e32c00\">Pending" : "<font color=\"#136f01\">Yes") . "</font></strong></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&amp;act=edit_site&amp;id={$row['site_id']}\"><img src=\"images/pencil.jpg\" border=\"0\" /></a></td>
    <td><a href=\"{$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&amp;act=del_site&amp;id={$row['site_id']}\"><img src=\"images/delete.jpg\" border=\"0\" /></a></td>
  </tr>";
		}
		echo "\n</table>";
		$this->rknclass->global_tpl->webmasters_footer();
	}
	
	public function all_stats()
	{
		$this->rknclass->page_title='All Webmaster Statistics';
		$pos=0;
		
		$this->rknclass->load_object('pager');
		$this->rknclass->pager->set_page(); //Need to do this when not using pager::run() method
		$this->rknclass->pager->limit=50; //TODO: Add option in ACP
		
		
		/*========================
		Query below will set our
		own value for the pager
		=========================*/
		
		$this->rknclass->db->query("SELECT count(site_id) FROM " . TBLPRE . "sites WHERE approved='1' AND banned!='1' AND owner > 0");
		
		$this->rknclass->pager->total=$this->rknclass->db->result(); //Need to do this when not using pager::run() method
		$this->pager_data=$this->rknclass->pager->paging_data();
		$this->rknclass->global_tpl->webmasters_header();
		echo "<div class=\"page-title\">All Webmaster Statistics</div>
        
 <table id=\"listings\" cellpadding=\"1\" cellspacing=\"1\">
  <tr id=\"columns\">
    <th scope=\"col\" id=\"title\">Name</th>
    <th scope=\"col\">Unique<br />Today's In</th>
    <th scope=\"col\">Unique<br />Today's Out</th>
    <th scope=\"col\">Raw<br />Today's In</th>
    <th scope=\"col\">Raw<br />Today's Out</th>
    <th scope=\"col\">Unique<br />Total In</th>
    <th scope=\"col\">Unique<br />Total Out</th>
    <th scope=\"col\">Raw<br />Total In</th>
    <th scope=\"col\">Raw<br />Total Out</th>
    <th scope=\"col\">Unique<br />" . ($this->rknclass->settings['trade_type'] === 'credits' ? "Credits" : "Ratio") . "</th>";
		$this->rknclass->db->query("SELECT * FROM " . TBLPRE . "sites WHERE approved='1' AND banned!='1' AND owner > 0 ORDER BY name ASC LIMIT " . $this->pager_data['offset'] . "," . $this->pager_data['limit']);
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
			echo "\n<tr id=\"" . ($row['owner'] === $this->rknclass->user['user_id'] ? "rows2" : "rows") . "\">
    <td id=\"title\"><a href=\"http://www.{$row['url']}\" target=\"_blank\" title=\"Position #$pos\">{$row['name']}</a></td>
    <td>{$row['u_todays_in']}</td>
    <td>{$row['u_todays_out']}</td>
    <td>{$row['r_todays_in']}</td>
    <td>{$row['r_todays_out']}</td>
    <td>{$row['u_total_in']}</td>
    <td>{$row['u_total_out']}</td>
    <td>{$row['r_total_in']}</td>
    <td>{$row['r_total_out']}</td>
    <td><strong>$ratio</strong></td>
  </tr>";
		}
		echo "\n</table>";
		echo '<div id="pagination">';
		if($this->pager_data['previous'] !== false)
		{
			echo '<a href="index.php?ctr=stats&amp;act=all_stats&amp;page=' . $this->pager_data['previous'] . '"><img src="images/pg-previous.jpg" border="0" id="previous" /></a>';
		}
		if($this->pager_data['next'] !== false)
		{
			echo '<a href="index.php?ctr=stats&amp;act=all_stats&amp;page=' . $this->pager_data['next'] . '"><img src="images/pg-next.jpg" border="0" id="next" /></a>';
		}
		echo '</div>';
		$this->rknclass->global_tpl->webmasters_footer();
	}
}
?>