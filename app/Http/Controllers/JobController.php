<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Jobs\GeneratePdf;
use Illuminate\Http\Request;
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

                  /* una mejor respuesta para sacar la info en infomsg */
                 $data = ['antes' => time(),
                 'msg' => 'Proceso enviado'];

                  dispatch(new GeneratePdf($user,$id,$year));

                  $data ['despues'] = time();
                  
                  return response()->json($data,200);

         }else{
  
         return $this->errorResponse('No autenticado',409);
    } 


    	
    }
}
