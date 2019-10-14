<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePdf;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Handle Queue Process
     */
    public function processQueue(Request $request)
    {

    	$id = request()->id;
    	$year = request()->year;

   	 $data = ['antes' => time(),
   	 'msg' => 'Proceso enviado'];

      dispatch(new GeneratePdf($id,$year));

      $data ['despues'] = time();
      
      return response()->json($data,200);
    }
}
