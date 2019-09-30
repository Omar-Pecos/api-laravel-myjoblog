<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;
use App\User;


class UserController extends Controller
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


     public function register(Request $request){
        //Recoger post
        $json = $request->input('json',null);

        $params = json_decode($json);

        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $number = (!is_null($json) && isset($params->number)) ? $params->number : null;
        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $dni = (!is_null($json) && isset($params->dni)) ? $params->dni : null;
        $role = 'user';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;

        // esto se puede pasar por un validator de data y las rules y si es obj validate is ok -> continua sino saca los errores del validate !
       

        if (!is_null($email) && !is_null($password) && !is_null($name) && !is_null($surname) && !is_null($dni)){

            //Crear el usuario 
            $user = new User();

            $user->name = $name;
            $user->surname = $surname;
           
            $user->number = $number;
             $user->email = $email;
             $user->dni = $dni;
            $user->role = $role;
            $user->active = 1;

            $pwd = hash('sha256',$password);
            $user->password = $pwd;

            // Comprobar usuarios duplicados
            $isset_user = User::where('email','=',$email)
                ->get();
            if (count($isset_user) == 0){
                //Guardarlo
                $user->save();

                $data = array(
              'status' => 'success',
              'code' => 200,
              'message' => 'Usuario registrado correctamente'
            );

            }else{
                $data = array(
              'status' => 'error',
              'code' => 400,
              'message' => 'Usuario duplicado, no puede registrarse'
            );
            }

        }else{
            $data = array(
              'status' => 'error',
              'code' => 400,
              'message' => 'Usuario no creado'
            );
        }

        return response()->json($data,200);

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

            $signup = array(
                'status' => 'error',
                'message' => 'Envia tus datos por post'
            );
        }

        return response()->json($signup,200);

        
    }
}
