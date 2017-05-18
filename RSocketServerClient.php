<?php

class RSocketServerClient extends SocketServerClient
{
	
    protected $name = '';

    protected $room = '';

    protected $is_bot = false;

    protected $events = array();
//    protected

    public function __construct(&$socket = null,$i = null){

        if(func_num_args() == 0) {
            $this->is_bot = true;
            return;
        }

        parent::__construct($socket,$i);

    }

    public function send_message($msg){

        if($this->is_bot){
            return;
        }

        $msg .= "\r\n";
        socket_write($this->socket, $msg, strlen($msg) );
    }

    public function trigger_event($event_name, $arg_array){
        if(!isset($this->events[$event_name])){
            return;
        }

        $this->events[$event_name]($arg_array);
    }

    function &__get($name)
    {
        return $this->{$name};
    }

    function __isset($name)
    {
        return isset($this->{$name});
    }
}