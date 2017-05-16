<?php

function connect_function($server,$client, $input){
    echo __FUNCTION__."\n";
}

function handle_input($server,$client, $input){
    echo __FUNCTION__." ".$input."\n";
    foreach($server->clients as $c) {
        socket_write($c->socket, $input, strlen($input));
    }
}

$server = new SocketServer("127.0.0.1",9001); // Binds to determined IP
$server->hook("connect","connect_function"); // On connect does connect_function($server,$client,"");
$server->hook("disconnect","disconnect_function"); // On disconnect does disconnect_function($server,$client,"");
$server->hook("input","handle_input"); // When receiving input does handle_input($server,$client,$input);
$server->infinite_loop(); // starts the loop.

