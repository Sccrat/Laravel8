<?php
namespace App\Common;

use ElephantIO\Client as Elephant;
use ElephantIO\Engine\SocketIO\Version1X as Version1X;

class SocketEvents {

    public static function newTask($schedule) {   		 	
		$apiUrl = env("SOCKET_SERVER","http://localhost:3000/");
		try {
	        $client = new Elephant(new Version1X($apiUrl));
			$client->initialize();
			$client->emit('notify-schedule', [
				'socket_id' => $schedule->user->socket_id,
				'schedule' => $schedule
				]);
			$client->close();
		} catch (Exception $e) {
			
		}
    }

    public static function deleteTask($schedule)
    {  	
		$apiUrl = env("SOCKET_SERVER","http://localhost:3000/"); 
		try {
	        $client = new Elephant(new Version1X($apiUrl));
			$client->initialize();
			$client->emit('notify-schedule-remove', [
				'socket_id' => $schedule->user->socket_id,
				'schedule' => $schedule
				]);
			$client->close();
			
		} catch (Exception $e) {
			
		}	
    }
    public static function updateTask($schedule)
    {  	
		$apiUrl = env("SOCKET_SERVER","http://localhost:3000/"); 	
		try {
	        $client = new Elephant(new Version1X($apiUrl));
			$client->initialize();
			$client->emit('notify-schedule-load', [
				'socket_id' => $schedule->user->socket_id,
				'schedule' => $schedule
				]);
			$client->close();			
		} catch (Exception $e) {
			
		}

    }

}
