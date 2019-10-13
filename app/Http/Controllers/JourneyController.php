<?php

namespace App\Http\Controllers;

use App\User;
use App\Journey;
use App\ActiveJourney;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class JourneyController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
             $user = $JwtAuth->checkToken($hash,true);

             if ($user->role == 'user'){
                /* $data = [
                        'status' => 'error',
                        'message' => 'No tiene permisos para realizar ese procedimiento',
                    ];*/

                     return $this->errorResponse('No tiene permisos para realizar ese procedimiento',401);
             }


              //  --->>> BUSQUEDA <<<<<----
             //     SERÍA BUENO SANITIZAR ante un SQL INJECTION 
                            // esto para quitar las tags de php y html 
                        // strip_tags ( $str [, string $allowable_tags ] ) 
            if (request()->has('search') && request()->has('field')){
                $search = request()->search;
                $field = request()->field;

               $journeys = DB::table('journeys')
                        ->Where($field, 'like', '%' . $search . '%')
                        ->get();

            }else{

                $journeys = Journey::all();
            }

            /* $data = [
                'status' => 'success',
                'journeys' =>$journeys,
             ];*/

             return $this->showAll($journeys,'journeys');
        }else{
           /* $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];*/

            return $this->errorResponse('No autenticado',409);
        } 
         
    }


    /**
     * Display the specified resource.  --- EN MI CASO DEVUELVE LAS JORNADAS DE UN USER LOGUEADO!
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // se puede renombra este metodo como miJourneys o algo así y si se necesita el show para el detalle de jornadas -> con toda la info ahí y con lo de inspeccionaar ?? igual es más limpio que en una tablar ahi !! bueno mirar ******
    public function show($id, Request $request)
    {
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
             $user = $JwtAuth->checkToken($hash,true);

             if ($user->role == 'user' && $user->sub != $id){
                /* $data = [
                        'status' => 'error',
                        'message' => 'No tiene permisos para realizar ese procedimiento',
                    ];*/

                    return $this->errorResponse('No tiene permisos para realizar ese procedimiento',401);
             }

             //  coje las jornadas del id del user que se pasa por url
           /*  $journeys = Journey::where('user_id', $id)
                        ->get();*/

            $usuario = User::find($id);
            
            // validar que el id exista !!
            if (!$usuario){
                /* $data = [
                        'status' => 'error',
                        'message' => 'No existen registros para ese usuario',
                    ];*/

                     return $this->errorResponse('No existen registros de jornadas para ese usuario',404);
             }


                if (request()->has('search') && request()->has('field')){
                    $search = request()->search;
                    $field = request()->field;

                   $journeys = DB::table('journeys')
                            ->Where($field, 'like', '%' . $search . '%')
                            ->get();

                }else{
                     $journeys = $usuario->journeys;
                }             
                   

            /* $data = [
                'status' => 'success',
                'journeys' =>$journeys,
             ];*/
              return $this->showAll($journeys,'journeys');


        }else{
            /*$data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];*/
             return $this->errorResponse('No autenticado',409);
        } 
    }


    /*$hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            echo "Index de CarController AUTENTICADO" ; die();

            // recoge el user   $user = $JwtAuth->checkToken($hash,true);
        }else{
            echo "Index de CarController NO AUTENTICADO" ; die();
        }*/

        public function init_journey(Request $request){
            
        
        // verificar token con el hash

        $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                        // recoge el user   
                        $user = $JwtAuth->checkToken($hash,true);

                        // se podría valorar por aquí si el usuario tiene ya 
                        // una jornaada activa y no dejar crear otra más , supuestamente
                        // la opc estará oculta para users pero si por url se mete


                        //la info por POST -> json
                        $json = $request->input('json',null);
                        $params = json_decode($json);
         
                        $latitud = $params->lat;
                        $longitud =  $params->lon;

                         $pos = [
                             $latitud,
                             $longitud,       
                        ]; 
                        $pos_inicial = json_encode($pos);

                    // $name= $id.$user->name;
                   // setcookie($name, time(),time()+60*60*24*30*12,"/");


                         $time = time();

                         // saca la imagen en base64 y la almacena en el storage !!
                        $image_data = $params->image;

                            $imageInfo = explode(";base64,", $image_data);
                            $imgExt = str_replace('data:image/', '', $imageInfo[0]);      
                            $image = str_replace(' ', '+', $imageInfo[1]);
                            $imageName = "sign".$user->sub."_".$time.".".$imgExt;
                            Storage::disk('images')->put($imageName, base64_decode($image));
                            

                    $active_journey = new ActiveJourney();
                   

                    // el user de jwt devuelve menos info ID == SUB
                    $active_journey->user_id = $user->sub;
                    $active_journey->date = date('Y-m-d',$time);
                    $active_journey->initial_time = $time;
                    $active_journey->initial_pos = $pos_inicial;
                    $active_journey->signature =$imageName;

                    $active_journey->save();

                   /* $data = [
                        'status' => 'success',
                        'active_journey' => $active_journey,
                        'jornada' => 1
                    ];*/

                    return $this->showOne($active_journey,'active_journey');
                   
                }else{

                    /* $data = [
                        'status' => 'error',
                        'message' => 'No autenticado',
                        'auth' =>0
                    ];*/

                    return $this->errorResponse('No autenticado',409);
                }
        }


        public function pause_journey(Request $request){
             // verificar token con el hash
            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                        if ($checkToken){

                           // recoge el user   
                            $user = $JwtAuth->checkToken($hash,true);

                            //coje la jornada activa 

                             $Ajornada = ActiveJourney::where('user_id','=',$user->sub)->first();

                             if (is_null($Ajornada->stops)){
                                  $data = array(
                                      '0' => time() 
                                  );

                                  $dataencoded = json_encode($data,JSON_FORCE_OBJECT);
                                  
                             }else{
                                   $data = json_decode($Ajornada->stops,true);
                                   $num_paradas = count($data);

                                   //$data[$num_paradas] = time();
                                   $data += [$num_paradas => time()];

                                    $dataencoded = json_encode($data,JSON_FORCE_OBJECT);
                             }

                              $Ajornada->paused = 1;
                              $Ajornada->stops = $dataencoded;

                              $Ajornada->save();

                              return $this->showOne($Ajornada,'active_paused_journey');

                        }else{

                              return $this->errorResponse('No autenticado',409);
                    }
        }

        public function continue_journey(Request $request){
             // verificar token con el hash
            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                        if ($checkToken){

                           // recoge el user   
                            $user = $JwtAuth->checkToken($hash,true);

                            //coje la jornada activa 

                             $Ajornada = ActiveJourney::where('user_id','=',$user->sub)->first();

                             $time_NOW = time();

                             $time_lost = $Ajornada->time_lost;

                             if (is_null($Ajornada->stops)){
                                 $time_lost = 0;
                             }else{
                                   $data = json_decode($Ajornada->stops,true);
                                  
                                  foreach ($data as $clave => $valor) {
                                          $time_lost += ($time_NOW - $valor);
                                       }
                             }

                              $Ajornada->paused = 0;
                              $Ajornada->time_lost = $time_lost;

                              $Ajornada->save();

                              return $this->showOne($Ajornada,'active_continued_journey');

                        }else{

                              return $this->errorResponse('No autenticado',409);
                    }
        }

        public function end_journey(Request $request){
        
        // verificar token con el hash
        $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);


                    if ($checkToken){

                            // recoge el user   
                            $user = $JwtAuth->checkToken($hash,true);

                            //la info por POST -> json
                            $json = $request->input('json',null);
                            $params = json_decode($json);
             
                            $latitud = $params->lat;
                            $longitud =  $params->lon;

                        // $name= $id.$user->name;
                       // setcookie($name, time(),time()+60*60*24*30*12,"/");
                        

                        $pos = [
                             $latitud,
                             $longitud,       
                        ];
                        
                        $pos_final = json_encode($pos);

                        // sacar la jornada activa del user

                       

                        // si el user no tiene jornada activa no se debe poder finalizar !!! tener cuidado ahí ---> En principio no HARA FALTA !!! TODELETE
                            $Ajornada = ActiveJourney::where('user_id','=',$user->sub)->first();

                            if (!$Ajornada){
                               /* $data = [
                                    'status' => 'error',
                                    'message' => 'No existe jornada activa para este usuario',
                                ];*/

                                 return $this->errorResponse('No existe jornada activa para este usuario',409);
                            }

                        $journey = new Journey();
                        $now = time();
                        $Ttime = $now - $Ajornada->initial_time;

                        // el user de jwt devuelve menos info ID == SUB
                        $journey->user_id = $user->sub;
                        $journey->date = $Ajornada->date;
                        $journey->initial_time = date('H:i:s',$Ajornada->initial_time);
                        $journey->final_time = date('H:i:s',$now);
                        $journey->initial_pos = $Ajornada->initial_pos;
                        $journey->final_pos = $pos_final;
                        // quitandole el tiempo de las paradas
                        $journey->time = $Ttime - $Ajornada->time_lost;

                        $journey->stops = $Ajornada->stops;
                        $journey->signature = $Ajornada->signature;

                        $journey->save();


                        // si la journey se ha creado bien --> elimina la AJourney

                        $Ajornada->delete();

                       /* $data = [
                            'status' => 'success',
                            'journey' => $journey
                        ];*/

                         return $this->showOne($journey,'journey');
                       
                    }else{

                         /*$data = [
                            'status' => 'error',
                            'message' => 'No autenticado',
                            'auth' => 0
                        ];*/

                        return $this->errorResponse('No autenticado',409);
                    }

        }

       public function hasactivejourney(Request $request){
        
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

            $jornada = ActiveJourney::where('user_id',$user->sub)->get();

            if (count($jornada) == 1){
                
                $value = true;

                if ($jornada->paused == 0){
                    $paused = false;
                }else if ($jornada->paused == 1){
                    $paused = true;
                }
            }else if (count($jornada) == 0){
               
                $value = false;
                 $paused = false;
            }

             $data = [
                    'status' => 'success',
                    'journey' => $value,
                    'paused' => $paused
                ];
             return response()->json($data,200);

          
        }else{

        /*$data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' => 0
            ];
        }*/

        return $this->errorResponse('No autenticado',409);
    } 

}

}