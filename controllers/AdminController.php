<?php

namespace app\controllers;

use app\commands\Utils;
use app\models\AccionComercial;
use app\models\AccionVendedor;
use app\models\Apertura;
use app\models\Prospecto;
use app\models\SupervisorSupervisa;
use app\models\Vendedor;
use app\models\Venta;
use app\models\UploadException;
use app\models\VentaPerdida;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
require ('../extensions/xlsxwriter.class.php');

class AdminController extends Controller
{
    public $modelClass = 'app\models\User';
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    # LO QUE YA SE REVISÓ QUEDA AQUI

    //LECTURA DE CSV SUBIDO SIN GUARDAR
    function readCSV($csvFile)
    {
        $file_handle = fopen($csvFile, 'r');
        $line_of_text = [];
        while (!feof($file_handle)) {
            $read = fgetcsv($file_handle, 1024, ';');
            $helper = [];
            if (is_array($read) || is_object($read)) {
                foreach ($read as $field) {
                    $helper[] = $field;
                    //var_dump($helper);
                }
            }
            //var_dump("------");
            $line_of_text[] = $helper;
        }
        fclose($file_handle);
        return $line_of_text;
    }

    //FUNCION DE CARGA DE LA BASE DE DESCONEXIONES
    function loadDx($lines){
        $model = new Venta();
        $error = [];
        $counterNews = 0;
        $counterUpdate = 0;
        foreach ($lines as $line){
            $venta = $model->findOne(['id_servicio' => $line[11]]);
            if (!is_null($venta)){
                $venta->cruce_con_base_vtr = 'De baja';
                if ($venta->update() !== false){
                    $counterUpdate = $counterUpdate + 1;
                }else{
                    $error = $model->getErrors();
                    break;
                }
            }
        }
        return ['counterNews' => $counterNews, 'error' => $error, 'counterUpdate' => $counterUpdate];
    }

    //FUNCION DE CARGA DE LA BASE DE VENTAS
    /*function loadVenta($lines)
    {

        $actualesMes = Venta::find()->where(['DATE_FORMAT(dia_registro,\'%M-%Y\')' => date('F-Y')])->all();
        //var_dump($actualesMes);
        $nuevas = $lines;
        $error = [];
        $counterNews = 0;
        $counterUpdate = 0;

        //crear ventas nuevas, inexistentes.
        for($j = 0; $j < count($nuevas); $j++){
            $model = new Venta();
            if ($j > 0) {

                $exist = $model::find()->where(['rut_cliente' => explode('-',$nuevas[$j][11])[0]])->andWhere(['servicio' => $nuevas[$j][12]])->andWhere(['id_vivienda' => $nuevas[$j][7]])->one();
                if (is_null($exist) || trim(strtoupper($nuevas[$j][28])) === 'B2B') {
                    //Significa que no hay registros con ese id de servicio
                    $venta = $this->asignarModeloVenta($model, $nuevas[$j]);

                    //generar los cruces para la venta actual
                    $venta = $this->cruceDotacion($venta);
                    //$venta = $this->cruceApertura($venta);
                    if (!is_null($venta->id_vendedor)){
                        $vendedor = Vendedor::getById($venta->id_vendedor);
                        if(is_null($vendedor->dismissal_date) || \DateTime::createFromFormat('Y-m-d', $vendedor->dismissal_date) <  \DateTime::createFromFormat('Y-m-d', strtotime("first day of 6 months ago"))){
                            if ($venta->save()) {
                                $counterNews = $counterNews + 1;
                            } else {
                                $error = $venta->getErrors();
                                break;
                            }
                        }
                    } else {
                        $error[] = "NO EXISTE EL VENDEDOR " . $nuevas[$j][3];
                        break;
                    }
                }
                else{
                    if(!(!is_null($exist->dia_final) && ($nuevas[$j][9]===''))) {
                        //aqui si hay registros, por tanto los actualizamos
                        $venta = $this->asignarModeloVenta($exist, $nuevas[$j]);

                        $venta = $this->cruceDotacion($venta);

                        //$venta = $this->cruceApertura($venta);

                        if (!is_null($venta->id_vendedor)) {
                            if ($venta->update() !== false) {
                                $counterUpdate = $counterUpdate + 1;
                            } else {
                                $error = $model->getErrors();
                                break;
                            }
                        } else {
                            $error[] = "NO EXISTE EL VENDEDOR " . $nuevas[$j][3];
                            break;
                        }
                    }
                }
            }
        }


        //detectar ventas perdidas

        for($i = 0; $i < count($actualesMes); $i++){
            $flag = false;
            //var_dump($actualesMes[$i]['id_servicio']);
            for($j = 0; $j < count($nuevas); $j++){
                //var_dump($nuevas[$j][10]);
                //necesito comparar: servicio, rut, id_vivienda
                //actualesmes[][3] RUT        NUEVAS[][11]
                //actualesmes[][7] VIVIENDA   NUEVAS[][7]
                //actualesmes[][5] SERVICIO   NUEVAS[][12]

                if ( ($actualesMes[$i]['servicio'] == $nuevas[$j][12]) && ($actualesMes[$i]['rut_cliente'] == explode('-',$nuevas[$j][11])[0]) && ($actualesMes[$i]['id_vivienda'] == $nuevas[$j][7]) ){
                    $flag = true;
                }
            }

            if (!$flag){
                $registro_perdido = VentaPerdida::find()->where(['rut_cliente' => $actualesMes[$i]['rut_cliente']])->andWhere(['servicio' => $actualesMes[$i]['servicio']])->andWhere(['id_vivienda' => $actualesMes[$i]['id_vivienda']])->one();
                if (is_null($registro_perdido)) {
                    $ventaPerdida = new VentaPerdida();
                    foreach ($actualesMes[$i] as $key => $value) {
                        if ($key !== 'id') {
                            $ventaPerdida->$key = $value;
                        }
                    }
                    $ventaPerdida->id = null;
                    if ($ventaPerdida->save() !== false) {
                        $reg = Venta::find()->where(['rut_cliente' => $ventaPerdida->rut_cliente])->andWhere(['servicio' => $ventaPerdida->servicio])->andWhere(['id_vivienda' => $ventaPerdida->id_vivienda])->one();
                        $reg->cruce_con_base_vtr = 'Rechazado';
                        $reg->update();
                    } else {
                        $error = $ventaPerdida->getErrors();
                        break;
                    }
                }else{
                    $ventaPerdida = $registro_perdido;
                    foreach ($actualesMes[$i] as $key => $value) {
                        if ($key !== 'id') {
                            $ventaPerdida->$key = $value;
                        }
                    }

                    if ($ventaPerdida->update() !== false) {
                        $reg = Venta::find()->where(['rut_cliente' => $ventaPerdida->rut_cliente])->andWhere(['servicio' => $ventaPerdida->servicio])->andWhere(['id_vivienda' => $ventaPerdida->id_vivienda])->one();
                        $reg->cruce_con_base_vtr = 'Rechazado';
                        $reg->update();
                    } else {
                        $error = $ventaPerdida->getErrors();
                        break;
                    }
                }
            }
        }

        $allVentas = Venta::find()->all();
        foreach ($allVentas as $oneVenta){
            if (!in_array($oneVenta, $actualesMes)){
                $oneVenta->tipo_carga = 4;
                $oneVenta->update();
            }
        }

        return ['counterNews' => $counterNews, 'error' => $error, 'counterUpdate' => $counterUpdate];
    }*/

    //FUNCION DE CARGA DE LA BASE DE VENTAS V2
    function loadVenta2($lines, $tipoBase){
        ini_set('max_execution_time', 300);
        $startPeriod = date('Y-m-d H:i:s', strtotime('midnight first day of 3 months ago'));

        $poolA = Venta::find()->where(['>=','dia_registro', $startPeriod])->all();
       //var_dump("Estas existen en la bd");
       //var_dump(count($poolA));
        //var_dump($poolA);
        $poolAids = []; //todos los id_servicio de las ventas ya existentes
        $poolB = $lines;
        $poolBids = []; //todos los id_servicio de las ventas nuevas
        $error = [];
        $counterNews = 0;
        $counterUpdate = 0;
        $counterPerdidas = 0;
        $iter = 0;

        if ($tipoBase == 'portal'){
            $index_idServicio = 10;
        }elseif ($tipoBase == 'vtr'){
            $index_idServicio = 2;
        }

        foreach ($poolA as $ventaAntigua){
            $poolAids[] = $ventaAntigua->id_servicio;
        }
       //var_dump($poolAids);

        foreach ($poolB as $ventaNueva) {
            if ($iter > 0) {
                if($tipoBase =='vtr' && $ventaNueva[16] !== 'A') {
                    if (in_array(intval($ventaNueva[$index_idServicio]), $poolAids)) {
                        //Si el idServicio de la venta nueva existe en el arreglo de ventas antiguas, acualizaremos los datos
                        $venta = Venta::findOne(['id_servicio' => intval($ventaNueva[$index_idServicio])]);
                        $venta = $this->asignarModeloVenta($venta, $ventaNueva, $tipoBase);
                        //guardamos los cambios
                        if ($venta->update() !== false) {
                            //var_dump("Guardada 1");
                            $counterUpdate = $counterUpdate + 1;
                            $poolAids[] = intval($venta->id_servicio);
                            $poolBids[] = intval($venta->id_servicio);
                        } else {
                            $error = $venta->getErrors();
                            //var_dump($error);
                            break;
                        }
                    } else {
                        //Si el idServicio de la venta nueva NO existe en el arreglo de ventas antiguas, lo agregamos
                        $venta = new Venta();
                        $venta = $this->asignarModeloVenta($venta, $ventaNueva, $tipoBase);
                        if (is_null($venta->id_vendedor)) {
                            if ($tipoBase == 'portal') {
                                $error[][] = "Usuario " . $ventaNueva[3] . " no existe";
                            } elseif ($tipoBase == 'vtr') {
                                $error[][] = "Usuario " . $ventaNueva[7] . " no existe";
                            }
                            break;
                        }
                        if ($venta->save()) {
                            //var_dump("Guardada 2");
                            $counterNews = $counterNews + 1;
                            $poolAids[] = intval($venta->id_servicio);
                            $poolBids[] = intval($venta->id_servicio);
                        } else {
                            $error = $venta->getErrors();
                            //var_dump($error);
                            break;
                        }
                    }
                }else{

                }
            }
            $iter = $iter + 1;
        }

        //de las ventas en la bd, hay alguna que no venga en el excel?
        //¿Cual es el periodo de tiempo que viene en las bases de venta descargadas del portal?
        //Si es solo un mes, perfect!
        //var_dump("Estas existen en el excel");
        //var_dump(count($poolBids));
        //var_dump($poolBids);

        $ventasPerdidasId = array_diff($poolAids, $poolBids);
        //Cambiar comisable a NO para cada una de esas ventas
        foreach ($ventasPerdidasId as $idPerdido){
            $ventaPerdida = Venta::find()->where(['id_servicio' => $idPerdido])->one();
           //var_dump($ventaPerdida);
            $ventaPerdida->comisionable = 'No';
            if($ventaPerdida->update() !== false){
               //var_dump("se perdio". $ventaPerdida->id_servicio);
                $counterPerdidas = $counterPerdidas + 1;
               //var_dump($counterPerdidas);
            }
        }

       //var_dump($counterPerdidas);
        return ['counterNews' => $counterNews, 'error' => $error, 'counterUpdate' => $counterUpdate, 'counterPerdidas' => $counterPerdidas];
    }


    //FUNCION DE CARGA DE LA BASE DE COBRANZA
    //OK
    function loadCobranza($lines)
    {
        $error = [];
        $counterNews = 0;
        $counterUpdate = 0;
        $exist = false;
        //antes de agregar un registro a la bd debo ver que registros desaparecen
        $actual = Venta::findAll(['tipo_carga' => 3]);
        $nuevas = $lines;
        $regsOmitibles = [];
        $noExisteBase = []; //registros antiguos que desaparecieron del listado nuevo
        $index = null;


        for ($i = 0; $i < count($actual); $i++) {
            for ($j = 1; $j < count($nuevas); $j++) {
                if (($actual[$i]['id_servicio'] == $nuevas[$j][10])){
                    $exist = true;
                    $index = $j;
                    $regsOmitibles[] = $actual[$i]['id_servicio'];
                    break;
                } else {
                    $exist = false;
                }
            }
            //una vez que termino de recorrer la lista nueva, puedo estar seguro de si existe o no el registro.
            if ($exist){
                //actualizo el registro con los datos nuevos si corresponde
                $updateVenta = Venta::findOne(['id_servicio' => $actual[$i]['id_servicio'], 'tipo_carga' => 3]);
                $data = $nuevas[$index];

                $rut = explode('-', $data[11]);
                $updateVenta->rut_cliente = $rut[0];
                $updateVenta->dv_cliente = $rut[1];
                $updateVenta->mes_venta = date('n', strtotime(str_replace("/", "-", $data[0])));
                $updateVenta->id_vendedor = Vendedor::getByUsername($data[3])['id'];
                $updateVenta->zona = $data[4];
                $updateVenta->territorio = $data[5];
                $updateVenta->canal = $data[6];
                $updateVenta->id_vivienda = $data[7];
                $updateVenta->dia_registro = date('Y-m-d', strtotime(str_replace("/", "-", $data[8])));
                $updateVenta->dia_final = date('Y-m-d', strtotime(str_replace("/", "-", $data[9])));
                $updateVenta->id_servicio = $data[10];
                $updateVenta->servicio = $data[12];
                $updateVenta->comuna = $data[13];
                $updateVenta->estado_venta = $data[14];
                $updateVenta->PCS = $data[15];
                $updateVenta->codigo_area_funcional = $data[16];
                $updateVenta->area_funcional = utf8_encode($data[17]);
                $updateVenta->venta_en_cobranza = $data[18];
                $updateVenta->ciclo_en_facturacion = $data[19];
                $updateVenta->flujo_cobranza = $data[20];
                $updateVenta->dias_mora = $data[21];
                $updateVenta->fecha_vencimiento = date('Y-m-d', strtotime($data[22]));
                $updateVenta->fecha_analisis = date('Y-m-d', strtotime($data[23]));
                $updateVenta->zona2 = $data[24];
                $updateVenta->territorio2 = $data[25];
                $updateVenta->tango_super = $data[26];
                $updateVenta->nombre = $data[29];
                $updateVenta->fono1 = $data[30];
                $updateVenta->fono2 = $data[31];
                $updateVenta->email = $data[32];
                $updateVenta->monto_deuda = $data[33];

                if($updateVenta->update() !== false){
                    $error = $updateVenta->getErrors();
                }else{
                    $counterUpdate = $counterUpdate + 1;
                }
            }else{
                $noExisteBase[] = $actual[$i];
            }
        }


        //vamos uno por uno
        for ($i = 0; $i < count($nuevas); $i++) {

            //comprobamos si es el primer elemento del array y si no lo es procedemos
            if ($i != 0) {
                if (!in_array($nuevas[$i][10], $regsOmitibles)) {
                    $model = new Venta();
                    $data = $nuevas[$i];
                    $rut = explode('-', $data[11]);
                    $model->rut_cliente = $rut[0];
                    $model->dv_cliente = $rut[1];
                    $model->mes_venta = date('n', strtotime(str_replace("/", "-", $data[0])));
                    $model->id_vendedor = Vendedor::getByUsername($data[3])['id'];
                    $model->zona = $data[4];
                    $model->territorio = $data[5];
                    $model->canal = $data[6];
                    $model->id_vivienda = $data[7];
                    $model->dia_registro = date('Y-m-d', strtotime(str_replace("/", "-", $data[8])));
                    $model->dia_final = date('Y-m-d', strtotime(str_replace("/", "-", $data[9])));
                    $model->id_servicio = $data[10];
                    $model->servicio = $data[12];
                    $model->comuna = $data[13];
                    $model->estado_venta = $data[14];
                    $model->PCS = $data[15];
                    $model->codigo_area_funcional = $data[16];
                    $model->area_funcional = utf8_encode($data[17]);
                    $model->venta_en_cobranza = $data[18];
                    $model->ciclo_en_facturacion = $data[19];
                    $model->flujo_cobranza = $data[20];
                    $model->dias_mora = $data[21];
                    $model->fecha_vencimiento = date('Y-m-d', strtotime($data[22]));
                    $model->fecha_analisis = date('Y-m-d', strtotime($data[23]));
                    $model->zona2 = $data[24];
                    $model->territorio2 = $data[25];
                    $model->tango_super = $data[26];
                    $model->nombre = $data[29];
                    $model->fono1 = $data[30];
                    $model->fono2 = $data[31];
                    $model->email = $data[32];
                    $model->monto_deuda = $data[33];
                    $model->tipo_carga = 3;

                    if ($model->save()) {
                        $counterNews = $counterNews + 1;
                    } else {
                        $error = $model->getErrors();
                        break;
                    }
                }
            } else {
                if (count($nuevas[0]) != 34) {
                    $error[0][0] = "Archivo inválido, compruebe que la cantidad de columnas sea la que corresponde";
                    break;
                }
            }
        }
        return ['counterNews' => $counterNews, 'error' => $error, 'counterUpdate' => $counterUpdate];
    }

    //FUNCION DE CARGA DE LA BASE DE APERTURA
    //NOT USED ACORRDING LAST MEETING
    function loadApertura($lines)
    {
        $error = [];
        $counterNews = 0;
        $counterUpdate = 0;
        $exist = false;
        //antes de agregar un registro a la bd debo ver que registros desaparecen
        $actual = Apertura::find()->all();
        $nuevas = $lines;
        $regsOmitibles = [];
        $noExisteBase = []; //registros antiguos que desaparecieron del listado nuevo
        $index = null;

        for ($i = 0; $i < count($actual); $i++) {
            for ($j = 1; $j < count($nuevas); $j++) {
                if ($actual[$i]['id_tango'] == $nuevas[$j][0]) {
                    $regsOmitibles[] = $actual[$i]['id_tango'];
                    //actualizo el registro con los datos nuevos si corresponde
                    $updateApertura = Apertura::findOne(['id_tango' => $actual[$i]['id_tango']]);
                    $data = $nuevas[$j];
                    //ASOCIACION DE DATOS DEL EXCEL AL MODELO
                    $updateApertura->id_tango = $data[0];
                    $updateApertura->id_sgi = $data[1];
                    $updateApertura->id_gis = $data[2];
                    $updateApertura->nombre_proyecto = utf8_encode(trim($data[3]));
                    $updateApertura->direccion = utf8_encode(trim($data[4]));
                    $updateApertura->nodo = $data[5];
                    $updateApertura->subnodo = $data[6];
                    $updateApertura->comuna = utf8_encode(trim($data[7]));
                    $updateApertura->proyecto = utf8_encode(trim($data[8]));
                    $updateApertura->fecha_inicio_apertura = ($data[9] != '') ? strtotime($data[9]) : null;
                    $updateApertura->fecha_inicio_basal = ($data[10] != '') ? strtotime($data[10]) : null;
                    $updateApertura->dealer = utf8_encode(trim($data[11]));
                    $updateApertura->fecha_liber_operador = ($data[12] != '') ? strtotime($data[12]) : null;
                    if ($updateApertura->update() !== false) {
                        $error = $updateApertura->getErrors();
                        break;
                    } else {
                        $counterUpdate = $counterUpdate + 1;
                    }
                }
            }
        }

        //vamos uno por uno
        for ($i = 0; $i < count($nuevas); $i++) {
            //comprobamos si es el primer elemento del array y si no lo es procedemos
            if ($i != 0) {
                if (!in_array($nuevas[$i][0], $regsOmitibles)) {
                    //ASOCIACION DE DATOS DEL EXCEL AL MODELO
                    $model = new Apertura();
                    $model->id_tango = $nuevas[$i][0];
                    $model->id_sgi = $nuevas[$i][1];
                    $model->id_gis = $nuevas[$i][2];
                    $model->nombre_proyecto = utf8_encode(trim($nuevas[$i][3]));
                    $model->direccion = utf8_encode(trim($nuevas[$i][4]));
                    $model->nodo = $nuevas[$i][5];
                    $model->subnodo = $nuevas[$i][6];
                    $model->comuna = utf8_encode(trim($nuevas[$i][7]));
                    $model->proyecto = utf8_encode(trim($nuevas[$i][8]));
                    $model->fecha_inicio_apertura = ($nuevas[$i][9] != '') ? strtotime($nuevas[$i][9]) : null;
                    $model->fecha_inicio_basal = ($nuevas[$i][10] != '') ? strtotime($nuevas[$i][10]) : null;
                    $model->dealer = utf8_encode(trim($nuevas[$i][11]));
                    $model->fecha_liber_operador = ($nuevas[$i][12] != '') ? strtotime($nuevas[$i][12]) : null;

                    if ($model->save()) {
                        $counterNews = $counterNews + 1;
                    } else {
                        $error = $model->getErrors();
                        break;
                    }
                }
            } else {
                if (count($nuevas[0]) != 13) {
                    $error[0][0] = "Archivo inválido, compruebe que la cantidad de columnas sea la que corresponde";
                    break;
                }
            }
        }
        return ['counterNews' => $counterNews, 'error' => $error, 'counterUpdate' => $counterUpdate];
    }

    //FUNCION DE CARGA DE LA BASE DE DOTACION
    function loadDotacion($lines){
        $error = [];
        $counterNews = 0;
        $counterUpdate = 0;
        $actual = Vendedor::find()->all();
        $nuevas = $lines;
        $supervisoresSubida = [];
        $supervisoresFaltantes = [];
        $index = null;
        $model = new Vendedor();

        if (count($nuevas[0]) != 29) {
            $error[0][0] = "Archivo inválido, compruebe que la cantidad de columnas sea la que corresponde";
        }else {
            for ($k = 1; $k < count($nuevas); $k++) {
                if ($model->isSuper($nuevas[$k][12]) == 2 && !in_array($nuevas[$k][0], $supervisoresSubida)) {
                    $supervisoresSubida[] = $nuevas[$k][0];
                }
            }

            foreach ($supervisoresSubida as $supervisor) {
                if (is_null(Vendedor::getByUsername($supervisor))) {
                    $supervisoresFaltantes[] = $supervisor;
                }
            }

            for ($k = 1; $k < count($nuevas); $k++) {
                if (in_array($nuevas[$k][0], $supervisoresFaltantes)) {
                    //crear supervisor faltante
                    $modelSupervisor = new Vendedor();
                    $supervisor = $this->asignarModeloDotacion($modelSupervisor, $nuevas[$k]);
                    //si el codigo tango no viene, omitimos
                    if ($supervisor->username !== '-' || $supervisor->username !== '' || $supervisor->username !== ' ' || !is_null($supervisor->username)) {
                        $index[] = $nuevas[$k][0];
                        if (!$supervisor->save()) {
                            $error = $supervisor->getErrors();
                            break;
                        } else {
                            $counterNews = $counterNews + 1;

                        }
                    }
                }
            }


            //ACTUALIZAMOS VENDEDORES YA EXISTENTES
            if (empty($error)) {
                for ($i = 0; $i < count($actual); $i++) {
                    for ($j = 1; $j < count($nuevas); $j++) {
                        $data = $nuevas[$j];
                        if ($actual[$i]['username'] == $nuevas[$j][0]) {
                            $modelUpdate = $model->findByUsername($actual[$i]['username']);
                            $modelUpdate = $this->asignarModeloDotacion($modelUpdate, $data);
                            $index[] = $actual[$i]['username'];
                            if ($modelUpdate->update() !== false) {
                                $counterUpdate = $counterUpdate + 1;
                                $super = Vendedor::getByUsername($data[24]);
                                $uploadSuper = $this->setSupervisor($modelUpdate->id, $super['id']);
                                if(!$uploadSuper){
                                    $error[0][0] = "Problemas para vincular el supervisor al usuario " . $modelUpdate->username;
                                    break(2);
                                }
                            } else {
                                var_dump("Usuario Antiguo");
                                $error = $modelUpdate->getErrors();
                                break(2);
                            }
                        }
                    }
                }
            }

            //CREAMOS VENDEDORES NUEVOS
            if (empty($error)) {
                for ($k = 1; $k < count($nuevas); $k++) {
                    if (!in_array($nuevas[$k][0], $index)) {
                        $model2 = new Vendedor();
                        $modelSave = $this->asignarModeloDotacion($model2, $nuevas[$k]);
                        //si el codigo tango no viene, omitimos
                        if ($supervisor->username !== '-' || $supervisor->username !== '' || $supervisor->username !== ' ' || !is_null($supervisor->username)) {
                            if (!$modelSave->save()) {
                                var_dump("Usuario nuevo");
                                $error = $modelSave->getErrors();
                                break;
                            } else {
                                $counterNews = $counterNews + 1;
                                $vendedor = Vendedor::getByUsername($nuevas[$k][24]);
                                if (!is_null($vendedor)) {
                                    $uploadSuper = $this->setSupervisor($modelSave->id, $vendedor->id);
                                    if (!$uploadSuper) {
                                        $error[0][0] = "Problemas para vincular el supervisor al usuario " . $modelSave->username;
                                        break;
                                    }
                                } else {
                                    $error[0][0] = "Problemas para vincular el supervisor al usuario " . $modelSave->username;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return ['counterNews' => $counterNews, 'error' => $error, 'counterUpdate' => $counterUpdate];
    }

    function asignarModeloDotacion($model, $data){
        $model->username = utf8_encode($data[0]);
        $model->zona = utf8_encode($data[1]);
        $model->codi_pertenece = utf8_encode($data[2]);
        $model->canal = utf8_encode($data[3]);  //PERTENCE
        $model->codi_equipo = utf8_encode($data[4]);
        $model->nuevo_equipo = utf8_encode($data[5]);
        $model->jefaturas = utf8_encode($data[6]);
        $model->coordinadores = utf8_encode($data[7]);
        $model->rut = utf8_encode($data[8]);
        $model->nombre = utf8_encode($data[9]);
        $model->email = utf8_encode($data[11]);
        $model->desc_cargo = utf8_encode($data[12]);
        $model->desc_empresa = utf8_encode($data[15]);
        //$model->smartflex = ($data[17] == '') ? null : strtotime($data[17]);

        switch ($data[18]){
            case "ACTIVO":
                $model->estado = "0";
                break;
            case "LICENCIA":
                $model->estado = "2";
                break;
            case "DESVINCULADO":
                $model->estado = "1";
                break;
            default:
                $model->estado = "0";
                break;
        }
        $model->fecha_ini_lic = ($data[19] == '') ? null : strtotime($data[19]);
        $model->fecha_ter_lic = ($data[20] == '') ? null : strtotime($data[20]);
        $model->nps = utf8_encode($data[21]);
        $model->ilumina = utf8_encode($data[22]);
        $model->observacion = utf8_encode($data[23]);

        $model->ciudad = utf8_encode($data[25]);
        $model->territorios = utf8_encode($data[26]);

        $fecha = \DateTime::createFromFormat('!d-m-y', $data[27]);
        $data[27] === '' ? $model->hire_date = null : $model->hire_date = $fecha->getTimestamp();

        $fecha = \DateTime::createFromFormat('!d-m-y', $data[28]);
        $data[28] === '' ? $model->dismissal_date = null : $model->dismissal_date = $fecha->getTimestamp();

        return $model;
    }

    function asignarModeloVentaOld($model, $data){
        $rut = explode('-', $data[11]);
        $model->rut_cliente = $rut[0];
        $model->dv_cliente = $rut[1];
        $model->servicio = $data[12];
        $model->id_servicio = $data[10];
        $model->id_vivienda = $data[7];
        $model->id_vendedor = Vendedor::getByUsername($data[3])['id'];

        $fecha = \DateTime::createFromFormat('!d-m-y', $data[8]);
        $data[8] === '' ? $model->dia_registro = null : $model->dia_registro = $fecha->format('Y-m-d H:i:s');

        $fecha = \DateTime::createFromFormat('!d-m-y', $data[9]);
        $data[9] === '' ? $model->dia_final = null : $model->dia_final = $fecha->format('Y-m-d H:i:s');

        $fecha = \DateTime::createFromFormat('!d-m-y', $data[0]);
        $data[0] === '' ? $model->mes_venta = null : $model->mes_venta = $fecha->format('n');

        $model->comuna = $data[13];
        $model->comisionable = "Si";
        $model->facturada = $data[26];
        $model->valor = $data[27];
        $model->producto = $data[28];
        $model->b2b = $data[29];
        $model->tipo_venta = $data[30];

        if ($data[9] == ""){
            $model->estado_tango = "INS";
        }else{
            $model->estado_tango = "TERMINADA";
        }
        $model->cruce_con_base_vtr = 'Ok base vtr';

        //var_dump($model);
        return $model;
    }

    function asignarModeloVenta($model, $data, $tipoBase){
        if ($tipoBase === 'vtr'){
            $fecha = substr($data[0], -2);
            $fecha = \DateTime::createFromFormat('m', $fecha);
            $data[0] === '' ? $model->mes_venta = null : $model->mes_venta  = $fecha->format('m');

            $rut = explode('-', $data[1]);
            $model->rut_cliente = $rut[0];
            $model->dv_cliente = $rut[1];

            $model->id_servicio = $data[2];
            $model->id_vivienda = $data[3];
            $model->id_vendedor = Vendedor::getByUsername($data[7])['id'];
            $model->canal = $data[8];
            $model->servicio = $data[9];

            if ($data[16] != 'D') {
                $fecha = substr($data[11], -2) . '-' . substr($data[11], 4, 2) . '-' . substr($data[11], 0, 4);
                $fecha = \DateTime::createFromFormat('d-m-Y', $fecha);
                $data[11] === '' ? $model->dia_final = null : $model->dia_final = $fecha->format('Y-m-d');
            }else{
                $model->dia_final = null;
            }

            $fecha = substr($data[12], -2).'-'.substr($data[11], 4, 2).'-'.substr($data[12], 0, 4);
            $fecha = \DateTime::createFromFormat('d-m-Y', $fecha);
            $data[12] === '' ? $model->dia_registro = null : $model->dia_registro = $fecha->format('Y-m-d');

            $model->id_transaccion = $data[18];
            $model->producto = $data[19];
            $model->valor = $data[20];
            $model->tipo_venta = $data[21];
            $model->zona = $data[22];
            $model->canal = $data[23];
            $model->equipo = $data[24];
            $model->territorio = $data[25];
            $model->tango_super = $data[27];
            $model->b2b = $data[32];

            $model->comisionable = "Si";

            if ($data[9] == ""){
                $model->estado_tango = "INS";
            }else{
                $model->estado_tango = "TERMINADA";
            }

            //REVISAR QUE VIENEN DE LA BASE DEL PORTAL.
        }elseif($tipoBase == 'portal'){

            $fecha = \DateTime::createFromFormat('!d-m-y', $data[0]);
            $data[0] === '' ? $model->mes_venta = null : $model->mes_venta = $fecha->format('m');
            $model->id_vendedor = Vendedor::getByUsername($data[3])['id'];
            $model->zona = $data[4];
            $model->territorio = $data[5];
            $model->canal = $data[6];
            $model->id_vivienda = $data[7];

            $fecha = \DateTime::createFromFormat('!d-m-y', $data[8]);
            $data[8] === '' ? $model->dia_registro = null : $model->dia_registro = $fecha->format('Y-m-d');

            $fecha = \DateTime::createFromFormat('!d-m-y', $data[9]);
            $data[9] === '' ? $model->dia_final = null : $model->dia_final = $fecha->format('Y-m-d');

            $model->id_servicio = $data[10];

            $rut = explode('-', $data[11]);
            $model->rut_cliente = $rut[0];
            $model->dv_cliente = $rut[1];

            $model->servicio = $data[12];
            $model->comuna = $data[13];
            $model->estado_venta = $data[14];
            $model->PCS = $data[15];
            $model->codigo_area_funcional = $data[16];
            $model->area_funcional = $data[17];
            $model->venta_en_cobranza = $data[18];
            $model->ciclo_en_facturacion = $data[19];
            $model->flujo_cobranza = $data[20];
            $model->dias_mora = $data[21];

            $fecha = \DateTime::createFromFormat('!d-m-y', $data[22]);
            $data[22] === '' ? $model->fecha_vencimiento = null : $model->fecha_vencimiento = $fecha->format('Y-m-d');

            $fecha = \DateTime::createFromFormat('!d-m-y', $data[23]);
            $data[23] === '' ? $model->fecha_analisis = null : $model->fecha_analisis = $fecha->format('Y-m-d');

            $model->zona2 = $data[24];
            $model->territorio2 = $data[25];
            $model->comisionable = "Si";

            if ($data[9] == ""){
                $model->estado_tango = "INS";
            }else{
                $model->estado_tango = "TERMINADA";
            }
        }

        return $model;
    }

    function cruceDotacion($model){
        $vendedor = Vendedor::findOne(['id' => $model->id_vendedor]);
        if (!is_null($vendedor)) {
            $model->canal = $vendedor->canal;
            $model->equipo = $vendedor->nuevo_equipo;
        }
        return $model;
    }

    /*function cruceApertura($model){
        $apertura = Apertura::findOne(['id_tango' => $model->id_vivienda]);
        if (!is_null($apertura)){
            if (preg_match('/Apertura/i', $apertura->proyecto) == 1){
                $model->tipo_venta = "Apertura";
            }else{
                $model->tipo_venta = "Basal";
            }
        }else{
            $model->tipo_venta = "Basal";
        }
        return $model;
    }*/

    function setSupervisor($id_vendedor, $id_supervisor){

        $model = SupervisorSupervisa::find()->where(['id_vendedor' => $id_vendedor])->one();
        if (is_null($model)){
            $model = new SupervisorSupervisa();
            $model->id_supervisor = $id_supervisor;
            $model->id_vendedor= $id_vendedor;
            if ($model->save()){
                return true;
            }else{
                return false;
            }
        }else{
            $model->id_supervisor = $id_supervisor;
            $model->id_vendedor= $id_vendedor;
            if ($model->update() !== false){
                return true;
            }else{
                return false;
            }
        }
    }

    ######################################
    ########## CARGA DE VISTAS ###########
    ######################################

    //RENDERIZA LA VISTA PRINCIPAL DE ADMINS
    //OK
    public function actionIndex()
    {
        if (!\Yii::$app->user->isGuest) {
            Utils::log(" TENGO SESION");
            if (\Yii::$app->user->getIdentity()->tipo_usuario == 4) {
                return $this->redirect('chats');
            } else if (\Yii::$app->user->getIdentity()->tipo_usuario == 1) {
                $data = AccionVendedor::getAccionesPorDia();
                return $this->render('index', ['data' => $data]);
            }
        } else {
            Utils::log("GUEST");
            return $this->redirect('sites/login');
        }

        return $this->render('index');
    }

    //RENDERIZA LA VISTA DE LAS DIRECCIONES
    //OK
    public function actionGetprospectos()
    {
        $model = new Prospecto();
        if (Yii::$app->user->identity->tipo_usuario != 1) {
            $direcciones = $model->getProspectobysuper(Yii::$app->user->id);
        } else {
            $direcciones = $model->getProspectoEdo();
        }
        return $this->render('direcciones', ['direcciones' => $direcciones]);
    }

    //RENDERIZA LA VISTA DE LOS USERS
    //OK
    public function actionGetusers()
    {
        $model = new Vendedor();
        $vendedores = "";
        $admin = false;

        if (Yii::$app->user->identity->tipo_usuario == 2) {
            $vendedores = $model->getUsersbysuper(Yii::$app->user->id);
            //var_dump($vendedores);
        }elseif (Yii::$app->user->identity->tipo_usuario == 1) {
            $vendedores = $model->find()->all();
            $admin = true;
        }
        return $this->render('users', ['vendedores' => $vendedores, 'admin' => $admin]);
    }

    //RENDERIZAR LA VISTA DE CREACION DE USUARIO MEDIANTE FORMULARIO
    //OK
    public function actionCreateuser()
    {
        //titulo de la pagina
        \Yii::$app->view->title = "Crear Usuario";

        $model = new Vendedor();
        $supers = $model->getAllSupers();
        return $this->render('createuser', ['model' => $model, 'supers' => $supers]);
    }

    #######################################
    ### ACTIONS PARA EJECUTAR FUNCIONES ###
    #######################################

    //SUBIR LAS DIRECCIONES DESDE UN ARCHIVO
    //RENDERIZA LA VISTA
    //OK
    public function actionUploadcsvprospectos()
    {
        $error = [];
        $fileChecker = false;
        $counter = 0;

        //verificamos si la variable $_FILES existe
        if ((!empty($_FILES)) && isset($_FILES['cvs'])) {
            $fileChecker = 1;
            //si el archivo es csv continuamos, sino damos el error
            if (explode('.', $_FILES['cvs']['name'])[1] == 'csv') {

                //usamos la funcion para leer csv que nos entregará un arreglo por cada linea, con los
                //campos en utf-8
                $lines = $this->readCSV($_FILES['cvs']['tmp_name']);
                $lines = array_filter($lines);
                //si tenemos lineas procedemos a generar el modelo y a guardarlo
                if (count($lines) > 0) {
                    //vamos uno por uno, omitiendo la linea del encabezado que es la primera
                    foreach ($lines as $line) {
                        //comprobamos si es el primer elemento del array y si no lo es procedemos
                        if ($line !== reset($lines)) {
                            //var_dump($line);
                            $model = new Prospecto();
                            $model->create_time = time();
                            $rut = explode("-", $line[10]);
                            $model->id_vendedor = Vendedor::getByUsername($line[0])['id'];
                            $model->comuna = utf8_encode($line[1]);
                            $model->calle = utf8_encode($line[2]);
                            $model->numero = $line[3];
                            $model->nodo = $line[4];
                            $model->cuadrante = $line[5];
                            $model->fono = strtoupper($line[6]);
                            $model->cable = strtoupper($line[7]);
                            $model->inet = strtoupper($line[8]);
                            $model->premium = strtoupper($line[9]);
                            if (empty($rut[0])) {
                                $model->rut_prospecto = null;
                            } else {
                                $model->rut_prospecto = $rut[0];
                            }

                            if (empty($rut[1])) {
                                $model->rut_prospecto = null;
                            } else {
                                $model->dv_prospecto = $rut[1];
                            }
                            $model->nombre = $line[11];
                            $model->deuda = strtoupper($line[12]);
                            $model->tipo_creacion = 1;
                            if ($model->save()) {
                                $counter = $counter + 1;
                            } else {
                                $error = $model->getErrors();
                                break;
                            }
                        }
                    }
                } else {
                    $error[0][0] = "Archivo Vacio";
                }
            } else {
                $error[0][0] = "Archivo Inválido";
            }
        }

        return $this->render('uploadprospectos', array('error' => $error, 'counter' => $counter, 'fileChecker' => $fileChecker));
    }

    //se ejecuta para subir los 5 archivos de ventas desde el csv
    //RENDERIZA LA VISTA
    //OK
    public function actionUploadcsvventa()
    {
        ini_set('memory_limit', '2048M');
        $error = [];
        $fileChecker = false;
        $counterNews = 0;
        $counterUpdate = 0;
        $counterPerdidas = 0;
        //var_dump($tipoBase);
        //verificamos si la variable $_FILES existe
        try{
        if ((!empty($_FILES)) && isset($_FILES['cvs'])) {
            $fileChecker = 1;
            //si el archivo es csv continuamos, sino damos el error
            $kaboom = explode('.', $_FILES['cvs']['name']);
            if ($kaboom[count($kaboom)-1]== 'csv') {
                //usamos la funcion para leer csv que nos entregará un arreglo por cada linea, con los
                //campos en utf-8
                if ($_FILES['cvs']['error'] === UPLOAD_ERR_OK) {
                    $lines = $this->readCSV($_FILES['cvs']['tmp_name']);
                    $lines = array_filter($lines);

                    if (count($lines) > 0) {
                        switch ($_POST['tipo']) {
                            case 1:
                                $response = $this->loadVenta2($lines, $_POST['tipoBase']);
                                $error = $response['error'];
                                $counterNews = $response['counterNews'];
                                $counterUpdate = $response['counterUpdate'];
                                $counterPerdidas = $response['counterPerdidas'];
                                break;
                            case 2:
                                $response = $this->loadCobranza($lines);
                                $error = $response['error'];
                                $counterNews = $response['counterNews'];
                                $counterUpdate = $response['counterUpdate'];
                                break;
                            case 3:
                                $response = $this->loadDx($lines);
                                $error = $response['error'];
                                $counterNews = $response['counterNews'];
                                $counterUpdate = $response['counterUpdate'];
                                break;
                            /*case 4:
                                $response = $this->loadApertura($lines);
                                $error = $response['error'];
                                $counterNews = $response['counterNews'];
                                $counterUpdate = $response['counterUpdate'];
                                break;*/
                            case 5:
                                $response = $this->loadDotacion($lines);
                                $error = $response['error'];
                                $counterNews = $response['counterNews'];
                                $counterUpdate = $response['counterUpdate'];
                                break;
                            default:
                                $error = [];
                                $counter = 0;
                                break;
                        }

                    } else {
                        $error[0][0] = "Archivo Vacio";
                    }
                }else{
                    throw new UploadException($_FILES['cvs']['error']);
                }
            } else {
                $error[0][0] = "Archivo Inválido";
            }
        }
        }catch(UploadException $e){
            $error[0][0] = $e->getMessage();
        }
        return $this->render('uploadventas', array('error' => $error, 'counterNews' => $counterNews, 'counterUpdate' => $counterUpdate, 'fileChecker' => $fileChecker, 'counterPerdidas' => $counterPerdidas));
    }

    //CREAR EL EXCEL DE USUARIOS
    //OK
    public function actionReporteusers(){

        Utils::log("Generando reporte de Usuarios");
        $cookiee_name = "token";
        $cookiee_value = $_GET["token"];
        $model = new Vendedor();
        $currentUser = Yii::$app->user->identity;
        $dataAcciones = [];
        $dataUbicaciones = [];
        $idsNotActivities = [1, 4, 5, 6,  ];

        $start_date = $_GET['from'] != '' ? strtotime($_GET['from']) : 0;
        $end_date = $_GET['to'] != '' ? strtotime($_GET['to']) : time();

        if ($currentUser->tipo_usuario == 1){
            $vendedores = $model->find()->where(['>','id', '13'])->andWhere(['NOT IN', 'tipo_usuario', $idsNotActivities])->all();
        }elseif ($currentUser->tipo_usuario ==2){
            $vendedores = $currentUser->getSupervisorSupervisas()->all();
            for ($i = 0 ; $i < count($vendedores); $i++){
                $vendedores[$i] = $model->findOne($vendedores[$i]->id_vendedor);
            }
        }

        $filename = "Reporte Usuarios" . date('d-m-Y H:i') . ".xls";

        if ($currentUser->tipo_usuario == 1 ) {
            $headers = [
                'Nombre' => 'string',
                'Tango' => 'string',
                'Tango Super' => 'string',
                'Accion Realizada' => 'string',
                'Fecha' => 'datetime',
                'Ubicación' => 'string'
            ];
        }else{
            $headers = [
                'Nombre' => 'string',
                'Tango' => 'string',
                'Tango Super' => 'string',
                'Accion Realizada' => 'string',
                'Fecha' => 'datetime',
            ];
        }

        $headerStyle = [
            'font' => 'Arial',
            'font-size' => 10,
            'font-style' => 'bold'
        ];

        if(isset($vendedores)){
            foreach ($vendedores as $vendedor){
                $supervisor = $vendedor->getSupervisorSupervisas0()->one();
                if(!is_null($supervisor)){
                    $supervisor = $model->findOne($supervisor->id_supervisor);
                }else{
                    $supervisor = null;
                }
                $accionesVendedor[] = AccionVendedor::find()->where(['id_vendedor' => $vendedor->id])->andWhere(['between', 'timestamp', $start_date, $end_date])->all();

                if (count($accionesVendedor) > 0){
                    foreach ($accionesVendedor[0] as $accionVendedor){
                        if ($accionVendedor->accion != 'Update ubicacion') {
                            $dataAcciones[] = [
                                $vendedor->nombre,
                                $vendedor->username,
                                !is_null($supervisor) ? $supervisor->username: "",
                                $accionVendedor->accion,
                                date('Y-m-d H:i',$accionVendedor->timestamp),
                                $currentUser->tipo_usuario == 1 ? $accionVendedor->lat.", ".$accionVendedor->lon : ""
                            ];
                        }else{
                            $dataUbicaciones[] = [
                                $vendedor->nombre,
                                $vendedor->username,
                                !is_null($supervisor) ? $supervisor->username: "",
                                $accionVendedor->accion,
                                date('Y-m-d H:i',$accionVendedor->timestamp),
                                $accionVendedor->lat.", ".$accionVendedor->lon
                            ];
                        }

                    }
                }
                $accionesVendedor = [];
            }
        }



        $writer = new \XLSXWriter();
        $writer->setAuthor('CFK Group');

        $writer->writeSheetHeader('Resumen Acciones Vendedor', $headers, $headerStyle);
        foreach($dataAcciones as $row) {
            $writer->writeSheetRow('Resumen Acciones Vendedor', $row);
        }

        if($currentUser->tipo_usuario == 1) {
            $writer->writeSheetHeader('Resumen Ubicaciones', $headers, $headerStyle);
            foreach ($dataUbicaciones as $row) {
                $writer->writeSheetRow('Resumen Ubicaciones', $row);
            }
        }

        $writer->writeToFile("data//".\XLSXWriter::sanitize_filename($filename));
        header("Content-disposition: attachment; filename=".\XLSXWriter::sanitize_filename($filename));
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        setcookie($cookiee_name, $cookiee_value);

        if(readfile("data//".\XLSXWriter::sanitize_filename($filename))){
            unlink("data//".\XLSXWriter::sanitize_filename($filename));
            return "success";
            //exit();
        }else{
            return "error";
        }
    }

    //CREAR EL EXCEL DE DIRECCIONES
    //OK
    public function actionReportedirecciones()
    {
        $cookiee_name = "token";
        $cookiee_value = $_GET["token"];
        $model = new Prospecto();
        $currentUser = Yii::$app->user->identity;
        $dataDireccion = [];
        $direcciones = [];

        $idsNotReport = [1, 4, 5, 6,  ];

        $start_date = $_GET['from'] != '' ? strtotime($_GET['from']) : 0;
        $end_date = $_GET['to'] != '' ? strtotime($_GET['to']) : time();

        if ($currentUser->tipo_usuario == 1) {
            $direcciones[0] = $model->find()->where(['not in', 'id_vendedor', $idsNotReport])->andWhere(['between', 'create_time', $start_date, $end_date])->all();
        } elseif ($currentUser->tipo_usuario == 2) {
            $vendedores = $currentUser->getSupervisorSupervisas()->all();
            for ($i = 0; $i < count($vendedores); $i++) {
                $direcciones[$i] = $model->find()->where(['id_vendedor' => $vendedores[$i]->id_vendedor])->andWhere(['between', 'create_time', $start_date, $end_date])->all();
            }
        }
        //var_dump($direcciones);

        $filename = "Reporte Direcciones y Acciones Comerciales" . date('d-m-Y H:i') . ".xlsx";

            $headers = [
                'Nombre Prospecto' => 'string',
                'Rut Prospecto' => 'string',
                'Tipo de Carga' => 'string',
                'Calle' => 'string',
                'Comuna' => 'string',
                'Nodo' => 'string',
                'Cuadrante' => 'string',
                'Deuda' => 'string',
                'Nombre Comprador' => 'string',
                'Rut Comprador' => 'string',
                'Teléfono de Contacto 1' => 'string',
                'Teléfono de Contacto 2' => 'string',
                'Email' => 'string',
                'Servicio a Contratar Teléfono' => 'string',
                'Servicio a Contratar Cable' => 'string',
                'Servicio a Contratar Internet' => 'string',
                'Accion Comercial' => 'string',
                'Fecha' => 'string',
                'Estado' => 'string',
                'Tango Vendedor' => 'string',
                'Accion Realizada' => 'string',
                'Contacto Realizado' => 'string',
                'Fecha de Creacion del Registro' => 'string',
                'Fecha de Modificacion del Registro' => 'string',
            ];

            $headerStyle = [
                'font' => 'Arial',
                'font-size' => 10,
                'font-style' => 'bold'
            ];

            foreach ($direcciones as $dir) {
                foreach ($dir as $direccion) {
                    //FALTA UN CICLO PARA RECORRER CADA DIRECCION PERTENECIENTE A LAS DIRECCIONES DE UN VENDEDOR
                    if (!is_null($direccion) && !empty($direccion)){
                        $accionesComerciales = $direccion->getAccionComercials()->all();
                        if  (is_null($direccion->id_vendedor)){
                            $vendedor = "No Asigando";
                        }else{
                            $vendedor = trim(($direccion->getIdVendedor()->one()))['username'];
                        }
                        if (count($accionesComerciales) > 0){
                            foreach ($accionesComerciales as $accionComercial) {
                                $dataDireccion[] = [
                                    trim($direccion->nombre),
                                    trim($direccion->rut_prospecto) . ' - ' . trim($direccion->dv_prospecto),
                                    $direccion->tipo_carga == 1 ? "Carga por Excel" : "Carga Manual",
                                    trim($direccion->calle),
                                    trim($direccion->comuna),
                                    trim($direccion->nodo),
                                    trim($direccion->cuadrante),
                                    trim($direccion->deuda),
                                    trim($direccion->rut_comprador) . ' - ' . trim($direccion->dv_comprador),
                                    trim($direccion->nombre_comprador),
                                    trim($direccion->fono_contacto_1),
                                    trim($direccion->fono_contacto_2),
                                    trim($direccion->email),
                                    trim($direccion->tipo_tv),
                                    trim($direccion->tipo_fono),
                                    trim($direccion->tipo_inet),
                                    trim($accionComercial->accion),
                                    date('d-m-Y H:i', $accionComercial->timestamp),
                                    trim($direccion->estado),
                                    $vendedor,
                                    trim($direccion->tipo_accion),
                                    trim($direccion->tipo_contacto),
                                    is_null($direccion->create_time) ? '' : date('d-m-Y H:i', $direccion->create_time),
                                    is_null($direccion->update_time) ? '' : date('d-m-Y H:i', $direccion->update_time)
                                ];
                            }
                        }else{
                            $dataDireccion[] = [
                                trim($direccion->nombre),
                                trim($direccion->rut_prospecto) . ' - ' . trim($direccion->dv_prospecto),
                                $direccion->tipo_carga == 1 ? "Carga por Excel" : "Carga Manual",
                                trim($direccion->calle),
                                trim($direccion->comuna),
                                trim($direccion->nodo),
                                trim($direccion->cuadrante),
                                trim($direccion->deuda),
                                trim($direccion->rut_comprador) . ' - ' . trim($direccion->dv_comprador),
                                trim($direccion->nombre_comprador),
                                trim($direccion->fono_contacto_1),
                                trim($direccion->fono_contacto_2),
                                trim($direccion->email),
                                trim($direccion->tipo_tv),
                                trim($direccion->tipo_fono),
                                trim($direccion->tipo_inet),
                                "",
                                "",
                                trim($direccion->estado),
                                $vendedor,
                                trim($direccion->tipo_accion),
                                trim($direccion->tipo_contacto),
                                is_null($direccion->create_time) ? '' : date('d-m-Y', $direccion->create_time),
                                is_null($direccion->update_time) ? '' : date('d-m-Y', $direccion->update_time)
                            ];
                        }
                    }
                }
            }

            $writer = new \XLSXWriter();
            $writer->setAuthor('CFK Group');

            $writer->writeSheetHeader('Resumen Direcciones', $headers, $headerStyle);
            foreach($dataDireccion as $row) {
                $writer->writeSheetRow('Resumen Direcciones', $row);
            }

            $writer->writeToFile(\XLSXWriter::sanitize_filename($filename));
            header("Content-disposition: attachment; filename=".\XLSXWriter::sanitize_filename($filename));
            header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            setcookie($cookiee_name, $cookiee_value);
            if(readfile(\XLSXWriter::sanitize_filename($filename))){
                unlink(\XLSXWriter::sanitize_filename($filename));
                exit();
                //return "success";
            }else{
                //return "error";
            }
    }

    public function actionResumedata($range, $id_usuario, $tipo_usuario){
        #$usuario = Vendedor::getById($id_usuario);
        $today = time();
        $from = strtotime($range, $today);

        if ($tipo_usuario == 2) {
            #Obtener direcciones cargadas
            $vendedoresSupevisados = SupervisorSupervisa::find()->select('id_vendedor')->where(['id_supervisor' => $id_usuario])->all();

            $dirCargadas = Prospecto::find()->where(['tipo_creacion' => '1'])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->andWhere(['between', 'create_time', $from, $today])->count();

            $prospectosConAccionComercial = AccionComercial::find()->select('id_prospecto as id')->andWhere(['between', 'timestamp', $from, $today])->distinct()->all();
            $prospectosConAccionComercialSupervisado = Prospecto::find()->where(['in', 'id', $prospectosConAccionComercial])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->count();

            $prospectosCreados = Prospecto::find()->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->andWhere(['tipo_creacion' => '2'])->orWhere(['tipo_creacion' => 3])->andWhere(['between', 'create_time', $from, $today])->count();

            $ventasTerminadas = Venta::find()->where(['estado_tango' => 'TERMINADA'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:s', $from), date('Y-m-d H:i:s', $today)])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->count();
            $ventasEnInstalacion = Venta::find()->where(['estado_tango' => 'INS'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:S', $from), date('Y-m-d H:i:s', $today)])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->count();

            $dataVendedores = [];

            foreach ($vendedoresSupevisados as $vendedor) {
                $vendedor = Vendedor::getById($vendedor->id_vendedor);
                #cantidad de direcciones cargadas para el prospecto desde excel
                $dc = Prospecto::find()->where(['tipo_creacion' => '1'])->andWhere(['id_vendedor' => $vendedor->id])->andWhere(['between', 'create_time', $from, $today])->count();

                $pcac = 0;
                $prospectosVendedor = $vendedor->getProspectos();
                foreach ($prospectosVendedor as $prospecto) {
                    $acciones = AccionComercial::find()->where(['id_prospecto' => $prospecto->id])->andWhere(['between', 'timestamp', $from, $today])->count();
                    if ($acciones > 0) {
                        $pcac++;
                    }
                }
                $pc = Prospecto::find()->andWhere(['id_vendedor' => $vendedor->id])->andWhere(['tipo_creacion' => '2'])->orWhere(['tipo_creacion' => 3])->andWhere(['between', 'create_time', $from, $today])->count();

                $vt = Venta::find()->where(['estado_tango' => 'TERMINADA'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:S', $from), date('Y-m-d H:i:s', $today)])->andWhere(['id_vendedor' => $vendedor->id])->count();
                $vi = Venta::find()->where(['estado_tango' => 'INS'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:S', $from), date('Y-m-d H:i:s', $today)])->andWhere(['id_vendedor' => $vendedor->id])->count();

                $dataVendedores[$vendedor->username]['direccionesCargadas'] = $dc;
                $dataVendedores[$vendedor->username]['prospectosConAccionComercial'] = $pcac;
                $dataVendedores[$vendedor->username]['prospectosCreados'] = $pc;
                $dataVendedores[$vendedor->username]['ventasTerminadas'] = $vt;
                $dataVendedores[$vendedor->username]['ventasEnInstalacion'] = $vi;

            }
        }

        if ($tipo_usuario == 1) {
            #Obtener direcciones cargadas
            $supervisores = SupervisorSupervisa::find()->select('id_supervisor')->andWhere(['>', 'id_supervisor', 13])->distinct()->all();
            $vendedoresSupevisados = SupervisorSupervisa::find()->where(['in', 'id_supervisor', $supervisores])->andWhere(['>', 'id_vendedor', 13])->all();
            /* echo "<pre>";
             print_r($vendedoresSupevisados);
             echo "</pre>";*/
            $dirCargadas = Prospecto::find()->where(['tipo_creacion' => '1'])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->andWhere(['between', 'create_time', $from, $today])->count();

            $prospectosConAccionComercial = AccionComercial::find()->select('id_prospecto as id')->andWhere(['between', 'timestamp', $from, $today])->distinct()->all();
            $prospectosConAccionComercialSupervisado = Prospecto::find()->where(['in', 'id', $prospectosConAccionComercial])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->count();

            $prospectosCreados = Prospecto::find()->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->andWhere(['tipo_creacion' => '2'])->orWhere(['tipo_creacion' => 3])->andWhere(['between', 'create_time', $from, $today])->count();

            $ventasTerminadas = Venta::find()->where(['estado_tango' => 'TERMINADA'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:s', $from), date('Y-m-d H:i:s', $today)])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->count();
            $ventasEnInstalacion = Venta::find()->where(['estado_tango' => 'INS'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:S', $from), date('Y-m-d H:i:s', $today)])->andWhere(['in', 'id_vendedor', $vendedoresSupevisados])->count();

            $dataVendedores = [];

            foreach ($supervisores as $supervisor) {
                $vendedores = SupervisorSupervisa::find()->where(['in', 'id_supervisor', $supervisor->id_supervisor])->all();
                $dc = 0;
                $pcac = 0;
                $pc = 0;
                $vt = 0;
                $vi = 0;
                foreach ($vendedores as $vendedor) {
                    $vendedor = Vendedor::getById($vendedor->id_vendedor);
                    #cantidad de direcciones cargadas para el prospecto desde excel
                    $dc += Prospecto::find()->where(['tipo_creacion' => '1'])->andWhere(['id_vendedor' => $supervisor->id])->andWhere(['between', 'create_time', $from, $today])->count();
                    $prospectosVendedor = $vendedor->getProspectos()->all();
                    foreach ($prospectosVendedor as $prospecto) {
                        //var_dump(is_null($prospecto));
                        //if(!is_null($prospecto) && count($prospecto) > 0) {
                        //var_dump($prospecto);
                            $acciones = AccionComercial::find()->where(['id_prospecto' => $prospecto->id])->andWhere(['between', 'timestamp', $from, $today])->count();
                            if ($acciones > 0) {
                                $pcac++;
                            }
                        //}
                    }
                    $pc += Prospecto::find()->andWhere(['id_vendedor' => $vendedor->id])->andWhere(['tipo_creacion' => '2'])->orWhere(['tipo_creacion' => 3])->andWhere(['between', 'create_time', $from, $today])->count();

                    $vt += Venta::find()->where(['estado_tango' => 'TERMINADA'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:S', $from), date('Y-m-d H:i:s', $today)])->andWhere(['id_vendedor' => $vendedor->id])->count();
                    $vi += Venta::find()->where(['estado_tango' => 'INS'])->andWhere(['between', 'dia_registro', date('Y-m-d H:i:S', $from), date('Y-m-d H:i:s', $today)])->andWhere(['id_vendedor' => $vendedor->id])->count();
                }
                $supervisor = Vendedor::getById($supervisor->id_supervisor);
                $dataVendedores[$supervisor->username]['direccionesCargadas'] = $dc;
                $dataVendedores[$supervisor->username]['prospectosConAccionComercial'] = $pcac;
                $dataVendedores[$supervisor->username]['prospectosCreados'] = $pc;
                $dataVendedores[$supervisor->username]['ventasTerminadas'] = $vt;
                $dataVendedores[$supervisor->username]['ventasEnInstalacion'] = $vi;

            }
        }


        $range = $range . ' ' . $id_usuario;
        $return = [
            'direccionesCargadas' => $dirCargadas,
            'prospectosConAccionComercial' => $prospectosConAccionComercialSupervisado,
            'prospectosCreados' => $prospectosCreados,
            'ventasTerminadas' => $ventasTerminadas,
            'ventasEnInstalacion' => $ventasEnInstalacion,
            'dataVendedores' => $dataVendedores
        ];
        return Json::encode($return);
    }
}