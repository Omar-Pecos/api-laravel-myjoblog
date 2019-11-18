<?php

namespace App\Http\Controllers;

use App\Log;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;

class LogController extends ApiController
{
    // table vacations -> igual no hace falta modelo !

    public function getLogs(Request $request){
    	$hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){

        	 $logs = Log::all();

             /* $data = [
              		'status' => 'success',
              		'logs' => $logs
              	];*/
           
           //return response()->json($data,200);
             return $this->showAll($logs,'logs');
        }else{

            return $this->errorResponse('No autenticado',401);
        } 
    }

   /* public function getUserLogs(Request $request,$id){
    	$hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){

        	 $events = Log::where('user_id',$id)
                        ->get();

             // $data = [
             // 		'status' => 'success',
             // 		'events' => $events
             // 	];
           
            return $this->showAll($logs,'logs');
        }else{

            return $this->errorResponse('No autenticado',401);
        } 
    }*/

    
}
