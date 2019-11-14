<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;

class VacationController extends ApiController
{
    // table vacations -> igual no hace falta modelo !

    public function getVacations(Request $request){
    	$hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){

        	 $events = DB::table('vacations')
                            ->orderBy('id')
                            ->get();

              $data = [
              		'status' => 'success',
              		'events' => $events
              	];
           
           return response()->json($data,200);
            // return $this->showAll($journeys,'journeys');
        }else{
           /* $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];*/

            return $this->errorResponse('No autenticado',409);
        } 
    }

    public function getUserVacations(Request $request,$id){
    	$hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){

        	 $events = DB::table('vacations')
        	 				      ->where('user_id',$id)
                        ->orderBy('id')
                            ->get();

              $data = [
              		'status' => 'success',
              		'events' => $events
              	];
           
           return response()->json($data,200);
            // return $this->showAll($journeys,'journeys');
        }else{
           /* $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];*/

            return $this->errorResponse('No autenticado',409);
        } 
    }

     public function addVacation(Request $request)      
    {
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

              //Recoger post
        $json = $request->input('json',null);

        $params = json_decode($json);
        $params_array = json_decode($json,true);
       
       
       
             DB::table('vacations')->insert($params_array);
            
 				$data = [
              		'status' => 'success',
              		'newevent' => $params_array
              	];
            
             return response()->json($data,200);

             // return response()->json($usuario,200);
        
        }else{
 
             return $this->errorResponse('No autenticado',409);
        }

    }

    public function editVacation(Request $request)      
    {
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

              //Recoger post
        $json = $request->input('json',null);

        
        //$params = json_decode($json);
        $params_array = json_decode($json,true);
       	
       	$id = $params_array['event_id'];
       	unset($params_array['event_id']);
       
       
             DB::table('vacations')
		             ->where('id', $id)
		             ->update($params_array);
		            
 				$data = [
              		'status' => 'success',
              		'editedevent' => $params_array
              	];
            
             return response()->json($data,200);

             // return response()->json($usuario,200);
        
        }else{
 
             return $this->errorResponse('No autenticado',409);
        }

    }

    public function deleteVacation(Request $request,$id)      
    {
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

       
       		// eliminar de BBDD
             DB::table('vacations')
		             ->where('id', $id)
		             ->delete();
		            
 				$data = [
              		'status' => 'success',
              		'deletedevent' => true
              	];
            
             return response()->json($data,200);

             // return response()->json($usuario,200);
        
        }else{
 
             return $this->errorResponse('No autenticado',409);
        }

    }

 

}
