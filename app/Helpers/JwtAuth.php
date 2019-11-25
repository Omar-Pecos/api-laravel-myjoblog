<?php 
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

	public $key;

	public function __construct(){
		$this->key = 'esta_es_mi_clave_de_cifrado_*?*1322469863567567469863';
	}

	public function signup ($email,$password,$getToken = null){

		$user = User::where(
			array(
				'email' => $email,
				'password' =>$password
			))->first();

		$signup = false;

		// comprueba tmb que active sea 1 !!!
		if (is_object($user) && $user->active == 1){
			$signup = true;
		}

		if ($signup){
			//genera el token y lo devuelve
			$token = array(
				'sub' => $user->id,
				'dni' => $user->dni,
				'email'=> $user->email,
				'name' => $user->name,
				'surname'=>$user->surname,
				'role' =>$user->role,
				'iat' => time(),
				'exp' =>time() + (7*24*60*60)
			);

			$jwt = JWT::encode($token,$this->key,'HS256');

			$decoded = JWT::decode($jwt,$this->key,array('HS256'));

			if (is_null($getToken)){
				$now = time();
				$fechayhora = date("Y-m-d H:i:s",$now);
				DB::statement('call regaccion(?,?,?,?,?)',array($user->id,$user->name.' '.$user->surname,$user->role,'Inicio de Sesión',$fechayhora));
				return $jwt;
			}else{
				return $decoded;
			}
		}
		else{
			// devolver un error
			return array(
				'status' => 'error',
				'message' => 'Login ha fallado !!'
			);
		}
	}

	public function checkToken ($jwt, $getIdentity = false){
		$auth = false;

		try{
			$decoded = JWT::decode($jwt,$this->key,array('HS256'));
		}
			catch (\UnexpectedValueException $e){
				$auth = false;
			}
			catch (\DomainException $e){
				$auth = false;
			}

		if (isset($decoded) &&  is_object($decoded) && isset($decoded->sub)){
			$auth = true;
		}else{
			$auth = false;
		}

		if ($getIdentity){
			return $decoded;
		}

		return $auth;
	}
}