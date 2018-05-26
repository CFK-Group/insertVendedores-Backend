<?php

namespace app\controllers;

class ProspectoController extends \yii\rest\ActiveController
{
    public function actionEditar()
    {
        return $this->render('editar');
    }

    public function actionGuardar()
    {
        return $this->render('guardar');
    }

}
