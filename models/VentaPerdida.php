<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "venta_perdida".
 *
 * @property integer $id
 * @property string $id_vendedor
 * @property integer $tipo_carga
 * @property integer $rut_cliente
 * @property string $dv_cliente
 * @property string $servicio
 * @property integer $id_servicio
 * @property integer $id_vivienda
 * @property string $dia_registro
 * @property string $dia_final
 * @property string $comuna
 * @property string $canal
 * @property string $equipo
 * @property string $tipo_venta
 * @property string $b2b
 * @property string $producto
 * @property string $valor
 * @property string $observaciones
 * @property string $estado_tango
 * @property string $cruce_con_base_vtr
 * @property string $facturada
 * @property string $comisionable
 * @property string $estado_venta1
 * @property integer $id_proyecto
 * @property integer $mes_venta
 * @property string $fecha_proyecto
 * @property string $cruce
 * @property string $visacion
 * @property string $area_funcional
 * @property string $zona
 * @property string $territorio
 * @property string $estado_venta
 * @property string $PCS
 * @property string $codigo_area_funcional
 * @property string $venta_en_cobranza
 * @property string $ciclo_en_facturacion
 * @property string $flujo_cobranza
 * @property string $rango
 * @property integer $dias_mora
 * @property string $fecha_vencimiento
 * @property string $fecha_analisis
 * @property string $zona2
 * @property string $territorio2
 * @property string $nombre
 * @property string $fono1
 * @property string $fono2
 * @property string $email
 * @property integer $monto_deuda
 * @property string $estado_vendedor
 * @property string $tango_super
 * @property string $super
 * @property string $portal
 * @property string $vtr
 * @property string $bst
 * @property string $estado_rendicion
 * @property string $fecha_visado
 * @property string $no_visado
 * @property string $motivos_en_regularizacion
 * @property string $motivos_rechazo_visado
 * @property string $observaciones_visado
 * @property string $cambio_codigo
 * @property integer $n_servicio
 * @property string $insert
 *
 * @property Vendedor $idVendedor
 */
class VentaPerdida extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'venta_perdida';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_vendedor', 'tipo_carga', 'rut_cliente', 'id_servicio', 'id_vivienda', 'id_proyecto', 'mes_venta', 'dias_mora', 'monto_deuda', 'n_servicio'], 'integer'],
            [['dia_registro', 'dia_final', 'fecha_proyecto', 'fecha_vencimiento', 'fecha_analisis', 'portal', 'vtr'], 'safe'],
            [['dv_cliente'], 'string', 'max' => 1],
            [['servicio', 'comuna', 'canal', 'equipo', 'tipo_venta', 'b2b', 'valor', 'estado_tango', 'cruce_con_base_vtr', 'facturada', 'comisionable', 'estado_venta1', 'cruce', 'visacion', 'zona', 'territorio', 'estado_venta', 'PCS', 'codigo_area_funcional', 'venta_en_cobranza', 'ciclo_en_facturacion', 'flujo_cobranza', 'rango', 'zona2', 'territorio2', 'fono1', 'fono2', 'email', 'estado_vendedor', 'tango_super', 'super', 'bst', 'estado_rendicion', 'fecha_visado', 'no_visado', 'cambio_codigo'], 'string', 'max' => 45],
            [['producto', 'observaciones', 'area_funcional'], 'string', 'max' => 128],
            [['nombre'], 'string', 'max' => 90],
            [['motivos_en_regularizacion', 'motivos_rechazo_visado', 'observaciones_visado'], 'string', 'max' => 400],
            [['insert'], 'string', 'max' => 80],
            [['id_vendedor'], 'exist', 'skipOnError' => true, 'targetClass' => Vendedor::className(), 'targetAttribute' => ['id_vendedor' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_vendedor' => 'Id Vendedor',
            'tipo_carga' => 'Tipo Carga',
            'rut_cliente' => 'Rut Cliente',
            'dv_cliente' => 'Dv Cliente',
            'servicio' => 'Servicio',
            'id_servicio' => 'Id Servicio',
            'id_vivienda' => 'Id Vivienda',
            'dia_registro' => 'Dia Registro',
            'dia_final' => 'Dia Final',
            'comuna' => 'Comuna',
            'canal' => 'Canal',
            'equipo' => 'Equipo',
            'tipo_venta' => 'Tipo Venta',
            'b2b' => 'B2b',
            'producto' => 'Producto',
            'valor' => 'Valor',
            'observaciones' => 'Observaciones',
            'estado_tango' => 'Estado Tango',
            'cruce_con_base_vtr' => 'Cruce Con Base Vtr',
            'facturada' => 'Facturada',
            'comisionable' => 'Comisionable',
            'estado_venta1' => 'Estado Venta 1',
            'id_proyecto' => 'Id Proyecto',
            'mes_venta' => 'Mes Venta',
            'fecha_proyecto' => 'Fecha Proyecto',
            'cruce' => 'Cruce',
            'visacion' => 'Visacion',
            'area_funcional' => 'Area Funcional',
            'zona' => 'Zona',
            'territorio' => 'Territorio',
            'estado_venta' => 'Estado Venta',
            'PCS' => 'Pcs',
            'codigo_area_funcional' => 'Codigo Area Funcional',
            'venta_en_cobranza' => 'Venta En Cobranza',
            'ciclo_en_facturacion' => 'Ciclo En Facturacion',
            'flujo_cobranza' => 'Flujo Cobranza',
            'rango' => 'Rango',
            'dias_mora' => 'Dias Mora',
            'fecha_vencimiento' => 'Fecha Vencimiento',
            'fecha_analisis' => 'Fecha Analisis',
            'zona2' => 'Zona2',
            'territorio2' => 'Territorio2',
            'nombre' => 'Nombre',
            'fono1' => 'Fono1',
            'fono2' => 'Fono2',
            'email' => 'Email',
            'monto_deuda' => 'Monto Deuda',
            'estado_vendedor' => 'Estado Vendedor',
            'tango_super' => 'Tango Super',
            'super' => 'Super',
            'portal' => 'Portal',
            'vtr' => 'Vtr',
            'bst' => 'Bst',
            'estado_rendicion' => 'Estado Rendicion',
            'fecha_visado' => 'Fecha Visado',
            'no_visado' => 'No Visado',
            'motivos_en_regularizacion' => 'Motivos En Regularizacion',
            'motivos_rechazo_visado' => 'Motivos Rechazo Visado',
            'observaciones_visado' => 'Observaciones Visado',
            'cambio_codigo' => 'Cambio Codigo',
            'n_servicio' => 'N Servicio',
            'insert' => 'Insert',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdVendedor()
    {
        return $this->hasOne(Vendedor::className(), ['id' => 'id_vendedor']);
    }
}
