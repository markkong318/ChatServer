<?php

class RSocketServer extends SocketServer
{

    public function __construct($bind_ip,$port){
        $this->client_class = 'RSocketServerClient';

        parent::__construct($bind_ip,$port);

        $this->hook("connect","connect_function");
        $this->hook("input","handle_input");
    }


    public function handle_connect($server,$client, $input){
        $client->send_message("Welcome to the XYZ chat server");
        $client->getStage = RSocketServerClient::STAGE_LOGIN_NAME;

        $client->send_message("Login Name?");
    }

    public function handle_disconnect($server,$client, $input){
        $client->send_message("ff");
        if($client->stage == RSocketServerClient::STAGE_LOGIN_NAME){

        }
    }

    public function handle_input($server,$client, $input){
        echo __FUNCTION__." ".$input."\n";
        foreach($server->clients as $c) {
            socket_write($c->socket, $input, strlen($input));
        }
    }


}