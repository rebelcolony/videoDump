<?php
class dashboard extends rkn_render
{
	public function init()
	{
		if($this->rknclass->session->is_guest === true)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php"));
		}
		
		if($this->rknclass->user['total_sites']<1)
		{
			exit(header("Location: {$this->rknclass->settings['site_url']}/webmasters/index.php?ctr=sites&act=add_site"));
		}
		
		$this->rknclass->load_objects(array('global_tpl', 'form'));
	}
	
	public function idx()
	{
		$this->rknclass->page_title='Webmaster Dashboard';
		$this->rknclass->get['ctr']='stats';
		$this->load_controller();
	}

}
?>