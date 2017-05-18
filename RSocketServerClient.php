<?php

class RSocketServerClient extends SocketServerClient
{
	
    protected $name = '';
	
    public function send_message($msg){
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