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
use Illuminate\Database\Eloquent\Collection;

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

                     return $this->errorResponse('No tiene permisos para realizar ese procedimiento',403);
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

                  // como el accesor de date en Journey model
                    foreach ($journeys as $j) {
                          $date =  explode("-", $j->date);
                          $string_date = $date[2].'/'.$date[1].'/'.$date[0];
                          $j->date = $string_date;

                          $j->time = round($j->time/60/60,2);
                          
                          $userJornada =  User::where('id',$j->user_id)->first();
                          $j->user = $userJornada->getAttributes();
                    }  

            }else{

               
                     $journeys = Journey::all();
            

                foreach ($journeys as $j) {
                    $j->user;
                }
            }


             // transforma las posiciones en array para facilitar la lectura en angular--> se puede hacer con un accesor o que direcmente lo guarde como array !! 
             foreach ($journeys as $j) {
                 $valorini = $j->initial_pos;
                 $valorend = $j->final_pos;
                 $j->initial_pos = json_decode($valorini,true);
                 $j->final_pos = json_decode($valorend,true);
             }

             return $this->showAll($journeys,'journeys');
        }else{
           /* $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];*/

            return $this->errorResponse('No autenticado',401);
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

                    return $this->errorResponse('No tiene permisos para realizar ese procedimiento',403);
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
                            ->Where('user_id',$id)
                            ->Where($field, 'like', '%' . $search . '%')
                            ->get();

                    // como el accesor de date en Journey model
                    foreach ($journeys as $j) {
                          $date =  explode("-", $j->date);
                          $string_date = $date[2].'/'.$date[1].'/'.$date[0];
                          $j->date = $string_date;

                          $j->time = round($j->time/60/60,2);
                    }

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
             return $this->errorResponse('No autenticado',401);
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

                    return $this->errorResponse('No autenticado',401);
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

                            $time = time();
                            //coje la jornada activa 

                             $Ajornada = ActiveJourney::where('user_id','=',$user->sub)->first();

                             if (is_null($Ajornada->stops)){
                                  $data = array(
                                      '0' =>  $time
                                  );

                                  $dataencoded = json_encode($data,JSON_FORCE_OBJECT);
                                  
                             }else{
                                   $data = json_decode($Ajornada->stops,true);
                                   $num_paradas = count($data);

                                   //$data[$num_paradas] = time();
                                   $data += [$num_paradas =>  $time];

                                    $dataencoded = json_encode($data,JSON_FORCE_OBJECT);
                             }

                              $Ajornada->paused =  $time;
                              $Ajornada->stops = $dataencoded;

                              $Ajornada->save();

                              return $this->showOne($Ajornada,'active_paused_journey');

                        }else{

                              return $this->errorResponse('No autenticado',401);
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
                                  $num = count($data);

                                  //arregla el error del time_lost
                                  $time_lost += ($time_NOW - $data[$num-1]);
                                 /* foreach ($data as $clave => $valor) {
                                          $time_lost += ($time_NOW - $valor);
                                       }*/
                             }

                              $Ajornada->paused = 0;
                              $Ajornada->time_lost = $time_lost;

                              $Ajornada->save();

                              return $this->showOne($Ajornada,'active_continued_journey');

                        }else{

                              return $this->errorResponse('No autenticado',401);
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

                                 return $this->errorResponse('No existe jornada activa para este usuario',401);
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

                        return $this->errorResponse('No autenticado',401);
                    }

        }

 public function hasactivejourney(Request $request){
        
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

            $jornada = ActiveJourney::where('user_id',$user->sub)->get();

            $init = 0;
            $time_lost = 0;
            $paused = 0;
            $stops = 0;

            if (count($jornada) == 1){
                
                $value = true;

               if ($jornada[0]->paused != 0){
                    $paused = $jornada[0]->paused;
                }

                $init = date('H:i:s',$jornada[0]->initial_time);
                $time_lost = $jornada[0]->time_lost;
                $stops = json_decode($jornada[0]->stops,true);
            }else if (count($jornada) == 0){
               
                $value = false;
            }

            if ($paused != 0){
                $paused  = (time() - $paused)/60 + 0.01;
            }
            if ( $time_lost != 0){
                $time_lost = $time_lost/60;
            }

             $data = [
                    'status' => 'success',
                    'journey' => $value,
                    'paused' =>$paused,
                    'init' => $init,
                    'stops' => $stops,
                    'time_lost' => $time_lost
                ];
             return response()->json($data,200);

          
        }else{

        /*$data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' => 0
            ];
        }*/

        return $this->errorResponse('No autenticado',401);
    } 

}



/* Consulta los datos de un user (del token y devuelve un json con esos datos para cada gráfico) 
public function chart_data (Request $request){

            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                   // recoge el user   
                    $user = $JwtAuth->checkToken($hash,true);

                
                    $time = time();
                    $diahoy = date('Y-m-d',$time);
                    $meshoy = date('Y-m-',$time);
                    $añohoy = date('Y-',$time);

                

                    $jornadaactiva = ActiveJourney::where('user_id',$user->sub)->get();
                    $jornada = Journey::where('date',$diahoy)->get();

                    $today = 0;
                    // $semana  es mas chungo porque hay que ver que semana estamos y tal o sea que de momento no !!!
                    $mes  = Journey::where('date','like','%'.$meshoy.'%')->sum('time');
                    $año  = Journey::where('date','like','%'.$añohoy.'%')->sum('time');

                    //tiene jornada activa 
                    if (count($jornadaactiva) == 1){
                            // TODO -- quedaria quitar tmb el tiempo de las paradas
                         $today = time()-($jornadaactiva[0]->initial_time);

                         $mes += $today;
                         $año += $today;
                    }else{
                        //tiene jornada finalizada o todavia no la ha hecho
                        if (count($jornada) == 1){
                            $today = $jornada[0]->time;
                        }else if (count($jornada) == 0){
                            $today = 0;
                        }
                    }


                    // data porcentajes mios/totales y meses/cantidad de horas totales algo chulo como el de fecht json to charjs
                    $data = [
                         'data_dia' =>[
                                'labels' =>['Horas hoy','Jornada 8 horas'],
                                'data' =>[($today/60/60),8*60*60]
                         ],
                          'data_mes' =>[
                                'labels' =>['Horas hoy','8 horas/dia en un mes'],
                                'data' =>[($mes/60/60),8*60*60]
                         ] ,
                          'data_año' =>[
                                'labels' =>['Horas este año','8 horas/dia en un año'],
                                'data' =>[($año/60/60),8*60*60]
                         ]        
                    ];

                    return response()->json($data,200);



                }else{

                    return $this->errorResponse('No autenticado',401);
                } 

   
}*/

/* DATA PARA EL GRAFICO DE LÍNEA POR MES / HORAS TRABAJADAS */
public function chart_line_pormes ($id,Request $request){

            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                   // recoge el user   
                    //$user = $JwtAuth->checkToken($hash,true);
                    $user = User::find($id);
                    $nombrecompleto = $user->name.' '.$user->surname;

                
                    $time = time();
                    $añohoy = date('Y-',$time);

                    $meses = [];

                    for ($i = 1;$i<13;$i++){
                        if ($i < 10 ){
                            $num = '0'.$i;
                        }else{
                            $num = $i;
                        }
                         $valor = Journey::where('user_id',$id)
                            ->where('date','like','%'.$añohoy.$num.'-'.'%')
                            ->sum('time');
                       
                        $meses[] = round($valor/60/60,2);
                        // $meses[] = $valor;
                    }


                
                    $data = [
                        'status' => 'success',
                         'data_line' =>$meses,
                         'label' => $nombrecompleto    
                    ];

                    return response()->json($data,200);



                }else{

                    return $this->errorResponse('No autenticado',401);
                }  
}

/* DATA PARA EL GRAFICO DE Donut MISHORAS/HORASTOTALES */
public function chart_donut_porcentaje ($id,Request $request){

            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                   // recoge el user   
                   // $user = $JwtAuth->checkToken($hash,true);
                    $user = User::find($id);
                    $nombrecompleto = $user->name.' '.$user->surname;

                    $time = time();
                    $diahoy = date('Y-m-d',$time);

                     $jornadaactiva = ActiveJourney::where('user_id',$id)->get();
                    $jornada = Journey::where('user_id',$id)
                                ->where('date',$diahoy)
                                ->get();

                   $horastotales = Journey::sum('time');
                   $mishoras = Journey::where('user_id',$id)->sum('time');

                   $today = 0;
                   //tiene jornada activa 
                    if (count($jornadaactiva) == 1){
                            // TODO -- quedaria quitar tmb el tiempo de las paradas
                         $today = time()-($jornadaactiva[0]->initial_time);

                         $today -= $jornadaactiva[0]->time_lost;

                         /*if ($today > 0){
                            $today = round(($today/60/60),2);
                        }*/

                    }else{
                        //tiene jornada finalizada o todavia no la ha hecho
                        if (count($jornada) >= 1){

                            foreach ($jornada as $j) {
                                $today += $j->time;
                            }
                        }
                    }

                    $mishoras += $today;

                   if ($horastotales > 0){
                        $horastotales = round($horastotales/60/60,2);
                   }
                    if ($mishoras > 0){
                        $mishoras = round($mishoras/60/60,2);
                   }
                
                    $data = [
                        'status' => 'success',
                         'data_donut' =>
                         array(
                            $mishoras,$horastotales
                         ),
                         'label' => array($nombrecompleto,'Horas totales') 
                    ];

                    return response()->json($data,200);



                }else{

                    return $this->errorResponse('No autenticado',401);
                }  
}

/* DATA PARA EL GRAFICO DE Donut MISHORAS/ 8 horas */
public function chart_donut_dia ($id,Request $request){

            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                   // recoge el user   
                   // $user = $JwtAuth->checkToken($hash,true);

                     $user = User::find($id);
                    $nombrecompleto = $user->name.' '.$user->surname;
                    
                    $time = time();
                    $diahoy = date('Y-m-d',$time);

                    $jornadaactiva = ActiveJourney::where('user_id',$id)->get();
                    $jornada = Journey::where('user_id',$id)
                                ->where('date',$diahoy)
                                ->get();

                    $today = 0;

                    //tiene jornada activa 
                    if (count($jornadaactiva) == 1){
                            // TODO -- quedaria quitar tmb el tiempo de las paradas
                         $today = time()-($jornadaactiva[0]->initial_time);

                         $today -= $jornadaactiva[0]->time_lost;

                          if ($today > 0){
                            $today = round(($today/60/60),2);
                        }

                    }else{
                        //tiene jornada finalizada o todavia no la ha hecho
                        if (count($jornada) >= 1){

                            foreach ($jornada as $j) {
                                $today += $j->time;
                            }
                           // $today = $jornada[0]->time;
                        }
                    }

                   /* if ($today > 0){
                            $today = round(($today/60/60),2);
                        }*/

                    $data = [
                         'data_donut' =>$today,
                         'label' =>$nombrecompleto      
                    ];

                    return response()->json($data,200);

                }else{

                    return $this->errorResponse('No autenticado',401);
                }   
}
/* DATA PARA EL GRAFICO DE Donut MISHORAS/ 160 horas */
public function chart_donut_mes ($id,Request $request){

            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                   // recoge el user   
                   // $user = $JwtAuth->checkToken($hash,true);
                     $user = User::find($id);
                    $nombrecompleto = $user->name.' '.$user->surname;

                    $time = time();
                    $diahoy = date('Y-m-d',$time);
                    $meshoy = date('Y-m-',$time);

                    $jornadaactiva = ActiveJourney::where('user_id',$id)->get();
                     $jornada = Journey::where('user_id',$id)
                                ->where('date',$diahoy)
                                ->get();

                    $today = 0;
                    // $semana  es mas chungo porque hay que ver que semana estamos y tal o sea que de momento no !!!
                    $mes  = Journey::where('user_id',$id)
                            ->where('date','like','%'.$meshoy.'%')
                            ->sum('time');

                    //tiene jornada activa 
                    if (count($jornadaactiva) == 1){
                            // TODO -- quedaria quitar tmb el tiempo de las paradas
                         $today = time()-($jornadaactiva[0]->initial_time);
                         $today -= $jornadaactiva[0]->time_lost;

                       /* if ($today > 0){
                            $today = round(($today/60/60),2);
                        }*/

                    }else{
                        //tiene jornada finalizada o todavia no la ha hecho
                        if (count($jornada) >= 1){

                            foreach ($jornada as $j) {
                                $today += $j->time;
                            }
                           // $today = $jornada[0]->time;
                        }
                    }

                    $mes += $today;

                    if ($mes > 0){
                        $mes = round(($mes/60/60),2);
                    }

                    $data = [
                         'data_donut' => $mes,
                         'label' =>$nombrecompleto
                                
                    ];

                    return response()->json($data,200);

                }else{

                    return $this->errorResponse('No autenticado',401);
                }   
}

/* DATA PARA EL GRAFICO DE Donut MISHORAS/ 160 horas */
public function chart_donut_anio ($id,Request $request){

            $hash = $request->header('Authorization',null);
            $JwtAuth = new JwtAuth();
            $checkToken = $JwtAuth->checkToken($hash);


                if ($checkToken){

                   // recoge el user   
                   // $user = $JwtAuth->checkToken($hash,true);
                     $user = User::find($id);
                    $nombrecompleto = $user->name.' '.$user->surname;

                    $time = time();
                    $diahoy = date('Y-m-d',$time);
                    $añohoy = date('Y-',$time);

                    $jornadaactiva = ActiveJourney::where('user_id',$id)->get();
                    $jornada = Journey::where('user_id',$id)
                                ->where('date',$diahoy)
                                ->get();

                    $today = 0;
                   
                    $año  = Journey::where('user_id',$id)
                            ->where('date','like','%'.$añohoy.'%')
                            ->sum('time');

                    //tiene jornada activa 
                    if (count($jornadaactiva) == 1){
                            // TODO -- quedaria quitar tmb el tiempo de las paradas
                         $today = time()-($jornadaactiva[0]->initial_time);
                         $today -= $jornadaactiva[0]->time_lost;

                         if ($today > 0){
                            $today = round(($today/60/60),2);
                        }

                    }else{
                        //tiene jornada finalizada o todavia no la ha hecho
                        if (count($jornada) >= 1){

                            foreach ($jornada as $j) {
                                $today += $j->time;
                            }
                           // $today = $jornada[0]->time;
                        }
                    }

                    $año += $today;

                    if ($año > 0){
                        $año = round(($año/60/60),2);
                    }

                    $data = [
                         'data_donut' => $año,
                         'label' =>$nombrecompleto          
                    ];

                    return response()->json($data,200);

                }else{

                    return $this->errorResponse('No autenticado',401);
                }   
   }

}