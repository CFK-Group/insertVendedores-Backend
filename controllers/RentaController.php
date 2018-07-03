<?php

namespace app\controllers;

use app\models\SupervisorSupervisa;
use app\models\Vendedor;
use app\models\Venta;
use Faker\Provider\cs_CZ\DateTime;

class RentaController extends \yii\web\Controller
{

    public function actionIndex()
    {
        //var_dump($this->actionCalcularrenta());
        $vendedores = Vendedor::find()->where(['tipo_usuario' => 3])->andWhere(['>', 'id', 13])->all();
        $renta = [];
        foreach ($vendedores as $vendedor){
            //$renta[] = $vendedor;
            $renta[] = $this->actionCalcularrentaweb($vendedor->username);
        }
        return $this->render('index', ['renta' => $renta]);
    }



    public function actionCalcularrentaweb($tango){

        $vendedor = Vendedor::getByUsername($tango);
        $id_vendedor = $vendedor->id;

        $hire_date = $vendedor->hire_date;

        if (!is_null($hire_date)) {
            $antiguedad = $this->calcularAntiguedad($vendedor->hire_date);
            $comision_por_ventas = $this->getComisionVentas($id_vendedor, $antiguedad);
            $bono_concurso = $this->getBonoConcurso($id_vendedor, $antiguedad);
            $bono_permanencia = $this->getBonoPermanencia($id_vendedor);
            $variablesRenta = $comision_por_ventas + $bono_concurso + $bono_permanencia;
            return ["nombre" => $vendedor->nombre, "tango" => $vendedor->username, "renta" => $variablesRenta, "comisionVenta" => $comision_por_ventas, "bonoConcurso" => $bono_concurso, "bonoPermanencia" => $bono_permanencia, "error" => 0];
        }else {
            return ["nombre" => $vendedor->nombre, "tango" => $vendedor->username, "renta" => 0, "comisionVenta" => 0, "bonoConcurso" => 0, "bonoPermanencia" => 0, "error" => 1];
        }

    }


    /**
     * ESTA FUNCION ES PARA EL CALCULO A NIVEL DE LA APP MOVIL
     * MÁS ABAJO ESTÁ PARA LOS REPORTES WEB Y EXCEL
     */

    //***********************************************************************
    public function actionCalcularrenta(){
        $request = \Yii::$app->request;
        $object = $request->get();
        $token = $object["token"];

        if(!is_null($token)){
            $id_vendedor = Vendedor::getBySessionToken($token);
            $vendedor = Vendedor::getById($id_vendedor->id);
        }else{
            return json_encode(["renta" => 0, "comisionVenta" => 0, "bonoConcurso" => 0, "bonoPermanencia" => 0]);
        }

        $hire_date = $vendedor->hire_date;

        if (!is_null($hire_date)) {
            $antiguedad = $this->calcularAntiguedad(Vendedor::getById($id_vendedor)->hire_date);
            $comision_por_ventas = $this->getComisionVentas($id_vendedor, $antiguedad);
            $bono_concurso = $this->getBonoConcurso($id_vendedor, $antiguedad);
            $bono_permanencia = $this->getBonoPermanencia($id_vendedor);
            $variablesRenta = $comision_por_ventas + $bono_concurso + $bono_permanencia;
            return json_encode(["renta" => $variablesRenta, "comisionVenta" => $comision_por_ventas, "bonoConcurso" => $bono_concurso, "bonoPermanencia" => $bono_permanencia]);
        }else {
            return json_encode(["renta" => 0, "comisionVenta" => 0, "bonoConcurso" => 0, "bonoPermanencia" => 0]);
        }

    }

    //BONO PERMANENCIA FALTA SABER DE DONDE VIENEN LAS DESCONEXIONES
    public function getBonoPermanencia($id_vendedor){
        $RGU_basal = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['comisionable'=>'Si'])->count();
        $DX_basal =  (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_activos = $RGU_basal - $DX_basal;
        if ($RGU_basal != 0) {
            $Perm_basal = $RGU_activos / $RGU_basal;
            //en base a la permamencia se ve que valor de bono se les entrega
            $valor_unitario = 0;
            if ($Perm_basal >= 0 && $Perm_basal < 0.9) {
                $valor_unitario = 0;
            } elseif ($Perm_basal >= 0.9 && $Perm_basal <= 0.94) {
                $valor_unitario = 1500;
            } elseif ($Perm_basal >= 0.95 && $Perm_basal < 1) {
                $valor_unitario = 1800;
            } elseif ($Perm_basal >= 1) {
                $valor_unitario = 2000;
            }
            return ($valor_unitario * $RGU_activos);
        }else{
            return 0;
        }
    }

    //BONO CONCURSO FALTA SABER COMO SE CALCULA LA PERMANENCIA
    public function getBonoConcurso($id_vendedor, $antiguedad){
        $antiguedad_concurso = "";

        if ($antiguedad == 1 || $antiguedad == 2){
            $antiguedad_concurso = "nuevo";
        }elseif ($antiguedad >= 3){
            $antiguedad_concurso = "antiguo";
        }

        $RGU_alto = Venta::find()->where(['valor' => 'Alto'])->andWhere(['id_vendedor' => $id_vendedor])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_medio = Venta::find()->where(['valor' => 'Medio'])->andWhere(['id_vendedor' => $id_vendedor])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_bajo = Venta::find()->where(['valor' => 'Bajo'])->andWhere(['id_vendedor' => $id_vendedor])->andWhere(['comisionable'=>'Si'])->count();

        $RGU_concurso =  0;
        if ($antiguedad_concurso == "nuevo"){
            $RGU_concurso = $RGU_alto + $RGU_medio + $RGU_bajo;
        }elseif ($antiguedad_concurso == "antiguo"){
            $RGU_concurso = $RGU_alto + $RGU_medio;
        }


        $permanencia = $this->getPermanencia($id_vendedor); //calcular la permanencia como se calcula?

        $factor_permanencia = $this->calcularFactorPermanencia($permanencia);

        $bono_concurso = (int) ($factor_permanencia * $this->calcularBonoConcurso($antiguedad, $RGU_concurso));

        $bono_alto = 0;

        if ($antiguedad_concurso == "antiguo"){
            $bono_alto = $RGU_alto * 3500;
        }

        return ($bono_alto + $bono_concurso);
    }

    //COMISION POR VENTA FALTA SABER DE DONDE VIENEN LAS DESCONEXIONES
    public function getComisionVentas($id_vendedor, $antiguedad){
        $dias_trabajados = (int)  Vendedor::getById($id_vendedor)->dias_trabajados;
        $factor_meta = round($dias_trabajados/30, 2);

        $RGU_basal = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_apertura = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Apertura'])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_b2b = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['b2b'=>'Si'])->andWhere(['comisionable'=>'Si'])->count();

        $Dx_basal =  (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();
        $Dx_apertura = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'apertura'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();
        $Dx_b2b = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['b2b'=>'Si'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();

        $RGU_meta = $RGU_basal - $Dx_basal;
        $meta_antiguedad = $this->calcularMetaAntiguedad($antiguedad);

        $meta_mes = $meta_antiguedad * $factor_meta;

        $cumplimiento = round($RGU_meta/$meta_mes, 2);

        $valor_unitario = [
            "apertura" => $this->getValorUnitario($cumplimiento, "apertura"),
            "basal" => $this->getValorUnitario($cumplimiento, "basal", $antiguedad),
            "b2b" => $this->getValorUnitario($cumplimiento, "b2b")
        ];
        return (($valor_unitario["apertura"] * ($RGU_apertura - $Dx_apertura)) + ($valor_unitario["basal"] * ($RGU_basal - $Dx_basal)) + ($valor_unitario["b2b"] * ($RGU_b2b - $Dx_b2b)));

    }



    /**
     * AQUI SE REALIZAN LOS CALCULOS DE RENTA PARA LOS REPORTES
     */

    //***********************************************************************************
    public function getBonoPermanenciaWeb($id_vendedor){

        $RGU_basal = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['comisionable'=>'Si'])->count();
        $DX_basal =  (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_activos = $RGU_basal - $DX_basal;
        if ($RGU_basal != 0) {
            $Perm_basal = $RGU_activos / $RGU_basal;
            //en base a la permamencia se ve que valor de bono se les entrega
            $valor_unitario = 0;
            if ($Perm_basal >= 0 && $Perm_basal < 0.9) {
                $valor_unitario = 0;
            } elseif ($Perm_basal >= 0.9 && $Perm_basal <= 0.94) {
                $valor_unitario = 1500;
            } elseif ($Perm_basal >= 0.95 && $Perm_basal < 1) {
                $valor_unitario = 1800;
            } elseif ($Perm_basal >= 1) {
                $valor_unitario = 2000;
            }
            return ($valor_unitario * $RGU_activos);
        }else{
            return 0;
        }
    }

    //BONO CONCURSO FALTA SABER COMO SE CALCULA LA PERMANENCIA
    public function getBonoConcursoWeb($id_vendedor, $antiguedad){
        $antiguedad_concurso = "";

        if ($antiguedad == 1 || $antiguedad == 2){
            $antiguedad_concurso = "nuevo";
        }elseif ($antiguedad >= 3){
            $antiguedad_concurso = "antiguo";
        }

        $RGU_alto = Venta::find()->where(['valor' => 'Alto'])->andWhere(['id_vendedor' => $id_vendedor])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_medio = Venta::find()->where(['valor' => 'Medio'])->andWhere(['id_vendedor' => $id_vendedor])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_bajo = Venta::find()->where(['valor' => 'Bajo'])->andWhere(['id_vendedor' => $id_vendedor])->andWhere(['comisionable'=>'Si'])->count();

        $RGU_concurso =  0;
        if ($antiguedad_concurso == "nuevo"){
            $RGU_concurso = $RGU_alto + $RGU_medio + $RGU_bajo;
        }elseif ($antiguedad_concurso == "antiguo"){
            $RGU_concurso = $RGU_alto + $RGU_medio;
        }


        $permanencia = $this->getPermanencia($id_vendedor); //calcular la permanencia como se calcula?

        $factor_permanencia = $this->calcularFactorPermanencia($permanencia);

        $bono_concurso = (int) ($factor_permanencia * $this->calcularBonoConcurso($antiguedad, $RGU_concurso));

        $bono_alto = 0;

        if ($antiguedad_concurso == "antiguo"){
            $bono_alto = $RGU_alto * 3500;
        }

        return ($bono_alto + $bono_concurso);
    }

    //COMISION POR VENTA FALTA SABER DE DONDE VIENEN LAS DESCONEXIONES
    public function getComisionVentasWeb($id_vendedor, $antiguedad){
        $dias_trabajados = (int)  Vendedor::getById($id_vendedor)->dias_trabajados;
        $factor_meta = round($dias_trabajados/30, 2);

        $RGU_basal = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_apertura = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Apertura'])->andWhere(['comisionable'=>'Si'])->count();
        $RGU_b2b = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['b2b'=>'Si'])->andWhere(['comisionable'=>'Si'])->count();

        $Dx_basal =  (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'Basal'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();
        $Dx_apertura = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['tipo_venta'=>'apertura'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();
        $Dx_b2b = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['b2b'=>'Si'])->andWhere(['estado_tango' =>'De baja'])->andWhere(['comisionable'=>'Si'])->count();

        $RGU_meta = $RGU_basal - $Dx_basal;
        $meta_antiguedad = $this->calcularMetaAntiguedad($antiguedad);

        $meta_mes = $meta_antiguedad * $factor_meta;

        $cumplimiento = round($RGU_meta/$meta_mes, 2);

        $valor_unitario = [
            "apertura" => $this->getValorUnitario($cumplimiento, "apertura"),
            "basal" => $this->getValorUnitario($cumplimiento, "basal", $antiguedad),
            "b2b" => $this->getValorUnitario($cumplimiento, "b2b")
        ];
        return (($valor_unitario["apertura"] * ($RGU_apertura - $Dx_apertura)) + ($valor_unitario["basal"] * ($RGU_basal - $Dx_basal)) + ($valor_unitario["b2b"] * ($RGU_b2b - $Dx_b2b)));

    }

    //***********************************************************************************


    public function calcularFactorPermanencia($permanencia){
        if($permanencia < 0.7){
            return 0;
        }elseif ($permanencia >= 0.7 && $permanencia < 0.8){
            return 0.5;
        }elseif ($permanencia >= 0.8 && $permanencia < 0.85){
            return 0.7;
        }elseif ($permanencia >= 0.8 && $permanencia < 0.85){
            return 0.8;
        }elseif ($permanencia >= 0.9 && $permanencia <= 0.92){
            return 1;
        }elseif ($permanencia >= 0.93){
            return 1.1;
        }else{
            return 0;
        }
    }

    public function calcularBonoConcurso($antiguedad, $RGU_concurso){
        if ($antiguedad == 1){
            if ($RGU_concurso <10){
                return 0;
            }elseif ($RGU_concurso >= 10 && $RGU_concurso <13){
                return 40000;
            }elseif ($RGU_concurso >= 13 && $RGU_concurso <15){
                return 80000;
            }elseif ($RGU_concurso >= 15 && $RGU_concurso <20){
                return 120000;
            }elseif ($RGU_concurso >= 20 && $RGU_concurso <30){
                return 150000;
            }elseif ($RGU_concurso >= 30 && $RGU_concurso <50){
                return 180000;
            }elseif ($RGU_concurso >= 50 && $RGU_concurso <60){
                return 250000;
            }elseif ($RGU_concurso >= 60 && $RGU_concurso <70){
                return 325000;
            }elseif ($RGU_concurso >= 70 && $RGU_concurso <80){
                return 400000;
            }elseif ($RGU_concurso >= 80 && $RGU_concurso <90){
                return 6000 * $RGU_concurso;
            }elseif ($RGU_concurso >= 90){
                return 7000 * $RGU_concurso;
            }else{
                return 0;
            }
        }elseif($antiguedad == 2){
            if ($RGU_concurso <10 && $RGU_concurso <13){
                return 0;
            }elseif ($RGU_concurso >= 13 && $RGU_concurso <15){
                return 30000;
            }elseif ($RGU_concurso >= 15 && $RGU_concurso <20){
                return 50000;
            }elseif ($RGU_concurso >= 20 && $RGU_concurso <30){
                return 100000;
            }elseif ($RGU_concurso >= 30 && $RGU_concurso <50){
                return 120000;
            }elseif ($RGU_concurso >= 50 && $RGU_concurso <60){
                return 250000;
            }elseif ($RGU_concurso >= 60 && $RGU_concurso <70){
                return 325000;
            }elseif ($RGU_concurso >= 70 && $RGU_concurso <80){
                return 400000;
            }elseif ($RGU_concurso >= 80 && $RGU_concurso <90){
                return 6000 * $RGU_concurso;
            }elseif ($RGU_concurso >= 90){
                return 7000 * $RGU_concurso;
            }else{
                return 0;
            }
        }else{
            if ($RGU_concurso >= 0 && $RGU_concurso <31){
                return 0;
            }elseif ($RGU_concurso >= 31 && $RGU_concurso <35){
                return 60000;
            }elseif ($RGU_concurso >= 35 && $RGU_concurso <40){
                return 80000;
            }elseif ($RGU_concurso >= 40 && $RGU_concurso <45){
                return 120000;
            }elseif ($RGU_concurso >= 45 && $RGU_concurso <50){
                return 150000;
            }elseif ($RGU_concurso >= 50 && $RGU_concurso <60){
                return 250000;
            }elseif ($RGU_concurso >= 60 && $RGU_concurso <70){
                return 325000;
            }elseif ($RGU_concurso >= 70 && $RGU_concurso <80){
                return 400000;
            }elseif ($RGU_concurso >= 80 && $RGU_concurso <90){
                return 6000 * $RGU_concurso;
            }elseif ($RGU_concurso >= 90){
                return 7000 * $RGU_concurso;
            }else{
                return 0;
            }
        }
    }

    public function calcularMetaAntiguedad($antiguedad){
        if ($antiguedad == 1){
            return 6;
        }elseif ($antiguedad == 2){
            return 10;
        }elseif ($antiguedad == 3){
            return 14;
        }elseif ($antiguedad == 4){
            return 20;
        }elseif ($antiguedad == 5){
            return 25;
        }elseif ($antiguedad == 6){
            return 30;
        }elseif ($antiguedad >6){
            return 31;
        }else{
            return 0;
        }
    }

    public function calcularAntiguedad($hire_date)
    {
        $hire_day = date('d', strtotime($hire_date));
        $hire_month = date('m', strtotime($hire_date));
        $hire_year = date('Y', strtotime($hire_date));
        $today_month = date('m');
        $today_year = date('Y');
        //calcular antiguedad
        $dif_year = ($today_year - $hire_year) * 12;
        $dif_month = $dif_year + ($today_month - $hire_month);

        if ($hire_day <= 10){
            return $dif_month -1;
        }else{
            return $dif_month;
        }
    }

    public function getValorUnitario($cumplimiento, $param, $antiguedad = null){
        switch ($param){
            case "apertura":
                if ($cumplimiento < 1){
                    return 5000;
                }elseif ($cumplimiento >= 1){
                    return 7000;
                }
                break;
            case "basal":
                $cumplimiento = round($cumplimiento, 1, PHP_ROUND_HALF_DOWN);
                if ($antiguedad > 2){
                    if ($cumplimiento >= 0 && $cumplimiento <= 0.4){
                        return 2600;
                    }elseif ($cumplimiento > 0.4 && $cumplimiento <= 0.6){
                        return 6600;
                    }elseif ($cumplimiento > 0.6 && $cumplimiento <= 0.7){
                        return 7600;
                    }elseif ($cumplimiento > 0.7 && $cumplimiento <= 0.8){
                        return 8600;
                    }elseif ($cumplimiento > 0.8 && $cumplimiento <= 1){
                        return 9200;
                    }elseif ($cumplimiento > 1 && $cumplimiento <= 1.1){
                        return 9400;
                    }elseif ($cumplimiento > 1.1 && $cumplimiento <= 1.2){
                        return 9600;
                    }elseif ($cumplimiento > 1.2 && $cumplimiento <= 1.4){
                        return 9900;
                    }elseif ($cumplimiento > 1.4 && $cumplimiento <= 1.6){
                        return 10900;
                    }elseif ($cumplimiento > 1.6 && $cumplimiento < 2.01){
                        return 11100;
                    }elseif ($cumplimiento >= 2.01){
                        return 11300;
                    }
                }else{
                    return 9400;
                }
                break;
            case "b2b":
                return 4000;
                break;
            default:
                return 0;
                break;
        }
    }

    public function getPermanencia($id_vendedor){
        $fecha = new \DateTime(date('d-m-Y'));
        $fecha->modify('first day of this month');
        $primerDia = $fecha->modify('-4 month');
        $ultimoDia = $primerDia;
        $primerDia  = $primerDia->format('Y-m-d H:i:s');
        $ultimoDia = $ultimoDia->modify('last day of this month');
        $ultimoDia = $ultimoDia->format('Y-m-d H:i:s');
        $RGU = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['comisionable'=>'Si'])->andWhere(['between', 'dia_registro', $primerDia, $ultimoDia])->count();
        $RGU_activas = (int) Venta::find()->where(['id_vendedor'=>$id_vendedor])->andWhere(['comisionable'=>'Si'])->andWhere(['estado_tango'=>'Activa'])->andWhere(['between', 'dia_registro', $primerDia, $ultimoDia])->count();

        if ($RGU == 0 || $RGU_activas==0){
            return 0;
        }else{
            return $RGU_activas/$RGU;
        }
    }
}
