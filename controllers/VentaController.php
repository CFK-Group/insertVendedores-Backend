<?php

namespace app\controllers;

use app\models\Venta;
use app\models\VentaPerdida;

class VentaController extends \yii\web\Controller
{
	public $modelClass = 'app\models\Venta';
	
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

	
    public function actionGetventas()
    {
        return $this->render('getventas');
    }

    public function actionIndex()
    {
        return $this->render('venta/index');
    }

    public function actionUpdateventas()
    {
        return $this->render('updateventas');
    }
	
	public function actionSearch(){
		return "vendedor";
	}
    
    public function actionAsdf($asdf)
    {
    	$id=1;
    	return $asdf . $id;
    }
    
    public function actionGetbyvendedor($id){
		    	
    	return Venta::findAll(['id_vendedor' => $id]);
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
}
