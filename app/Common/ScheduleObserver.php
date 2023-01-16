<?php
namespace App\Common;

use App\Common\SocketEvents;

class ScheduleObserver {

    public static function updating($schedule) {
        if($schedule->user_id > 0){
            if ($schedule->user->socket_id) {               
               SocketEvents::updateTask($schedule);            
            }   
        }
    }

    public static function created($schedule) {
    	if($schedule->user_id > 0){
    		if ($schedule->user->socket_id) {    		 	
		       SocketEvents::newTask($schedule);
    		}	
    	}
    }

    public static function deleting($schedule)
    {
		if($schedule->user_id > 0){
    		if ($schedule->user->socket_id) {    		 	
		       SocketEvents::deleteTask($schedule);		       
    		}	
    	}
    }

}
