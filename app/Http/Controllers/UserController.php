<?php

namespace App\Http\Controllers;

use App\User;
use App\Export;
use App\Journey;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class UserController extends ApiController
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
                    ];
                     return response()->json($data, 200);*/
                     return $this->errorResponse('No tiene permisos para realizar ese procedimiento',403);
            }


        // Si hay una búsqueda !! --->>> BUSQUEDA <<<<<----
             //     SERÍA BUENO SANITIZAR ante un SQL INJECTION 
                            // esto para quitar las tags de php y html 
                        // strip_tags ( $str [, string $allowable_tags ] ) 

        if (request()->has('search') && request()->has('field')){
            $search = request()->search;
            $field = request()->field;

          $users = DB::table('users')
                    ->Where($field, 'like', '%' . $search . '%')
                    ->get(['id','name','surname','number','email','dni','role','active','created_at','updated_at']);

          // COMO HACEN MIS ACCESORES
           foreach ($users as $user) {
              $user->name = ucfirst($user->name);
              $user->surname = ucwords($user->surname);
            }
                    /* Consulta insegura ante SQL injection
                    $users = DB::table('users')
                        ->whereRaw('dni ='.$search, [200])
                        ->get();
                   */
        }
        else{
           // saca los datos con todo OK !!
             $users = User::all();
        }
          /*  $data = [
                'status' => 'success',
                'users' => $users
            ];*/

          return $this->showAll($users,'users');

        }else{
           /* $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];

             return response()->json($data, 200);*/
            return $this->errorResponse('No autenticado',401);
        } 
         
       
    }

     public function get2first(Request $request)
    {
        $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

            if ($user->role == 'user'){
              
                     return $this->errorResponse('No tiene permisos para realizar ese procedimiento',403);
            }

             $users = User::all()->take(2)->pluck('id');
       
              $data = [
                'status' => 'success',
                'users' => $users
              ];


          //return $this->showAll($users,'users');
              return response()->json($data,200);

        }else{

            return $this->errorResponse('No autenticado',401);
        }   
       
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id , Request $request) // mis datos para editar o detalle ,
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
            
            $usuario = User::find($id);

            if (!$usuario){
                /* $data = [
                        'status' => 'error',
                        'message' => 'No existe ese usuario',
                    ];*/

                     return $this->errorResponse('No existe ese usuario',404);
             }

           /* $data = [
                'status' => 'success',
                'user' => $usuario
            ];*/

               return $this->showOne($usuario,'user');

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)      
    {
         $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
            $user = $JwtAuth->checkToken($hash,true);

            if ($user->role == 'user' && $user->sub != $id){
               /*  $data = [
                        'status' => 'error',
                        'message' => 'No tiene permisos para realizar ese procedimiento',
                    ];*/

                  return $this->errorResponse('No tiene permisos para realizar ese procedimiento',403);
            }
            // coje el valor de User del que quiere ser editado
            $usuario = User::find($id);

            if (!$usuario){
                /* $data = [
                        'status' => 'error',
                        'message' => 'No existe ese usuario',
                    ];*/

                     return $this->errorResponse('No existe ese usuario',404);
             }

              //Recoger post
        $json = $request->input('json',null);

        $params = json_decode($json);
        $params_array = json_decode($json,true);

        //validar los datos
            $validate = \Validator::make($params_array,[
                'name' => 'string|min:3',
                'surname' =>'string',
                'number' =>'digits:9',
                'email' =>  Rule::unique('users')->ignore($id),
                'dni' =>'max:9',
                'password' =>'confirmed',
            ]);

            if ($validate->fails()){
                return response()->json($validate->errors()->getMessages(),400);
            }

            // igual hay que unsetear el campo ACTIVE SI CAMBIA ? Y EL CAMPO ROLE !!!! 
              unset($params_array['id']);
              unset($params_array['created_at']);

              if (isset($params->password)){
                $pwd = hash('sha256',$params->password);
                $params_array['password'] = $pwd;
              }
               
              // actualizar el usuario
              $usuario->update($params_array);

            /*  $data = [
                'status' => 'success',
                'user' => $usuario
            ];*/

        $now = time();
        $fechayhora = date("Y-m-d H:i:s",$now);
            DB::statement('call regaccion(?,?,?,?,?)',array($user->sub,$user->name.' '.$user->surname,$user->role,'Edición de '.$usuario->name.' '.$usuario->surname,$fechayhora));

             return $this->showOne($usuario,'user');
        
        }else{
            // Devolver el error no logged
           /* $data = [
                'status' => 'error',
                'message' => 'No autenticado',
                'auth' =>0
            ];*/

             return $this->errorResponse('No autenticado',401);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {

      $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){
             $usuario = $JwtAuth->checkToken($hash,true);
            $user = User::find($id);


            // PUEDE METERSE TODO EN UNA TRANSACCION EN VERDAD

             // con sus jornadas y elemntos de imagen por supuesto tmb borrados !
              $journeys = Journey::where('user_id',$id)->get();

              //delete los files relacionados
              if (count($journeys) > 0){
                  foreach ($journeys as $j) {
                     Storage::disk('images')->delete($j->signature);
                }
              }
              

              // delete de la tabla trigger pdf
                 DB::table('trigger_pdf')->where('user_id', '=', $id)->delete();

              //borra los exports y de su tabla

                  $names = Export::where('user_id',$id)->get();

              if (count($names) > 0){
                    foreach ($names as $file) {
                        Storage::disk('local')->delete('pdf/'.$file->namefile);
                        $file->delete();
                    }
              }
              // elimina las jornaadas  de este user
               if (count($journeys) > 0){
                    foreach ($journeys as $j) {
                         $j->delete();
                    }
              }
            
              /*sacar las vacaciones y eliminarlas */
               $vacations = DB::table('vacations')->where('user_id', '=', $id)->delete();

              // ELIMINA EL USER

                  $user->delete();

                   $now = time();
                  $fechayhora = date("Y-m-d H:i:s",$now);
                  DB::statement('call regaccion(?,?,?,?,?)',array($usuario->sub,$usuario->name.' '.$usuario->surname,$usuario->role,'Borrado de '.$user->name.' '.$user->surname.' y sus registros asociados',$fechayhora));

              return $this->showOne($user,'userdeleted');

        }else{

            return $this->errorResponse('No autenticado',401);
        }   
       
       
    }

     public function register(Request $request){   // devuelve los errors a pelo ahi { 'name' => 'debe ser ..' }
        //Recoger post
        $json = $request->input('json',null);

        $params = json_decode($json);
        $params_array = json_decode($json,true);

      /*  $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $number = (!is_null($json) && isset($params->number)) ? $params->number : null;
        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $dni = (!is_null($json) && isset($params->dni)) ? $params->dni : null;
        $role = 'user';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;*/

        $role = 'user';
        $active = 0;

        if (isset($params->role)){
            $role = $params->role;
        }
        if ($params->id == 99){
          $active = 1;
        }

        //validar los datos
            $validate = \Validator::make($params_array,[
                'name' => 'required|string|min:3',
                'surname' =>'required|string',
                'number' =>'digits:9',
                'email' =>'email|required',
                'dni' =>'required|max:9',
                'password' =>'required|confirmed',
            ]);

            //errors()->getMessages();
            if ($validate->fails()){
                return response()->json($validate->errors()->getMessages(),400);
            }

            //Crear el usuario 
            $user = new User();

            $user->name = $params->name;
            $user->surname = $params->surname;
            $user->number = isset($params->number) ? $params->number : null ;
             $user->email = $params->email;
             $user->dni = $params->dni;
            $user->role = $role;
            $user->active = $active;

            $pwd = hash('sha256',$params->password);
            $user->password = $pwd;

            // Comprobar usuarios duplicados
            $isset_user = User::where('email','=',$params->email)
                ->get();

            if (count($isset_user) == 0){
                //Guardarlo
                $user->save();

                /* CREA LOS DATOS EN TRIGGER_PDF NOSE SI COGE BIEN EL ID QUE TOCA */
                    DB::table('trigger_pdf')->insert([
                        'user_id' => $user->id,
                        'quantity' => 0,
                        'id_journeys' => '0',
                        'done' => 0
                    ]);

              /*  $data = array(
              'status' => 'success',
              'code' => 200,
              'message' => 'Usuario registrado correctamente');*/

              // Creacion desde el sistema por parte de un ADMIN
               $now = time();
              $fechayhora = date("Y-m-d H:i:s",$now);
                DB::statement('call regaccion(?,?,?,?,?)',array(0,$user->name.' '.$user->surname,$user->role,'Registro de '.$user->name.' '.$user->surname,$fechayhora));
              
              return $this->showOne($user,'registered');
            }
            else{
              /*  $data = array(
              'status' => 'error',
              'code' => 400,
              'message' => 'Usuario duplicado, ya existe una cuenta con ese correo'
            );   */

            
            return $this->errorResponse('Usuario duplicado, ya existe una cuenta con ese correo',409);
        }

    }

     public function login(Request $request){
        
        $jwtAuth = new JwtAuth();

        // recibir los datos por post 

        $json = $request->input('json',null);
        $params = json_decode($json);

        $email =(!is_null ($json) && isset ($params->email)) ? $params->email : null; 
        $password = (!is_null ($json) && isset ($params->password)) ? $params->password : null; 
        $getToken = (!is_null ($json) && isset ($params->gettoken)) ? $params->gettoken : null; 

        // cifrar la constraseña
        $pwd = hash('sha256',$password);

        if (!is_null($email) && !is_null($password) &&  ($getToken == null ||$getToken == 'true')){

            $signup = $jwtAuth->signup($email,$pwd);

        }elseif($getToken != 'true'){
            //var_dump($getToken);die();

            $signup = $jwtAuth->signup($email,$pwd,$getToken);

        }else{

           /* $signup = array(
                'status' => 'error',
                'message' => 'Envia tus datos por post'
            );*/

            // en verda no si si es este error o tal *PENDIENTE
             return $this->errorResponse('Envia tus datos por post',405);
        }

        return response()->json($signup,200);    
    }

    public function makeadmin(Request $request){

        $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){

             $id = $request->id;
             $usuario = $JwtAuth->checkToken($hash,true);
              $user = User::find($id);

            $user->role = 'admin';
            $user->save();

             $now = time();
          $fechayhora = date("Y-m-d H:i:s",$now);
            DB::statement('call regaccion(?,?,?,?,?)',array($usuario->sub,$usuario->name.' '.$usuario->surname,$usuario->role,'Elevar privilegios a '.$user->name.' '.$user->surname,$fechayhora));
            return $this->showOne($user,'useradmin');

        }else{

            return $this->errorResponse('No autenticado',401);
        }   
    }

    public function setactive(Request $request){

        $hash = $request->header('Authorization',null);
        $JwtAuth = new JwtAuth();
        $checkToken = $JwtAuth->checkToken($hash);

        if ($checkToken){

             $id = $request->id;
             $activo = $request->active;

              $usuario = $JwtAuth->checkToken($hash,true);
             $user = User::find($id);

              $now = time();
              $fechayhora = date("Y-m-d H:i:s",$now);
             if ($activo == 0 ){
              // elimina ese user porque no lo acepta en el sistema
                $user->delete();

                 DB::table('trigger_pdf')->where('user_id', '=', $user->id)->delete();
                 DB::statement('call regaccion(?,?,?,?,?)',array($usuario->sub,$usuario->name.' '.$usuario->surname,$usuario->role,'Denegación de registro y borrado de '.$user->name.' '.$user->surname,$fechayhora));

             }elseif($activo == 1 ) {
                 $user->active = 1;
                $user->save();

                DB::statement('call regaccion(?,?,?,?,?)',array($usuario->sub,$usuario->name.' '.$usuario->surname,$usuario->role,'Aceptación en el sistema de '.$user->name.' '.$user->surname,$fechayhora));
             }

            $data = [
                  'status' =>'success',
                  'user'=>$user,
                  'active'=>$activo
                ];

            return response()->json($data,200);

        }else{

            return $this->errorResponse('No autenticado',401);
        }   
    }

}
