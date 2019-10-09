<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



class ImageController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getImage($name , Request $request){

    	 $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
           		$contents = Storage::get('images/'.$name);
            }
        else{
           		$contents = Storage::get('images/lienzo.png');
        } 


        /// hacer un json.encoded o algo asi de contents aver si asi y sino a mamarla publicas 
         $data = [
         	'status' =>'success',
         	'image' => $contents
         ];
		return response()->json($data,200);
    }
}
