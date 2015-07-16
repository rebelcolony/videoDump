<?php
@set_time_limit(0);

class sitemap
{
    public function init()
    {}
    
    public function idx()
    {
        $this->rknclass->load_object('sitemap');
		$this->rknclass->sitemap->generate_sitemap();
    }
}