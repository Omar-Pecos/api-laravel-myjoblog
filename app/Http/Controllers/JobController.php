<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Jobs\GeneratePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;

class JobController extends ApiController
{
    /**
     * Handle Queue Process
     */
    public function processQueue(Request $request)
    {
       $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
             $user = $JwtAuth->checkToken($hash,true);

                  $id = request()->id;
                  $year = request()->year;
                  $identificador = request()->user_id;

                  /* una mejor respuesta para sacar la info en infomsg */
                 $data = ['antes' => time(),
                 'msg' => 'Proceso enviado'];

                  dispatch(new GeneratePdf($user,$id,$year,$identificador));

                  $data ['despues'] = time();
                  $tiempo = $data['despues'] - $data['antes'];
                  $data['tiempo'] = $tiempo;
                  
                  $string = '';
                  if ($id == 'all'){
                    if ($identificador == 0){
                        $string = 'de todos los usuarios en '.$year;
                    }else{
                        $string = 'del usuario con ID '.$identificador.' en '.$year;
                    }
                  }else{
                      $string = 'automático';
                  }

                $now = time();
                $fechayhora = date("Y-m-d H:i:s",$now);
                  DB::statement('call regaccion(?,?,?,?,?)',array($user->sub,$user->name.' '.$user->surname,$user->role,'Generado un pdf '.$string,$fechayhora));
                  return response()->json($data,200);

         }else{
  
         return $this->errorResponse('No autenticado',401);
    } 


      
    }
}
