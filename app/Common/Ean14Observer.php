<?php
namespace App\Common;

use App\Enums\ScheduleAction;
use App\Enums\ScheduleStatus;

class Ean14Observer {

    public static function updating($code) {
       
    }

    public static function created($code) {
    	if ($code->document_detail_id > 0) {
            $detailDocument  = $code->documentDetail;
            $document = $detailDocument->document;
            $deatils = $document->detail;
            
            $numberCodes = 0;
            $numberExpected = 0;
            foreach ($deatils as $key => $value) {
                $ean14 = $value->ean14()->count();
                if ($ean14 > 0) {                    
                    $numberCodes += $ean14;
                }
                $numberExpected += $value->quanty_received;
            }
            if ($numberCodes >=  $numberExpected) {                
                $schedulesDocuments = $document->scheduleDocument;
                foreach ($schedulesDocuments as $key2 => $docSchedule) {
                    $schedule = $docSchedule->schedule;
                    if ($schedule->schedule_action === ScheduleAction::PrintCodes) {
                        $schedule->status = ScheduleStatus::Closed;
                        $schedule->save();
                    }
                }
            }
        }
    }

    public static function deleting($code)
    {
		
    }

}
