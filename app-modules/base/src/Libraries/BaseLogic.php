<?php

namespace Modules\Base\Libraries;

class BaseLogic
{
    protected $result = '';

    public function __construct()
    {
        //dd(explode('\\', get_class($this)));
    }

    public function set($key, $value)
    {
        $this->$key = $value;
    }

    public function run()
    {
        $this->preExecute();
        $this->execute();

        return $this->result;
    }

    protected function preExecute()
    {
    }

    protected function execute()
    {
    }
}
