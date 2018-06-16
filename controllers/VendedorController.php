<?php

namespace app\controllers;

use app\models\SupervisorSupervisa;
use app\models\Vendedor;
use app\commands\Utils;
use app\models\SessionData;
use app\models\Prospecto;
use app\models\AccionComercial;
use app\models\AccionVendedor;

class VendedorController extends \yii\rest\ActiveController
{
	public $modelClass = 'app\models\Vendedor';

	protected function verbs()
	{
		return [
				 
		];
	}

	public function actions()
	{
		$actions = parent::actions();

		// disable the "delete" and "create" actions
		//unset($actions['delete'], $actions['create']);
		return $actions;
	}


	public function actionIndex()
	{
		return $this->render('index');
	}


	public function actionSearch(){
		return "vendedor";
	}

	public function actionAsdf($asdf)
	{
		$id=1;
		return $asdf . $id;
	}
	
	public function actionPingloc(){
		
		$request = \Yii::$app->request;
		$object = $request->post();
		
		$token = $object["token"];
		$lat = $object["lat"];
		$lon = $object["lon"];
		$accion = $object["accion"];
		
		$vendedor = Vendedor::getBySessionToken($token);
		
		if( $vendedor != null ){
			$accionVendedor = new AccionVendedor();
			$accionVendedor->id_vendedor = $vendedor->id;
			$accionVendedor->accion = $accion;
			$accionVendedor->timestamp = time();
			$accionVendedor->lat = $lat;
			$accionVendedor->lon = $lon;
			$accionVendedor->insert();
			return $accionVendedor;
		}
		return null;
		
	}

    public function actionPingloc2(){

        header('Access-Control-Request-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $request_body = file_get_contents('php://input');
            $object = json_decode($request_body, TRUE);

            $token = $object["token"];
            $lat = $object["lat"];
            $lon = $object["lon"];
            $accion = $object["accion"];

            $vendedor = Vendedor::getBySessionToken($token);

            if ($vendedor != null) {
                $accionVendedor = new AccionVendedor();
                $accionVendedor->id_vendedor = $vendedor->id;
                $accionVendedor->accion = $accion;
                $accionVendedor->timestamp = time();
                $accionVendedor->lat = $lat;
                $accionVendedor->lon = $lon;
                $accionVendedor->insert();
                return $accionVendedor;
            }
        }
        return null;

    }
	public function actionAddaccioncomercial(){
		$request = \Yii::$app->request;
		$object = $request->post();
		$token = $object["token"];
		$idProspecto = $object["idProspecto"];
		$accion = $object["accionComercial"];
		
		$prospecto = Prospecto::findOne(["id"=>$idProspecto]);
		if( $prospecto != null ){
			$accionComercial = new AccionComercial();
			$accionComercial->accion = $accion;
			$accionComercial->id_prospecto = $prospecto->id;
			$accionComercial->timestamp = time();
			$accionComercial->insert();
			return $accionComercial;
		}
		return null;
	}

    public function actionAddaccioncomercial2(){
        header('Access-Control-Request-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        if ($_SERVER['REQUEST_METHOD']=='POST'){
            $request_body = file_get_contents('php://input');
            $obj = json_decode($request_body, TRUE);
            $token = $obj["token"];
            $idProspecto = $obj["idProspecto"];
            $accion = $obj["accionComercial"];
            $prospecto = Prospecto::findOne(["id"=>$idProspecto]);
            if( $prospecto != null ){
                $accionComercial = new AccionComercial();
                $accionComercial->accion = $accion;
                $accionComercial->id_prospecto = $prospecto->id;
                $accionComercial->timestamp = time();
                $accionComercial->insert();
                return $accionComercial;
            }
        }
        return null;
    }
	
	public function actionCreateprospecto(){

		$request = \Yii::$app->request;
		
		$object = $request->post();
		$token = $object["token"];
		$vendedor = Vendedor::getBySessionToken($token);
		$newProspecto = $object["prospecto"];
		$accion = $object["accionComercial"];
		
		$newProspecto["tipo_creacion"] = Prospecto::CREACION_USER;
		$newProspecto["id_vendedor"] = $vendedor->id;
				
		$prospecto = new Prospecto();
		$prospecto->updateAttributes($newProspecto);
		$prospecto->update_time=time();
		$prospecto->create_time=time();
		$prospecto->save(0);
		
		$accionComercial = new AccionComercial();
		$accionComercial->accion = $accion;
		$accionComercial->id_prospecto = $prospecto->id;
		$accionComercial->timestamp = time();
		$accionComercial->insert(1);
		
		$prospecto->accion_comercial = $prospecto->getAccionesComerciales();
		
		return $prospecto;
	}

    public function actionCreateprospecto2(){
        header('Access-Control-Request-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $request_body = file_get_contents('php://input');
            $obj = json_decode($request_body, TRUE);

            $token = $obj["token"];
            $vendedor = Vendedor::getBySessionToken($token);
            return(gettype($vendedor));
            $newProspecto = $obj["prospecto"];
            $accion = $obj["accionComercial"];

            $newProspecto["tipo_creacion"] = Prospecto::CREACION_USER;
            $newProspecto["id_vendedor"] = $vendedor->id;

            $prospecto = new Prospecto();
            $prospecto->updateAttributes($newProspecto);
            $prospecto->update_time = time();
            $prospecto->create_time = time();
            $prospecto->save(0);

            $accionComercial = new AccionComercial();
            $accionComercial->accion = $accion;
            $accionComercial->id_prospecto = $prospecto->id;
            $accionComercial->timestamp = time();
            $accionComercial->insert(1);

            $prospecto->accion_comercial = $prospecto->getAccionesComerciales();
            return $prospecto;
        }
        return null;
    }

	public function actionUpdateprospecto(){
		$request = \Yii::$app->request;
		
		$object = $request->post();
		$token = $object["token"];
		$prospecto = $object["prospecto"];
		
		$idProspecto = $prospecto["id"];
		$prospectoBD = Prospecto::findOne(["id"=> $idProspecto]);
		$prospectoBD->updateAttributes($prospecto);
		$prospectoBD->update_time = time();
		$prospectoBD->save();
		
		if(isset($object["accionComercial"]) && $object["accionComercial"] != null ){
			$accion = $object["accionComercial"];
			$accionComercial = new AccionComercial();
			$accionComercial->accion = $accion;
			$accionComercial->id_prospecto = $prospectoBD->id;
			$accionComercial->timestamp = time();
			$accionComercial->insert(1);
		}
		$prospectoBD->accion_comercial = $prospectoBD->getAccionesComerciales();
		return $prospectoBD;
	}

    public function actionUpdateprospecto2(){
        header('Access-Control-Request-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $request_body = file_get_contents('php://input');
            $object = json_decode($request_body, TRUE);
            $token = $object["token"];
            $prospecto = $object["prospecto"];

            $idProspecto = $prospecto["id"];
            $prospectoBD = Prospecto::findOne(["id"=> $idProspecto]);
            $prospectoBD->updateAttributes($prospecto);
            $prospectoBD->update_time = time();
            $prospectoBD->save();

            if(isset($object["accionComercial"]) && $object["accionComercial"] != null ){
                $accion = $object["accionComercial"];
                $accionComercial = new AccionComercial();
                $accionComercial->accion = $accion;
                $accionComercial->id_prospecto = $prospectoBD->id;
                $accionComercial->timestamp = time();
                $accionComercial->insert(1);
            }
            $prospectoBD->accion_comercial = $prospectoBD->getAccionesComerciales();
            return $prospectoBD;
        }
        return null;
    }

	public function actionChangeaction(){
		$request = \Yii::$app->request;
		$post = $request->post();
		$idProspecto = $post["idProspecto"];
		$token = $post["token"];
		$tipoContacto = $post["tipo_contacto"];
		$tipoAccion = $post["tipo_accion"];
		$prospecto = Prospecto::findOne(["id"=>$idProspecto]);
		$prospecto->tipo_contacto = $tipoContacto;
		$prospecto->tipo_accion = $tipoAccion;
		$prospecto->update_time=time();
		$prospecto->save(0);
		return $prospecto;
	}

    public function actionChangeaction2(){
        header('Access-Control-Request-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $request_body = file_get_contents('php://input');
            $post = json_decode($request_body, TRUE);


            $idProspecto = $post["idProspecto"];
            $token = $post["token"];
            $tipoContacto = $post["tipo_contacto"];
            $tipoAccion = $post["tipo_accion"];
            $prospecto = Prospecto::findOne(["id" => $idProspecto]);
            $prospecto->tipo_contacto = $tipoContacto;
            $prospecto->tipo_accion = $tipoAccion;
            $prospecto->update_time = time();
            $prospecto->save(0);
            return $prospecto;
        }
        return null;
    }
	
	public function actionCreateuser(){
		$request = \Yii::$app->request;
		$post = $request->post();
		
		$vendedor = new Vendedor();
		$vendedor->username = strtoupper($post["username"]);
		$vendedor->email = $post["email"];
		$vendedor->hash =  password_hash($post["password"], PASSWORD_DEFAULT);
        $vendedor->nombre = $post["nombre"];
        $vendedor->tipo_usuario= $post["tipo_usuario"];

        if ($vendedor->save()) {
            if ($post["tipo_usuario"] == 3) {
                $model_ss = new SupervisorSupervisa();
                $model_ss->id_supervisor = $post['id_supervisor'];
                $model_ss->id_vendedor = Vendedor::getByUsername($post["username"])['id'];
                if ($model_ss->save()) {
                    return "success";
                } else {
                    return $model_ss->getErrors();
                }
            } else {
                return "success";
            }
        } else {
            return $vendedor->getErrors();
        }
	}

    public static function actionCreateuserwithparams($username, $email, $hash, $nombre, $tipo_usuario){
        $vendedor = new Vendedor();
        $vendedor->username = strtoupper($username);
        $vendedor->email = $email;
        $vendedor->hash =  password_hash($hash, PASSWORD_DEFAULT);
        $vendedor->nombre = $nombre;
        $vendedor->tipo_usuario= $tipo_usuario;
        if($vendedor->save()){
            return $vendedor;
        }else{
            return $vendedor->getErrors();
        }
    }
	
	/**
	 * 
	 * @param string $sessionToken
	 */
	public function actionGetventas($sessionToken){
		$user = Vendedor::getBySessionToken($sessionToken);
		if($user == null ){
			$this->setHeader(400);
			return null;
		}

        /*if(\Yii::$app->user->getIdentity()->tipo_usuario == 2){
		    return $user->getVentas();
        }else{*/
            return $user->getVentas();
        //}
	}
	
	public function actionGetprospectos($sessionToken){
		$user = Vendedor::getBySessionToken($sessionToken);
		if($user == null ){
			$this->setHeader(400);
			return false;
		}
		$prospectos = $user->getProspectos();
		return $prospectos;
		if(count($prospectos)>0){
            foreach ($prospectos as $prospecto ){
                $prospecto->accion_comercial = $prospecto->getAccionesComerciales();
            }
            return $prospectos;
		}
		return false;
	}
	
	public function actionLogin($username, $pass, $deviceId, $deviceModel){

		Utils::log("Buscando usuario con username:" . $username);
		$vendedor = Vendedor::findOne(["username" => $username]);		
		if($vendedor == null || count($vendedor) == 0){
			return new SessionData(SessionData::LOGIN_NO_USER, "User not found", "");
		}
		if (password_verify($pass, $vendedor->hash)) {

            if ($vendedor->device_id == null) {
                $vendedor->device_id = $deviceId;
                $vendedor->device_model = $deviceModel;
            } else {
                // TODO HACK para usuario test
                $supers = [3, 333, 334, 335, 336, 337, 339, 539, 538];
                if (!(in_array($vendedor->id, $supers))) {
                    if ($deviceId == null || $deviceId == "" || $vendedor->device_id != $deviceId) {
                        return new SessionData(SessionData::WRONG_DEVICE, "WRONG DEVICE", "");
                    }
                }
            }

            if($vendedor->estado !== 0){
                return new SessionData(SessionData::NO_AUTH, "ACCESO NO AUTORIZADO", "");
            }

            session_start();
            $vendedor->api_token = session_id();
            Utils::log("Session id:" . $vendedor->api_token);
            $vendedor->api_token_create_date = time();
            if(!$vendedor->save()){
                return new SessionData(99, $vendedor->getErrors(), '');
            }
            return new SessionData(SessionData::LOGIN_OK, "", $vendedor->api_token);

        } else {
			return new SessionData(SessionData::WRONG_PASS, "WRONG PASS", "");
		}
	}

	private function setHeader($status)
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
		$content_type="application/json; charset=utf-8";
		header($status_header);
		header('Content-type: ' . $content_type);
		header('X-Powered-By: ' . "Nintriva <nintriva.com>");
	}

	private function _getStatusCodeMessage($status)
	{
		$codes = Array(
				200 => 'OK',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	public function actionGetvendedor(){
        $request = \Yii::$app->request;
        $get = $request->get();
        if (isset($get['token'])) {
            $token = $get['token'];
            $vendedor = Vendedor::getBySessionToken($token);
            $vendedor = ['nombreVendedor' => $vendedor->nombre, 'codigoTango' => $vendedor->username, 'idVendedor' => $vendedor->id];
            return json_encode($vendedor);
        }elseif (isset($get['tango'])){
            $tango = $get['tango'];
            $vendedor = Vendedor::getByUsername($tango);
            $vendedor = ['token' => $vendedor->api_token];
            return json_encode($vendedor);
        }
    }

}
