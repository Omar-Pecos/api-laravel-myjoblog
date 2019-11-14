<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



class ImageController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getImage(Request $request){

           /* $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){*/

             $name = $request->name;
             $path = storage_path("app/firmas/".$name);

             $response = response()->file($path);
               
             $response = $this->cacheResponse($response);
             return $response;

           /* }else{
                 return $this->errorResponse('No autenticado',409);
             } */
    }

    protected function cacheResponse($data){
         $url = request()->url();
         $queryParams = request()->query();

         ksort($queryParams);

         $queryString = http_build_query($queryParams);

         $fullUrl = "{$url}?{$queryString}";

         return Cache::remember($fullUrl,30/60, function() use ($data){
            return $data;
         });
   }
}
