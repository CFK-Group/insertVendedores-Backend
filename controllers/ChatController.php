<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;

class ChatController extends \yii\web\Controller
{
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['getmsgs'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
          //  var mensaje = {"mensaje": txt, "hora": hora, "emisor": "ejecutivo"};
    public function actionGetmsgs()
    {
        date_default_timezone_set("Chile/Continental");
        $object =Yii::$app->request->get();
        $id_vendedor = $object['idVendedor'];
        $id_ejecutivo = $object['idEjecutivo'];
        $rows = Yii::$app->getDb()
            ->createCommand("
                SELECT * FROM chat WHERE idVendedor = :idVendedor
            ")->bindValue(':idVendedor',  $id_vendedor)->queryAll();

        $mensajes = "[";
        $i = 1;
        $lenght = count($rows);
        foreach ($rows as $reg){
            $emisor = "";
            if ($reg["tipoMensaje"] == 1){
                $emisor = "vendedor";
            }else if ($reg["tipoMensaje"] == 2){
                $emisor = "ejecutivo";
            }

            if ($i == $lenght) {
                $mensajes = $mensajes . "{\"mensaje\": \"" . $reg['mensaje'] . "\", \"hora\": \"" . date('d-m-Y H:i', $reg['timestamp']) . "\", \"emisor\": \"" . $emisor . "\"} ";
            }else{
                $mensajes = $mensajes . "{\"mensaje\": \"" . $reg['mensaje'] . "\", \"hora\": \"" . date('d-m-Y H:i', $reg['timestamp']) . "\", \"emisor\": \"" . $emisor . "\"}, ";
            }
            $i++;
        }
        $mensajes = $mensajes . "]";
        return json_encode($mensajes);
    }
}
