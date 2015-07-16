<?php
@set_time_limit(0);

class update
{
    public function init()
    {}
    
    public function idx()
    {
        $this->rknclass->load_object('update');
		$this->rknclass->update->execute();
    }
}