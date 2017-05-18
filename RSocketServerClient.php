<?php

class RSocketServerClient extends SocketServerClient
{
	
    protected $name = '';

    protected $is_bot = false;

    public function __construct(){
        $this->is_bot = true;
    }
	
    public function send_message($msg){

        if($this->is_bot){
            return;
        }

        $msg .= "\r\n";
        socket_write($this->socket, $msg, strlen($msg) );
    }

    function &__get($name)
    {
        echo "get $name";
        return $this->{$name};
    }

    function __isset($name)
    {
        return isset($this->{$name});
    }
}