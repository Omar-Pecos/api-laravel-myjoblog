<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;
use App\ActiveJourney;
use App\Journey;

class JourneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
                'auth' =>0
            ];
        }

         
        return response()->json($data, 200);
        }

}
