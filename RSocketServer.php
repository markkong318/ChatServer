<?php

class RSocketServer extends SocketServer
{

	private $rooms = array();

    private $bot_clients = array();
	
    public function __construct($bind_ip,$port){
        $this->client_class = 'RSocketServerClient';

        parent::__construct($bind_ip,$port);

        $this->hook("connect","connect_function");
        $this->hook("input","handle_input");

        $this->rooms['chat'] = array();

        $this->rooms['hottub'] = array();

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'gc';
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'foo';
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'user1';
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'user2';
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'y2kcrisis';
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'hottub1';
        $this->bot_clients[] = $bot_client;
        $this->rooms['hottub'] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'hottub2';
        $this->bot_clients[] = $bot_client;
        $this->rooms['hottub'] = $bot_client;
    }


    public function handle_connect($server,$client, $input){
        $client->send_message("Welcome to the XYZ chat server");

        $client->send_message("Login Name?");
    }

    public function handle_disconnect($server,$client, $input){
    }

    public function handle_input($server,$client, $input){
		if($client->name == ''){
			$this->process_name($server,$client, $input);

			return;
		}

		if($this->process_cmd($server,$client, $input)){

		}
    }

	private function process_name($server,$client, $input){
		$input = preg_replace('/\r\n$/','',$input);

		echo ">$input<\n";

		if($input != 'gc_reviewer'){
			$client->send_message("Sorry, name taken");
			$client->send_message("Login Name?");

		}else{
			$client->name = $input;
			$client->send_message("Welcome ".$client->name);
		}

		return true;
	}

	private function process_cmd($server,$client, $input){

		if($this->startsWith($input, '/rooms')){

            $client->send_message("Active rooms are:");

            foreach($this->rooms as $key => $clients){
                $client->send_message("* " + $key + " (" + count($clients) + ")");
            }

            $client->send_message("end of lists.");

		}else if($this->startsWith($input, '/join')){

            $client->send_message("entering room:");

		}else if($this->startsWith($input, '/leave')){

		}else if($this->startsWith($input, '/quit')){
			$client->send_message("BYE");
			$this->disconnect($client->server_clients_index);
		}
	}

	private function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	private function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}
}