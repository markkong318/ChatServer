<?php
require_once 'SocketServer.php';
require_once 'SocketServerClient.php';
require_once 'RSocketServer.php';
require_once 'RSocketServerClient.php';

$server = new RSocketServer("172.31.35.49",9001); // Binds to determined IP
$server->infinite_loop(); // starts the loop.

