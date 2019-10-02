<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;
use App\ActiveJourney;
use App\Journey;
use App\User;

class JourneyController extends Controller
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
                 $data = [
                        'status' => 'error',
                        'message' => 'No tiene permisos para realizar ese procedimiento',
                    ];

                     return response()->json($data, 200);
             }

             //  mirar como filtrar por eloquent y por dos variables $orden y $campo_Ordenacion --> ver el APi 5.4 como hacia pa filtrar y el paginado !!!
             $journeys = Journey::all();

             $data = [
                'status' => 'success',
                'journeys' =>$journeys,
             ];
        }else{
            $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];
        } 
         
        return response()->json($data, 200);
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
                 $data = [
                        'status' => 'error',
                        'message' => 'No tiene permisos para realizar ese procedimiento',
                    ];

                     return response()->json($data, 200);
             }

             //  coje las jornadas del id del user que se pasa por url
           /*  $journeys = Journey::where('user_id', $id)
                        ->get();*/

            $usuario = User::find($id);

            if (!$usuario){
                 $data = [
                        'status' => 'error',
                        'message' => 'No existen registros para ese usuario',
                    ];

                     return response()->json($data, 200);
             }

            // validar que el id exista !!

            $journeys = $usuario->journeys;

             $data = [
                'status' => 'success',
                'journeys' =>$journeys,
             ];
        }else{
            $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];
        } 
         
        return response()->json($data, 200);
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

            // $name= $id.$user->name;
           // setcookie($name, time(),time()+60*60*24*30*12,"/");
            
            $pos = [
                 $latitud,
                 $longitud,       
            ];
            
            $pos_inicial = json_encode($pos);

            $active_journey = new ActiveJourney();
            $time = time();

            // el user de jwt devuelve menos info ID == SUB
            $active_journey->user_id = $user->sub;
            $active_journey->date = date('Y-m-d H:i:s',$time);
            $active_journey->initial_time = $time;
            $active_journey->initial_pos = $pos_inicial;
            $active_journey->signature ='f8-1561385730.png';

            $active_journey->save();

            $data = [
                'status' => 'success',
                'active_journey' => $active_journey,
                'jornada' => 1
            ];
           
        }else{

             $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];
        }

         
        return response()->json($data, 200);
       
    
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

           

            // si el user no tiene jornada activa no se debe poder finalizar !!! tener cuidado ahí
                $Ajornada = ActiveJourney::where('user_id','=',$user->sub)->first();

                if (!$Ajornada){
                    $data = [
                        'status' => 'error',
                        'message' => 'No existe jornada activa para este usuario',
                    ];

                     return response()->json($data, 200);
                }

            $journey = new Journey();
            $now = time();
            $Ttime = $now - $Ajornada->initial_time;

            // el user de jwt devuelve menos info ID == SUB
            $journey->user_id = $user->sub;
            $journey->date = $Ajornada->date;
            $journey->initial_time = date('Y-m-d H:i:s',$Ajornada->initial_time);
            $journey->final_time = date('Y-m-d H:i:s',$now);
            $journey->initial_pos = $Ajornada->initial_pos;
            $journey->final_pos = $pos_final;
            $journey->time = $Ttime;
            $journey->signature = $Ajornada->signature;

            $journey->save();


            // si la journey se ha creado bien --> elimina la AJourney

            $Ajornada->delete();

            $data = [
                'status' => 'success',
                'journey' => $journey
            ];
           
        }else{

             $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' => 0
            ];
        }

         
        return response()->json($data, 200);
        }

}
