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
        $bot_client->is_bot = true;
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'][] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'foo';
        $bot_client->is_bot = true;
        $bot_client->events['user_join_room'] = function($arg_array){
            $client = $arg_array[0];
            foreach($this->rooms["chat"] as $room_clients){
                $room_clients->send_message("foo: welcome ".$client->name."!");
            }
        };
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'][] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'user1';
        $bot_client->is_bot = true;
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'][] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'user2';
        $bot_client->is_bot = true;
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'][] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'y2kcrisis';
        $bot_client->is_bot = true;
        $this->bot_clients[] = $bot_client;
        $this->rooms['chat'][] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'hottub1';
        $bot_client->is_bot = true;
        $this->bot_clients[] = $bot_client;
        $this->rooms['hottub'][] = $bot_client;

        $bot_client = new RSocketServerClient();
        $bot_client->name = 'hottub2';
        $bot_client->is_bot = true;
        $this->bot_clients[] = $bot_client;
        $this->rooms['hottub'][] = $bot_client;
    }


    public function handle_connect($server,$client, $input){
        $client->send_message("Welcome to the XYZ chat server");

        $client->send_message("Login Name?");
    }

    public function handle_disconnect($server,$client, $input){
        if($client->room != '') {
            foreach ($this->rooms[$client->room] as $key => $room_client) {
                if ($room_client == $client) {
                    unset($this->rooms[$client->room][$key]);
                    break;
                }
            }

            $client->room = '';
        }
        return;
    }

    public function handle_input($server,$client, $input){
        $inputs = explode('\r\n$', $input);

        for($i = 0;$i < count($inputs) - 1; $i++){
            $this->handle_input2($server,$client, $inputs[$i]);
        }
        //echo print_r($inputs, true);
    }

    public function handle_input2($server,$client, $input){
        $input = preg_replace('/\r\n$/','',$input);

		if($client->name == ''){
			$this->process_name($server,$client, $input);

			return;
		}

		if($this->process_cmd($server,$client, $input)){
            return;
		}

        $this->process_chat($server,$client, $input);
    }


	private function process_name($server,$client, $input){
		$input = preg_replace('/\r\n$/','',$input);

        $is_taken = false;

        foreach($this->clients as $server_client){
            if($server_client->name == $input){
                $is_taken = true;
                break;
            }
        }

        if(!$is_taken){
            foreach($this->bot_clients as $bot_client){
                if($bot_client->name == $input) {
                    $is_taken = true;
                    break;
                }
            }
        }

		if($is_taken){
			$client->send_message("Sorry, name taken");
			$client->send_message("Login Name?");

		}else{
			$client->name = $input;
			$client->send_message("Welcome ".$client->name."!");
		}

		return true;
	}

	private function process_cmd($server,$client, $input){

		if($this->startsWith($input, '/rooms')){

            $client->send_message("Active rooms are:");
            echo print_r($this->rooms, true);
            foreach($this->rooms as $room_name => $clients){
                $client->send_message("* ". $room_name." (".count($clients).")");
            }

            $client->send_message("end of lists.");

            return true;

		}else if($this->startsWith($input, '/join')){

            if(!preg_match('/^(\/join)\s(.+)$/', $input, $match)){
                return true;
            }

            if($client->room != ''){
                $client->send_message("you has been in ".$client->room);
                return true;
            }

            $room_name = $match[2];

            if(!isset($this->rooms[$room_name])){
                $client->send_message("no room called ".$room_name);
                return true;
            }

            $this->rooms[$room_name][] = $client;
            $client->room = $room_name;

            $client->send_message("entering room: ".$room_name);

            $room = $this->rooms[$room_name];
            usort($room, function($a, $b){
               return strcmp($a->name, $b->name);
            });

            foreach($room as $room_client){
                $msg = "* ".$room_client->name." ";

                if($room_client == $client){
                    $msg .= "(** this is you)";
                }
                $client->send_message($msg);
            }

            $client->send_message("end of lists.");

            foreach($room as $room_client){
                if($room_client != $client) {
                    $room_client->trigger_event('user_join_room', array($client));
                    $room_client->send_message("* new user joined chat: ".$client->name);
                }
            }

            return true;

		}else if($this->startsWith($input, '/leave')){

            if($client->room == ''){
                $client->send_message('you have not joined any room');
                return true;
            }

            $msg = '* user has left chat: '.$client->name;
            foreach($this->rooms[$client->room] as $room_client){
                if($room_client == $client) {
                    $room_client->send_message($msg."(** this is you)");
                }else{
                    $room_client->send_message($msg);
                }
            }

            foreach($this->rooms[$client->room] as $key => $room_client){
                if($room_client == $client) {
                    unset($this->rooms[$client->room][$key]);
                    break;
                }
            }

            $client->room = '';

            return true;

		}else if($this->startsWith($input, '/quit')){
			$client->send_message("BYE");
			$this->disconnect($client->server_clients_index);

            return true;
		}
        return false;
	}

    private function process_chat($server,$client, $input){
        if($client->room == ''){
            $client->send_message('you have not joined any room');

            return;
        }

        foreach($this->rooms[$client->room] as $room_client){
            $room_client->send_message($client->name.': '.$input);
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