<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prospecto".
 *
 * @property integer $id
 * @property string $nombre
 * @property integer $rut_prospecto
 * @property string $dv_prospecto
 * @property string $calle
 * @property integer $numero
 * @property string $comuna
 * @property integer $nodo
 * @property integer $cuadrante
 * @property string $fono
 * @property string $cable
 * @property string $inet
 * @property string $premium
 * @property string $deuda
 * @property integer $rut_comprador
 * @property string $dv_comprador
 * @property string $nombre_comprador
 * @property string $fono_contacto_1
 * @property string $fono_contacto_2
 * @property string $email
 * @property string $tipo_tv
 * @property string $tipo_fono
 * @property string $tipo_inet
 * @property string $accion_comercial
 * @property integer $estado
 * @property integer $id_vendedor
 * @property integer $tipo_creacion
 * @property string $tipo_accion
 * @property string $tipo_contacto
 * @property integer $create_time
 * @property integer $update_time
 *
 * @property AccionComercial[] $accionComercials
 * @property Vendedor $idVendedor
 */
class Prospecto extends \yii\db\ActiveRecord
{
    const CREACION_USER=2;
    const CREACION_WEB=1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'prospecto';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rut_prospecto', 'numero', 'nodo', 'cuadrante', 'rut_comprador', 'estado', 'id_vendedor', 'tipo_creacion', 'create_time', 'update_time'], 'integer'],
            [['tipo_creacion', 'id_vendedor'], 'required'],
            [['nombre', 'calle', 'nombre_comprador'], 'string', 'max' => 128],
            [['dv_prospecto', 'dv_comprador'], 'string', 'max' => 1],
            [['comuna', 'fono_contacto_1', 'fono_contacto_2', 'email', 'tipo_tv', 'tipo_fono', 'tipo_inet', 'accion_comercial', 'tipo_accion', 'tipo_contacto'], 'string', 'max' => 45],
            [['fono', 'cable', 'inet', 'premium', 'deuda'], 'string', 'max' => 2],
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
            'nombre' => 'Nombre',
            'rut_prospecto' => 'Rut Prospecto',
            'dv_prospecto' => 'Dv Prospecto',
            'calle' => 'Calle',
            'numero' => 'Numero',
            'comuna' => 'Comuna',
            'nodo' => 'Nodo',
            'cuadrante' => 'Cuadrante',
            'fono' => 'Fono',
            'cable' => 'Cable',
            'inet' => 'Inet',
            'premium' => 'Premium',
            'deuda' => 'Deuda',
            'rut_comprador' => 'Rut Comprador',
            'dv_comprador' => 'Dv Comprador',
            'nombre_comprador' => 'Nombre Comprador',
            'fono_contacto_1' => 'Fono Contacto 1',
            'fono_contacto_2' => 'Fono Contacto 2',
            'email' => 'Email',
            'tipo_tv' => 'Tipo Tv',
            'tipo_fono' => 'Tipo Fono',
            'tipo_inet' => 'Tipo Inet',
            'accion_comercial' => 'Accion Comercial',
            'estado' => 'Estado',
            'id_vendedor' => 'Tango Vendedor',
            'tipo_creacion' => 'Tipo Creacion',
            'tipo_accion' => 'Tipo Accion',
            'tipo_contacto' => 'Tipo Contacto',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccionComercials()
    {
        return $this->hasMany(AccionComercial::className(), ['id_prospecto' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdVendedor()
    {
        return $this->hasOne(Vendedor::className(), ['id' => 'id_vendedor']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccionesComerciales()
    {
        return $this->hasMany(AccionComercial::className(), ['id_prospecto' => 'id'])->all();
    }

    public function getProspectobysuper($id_super){
        $rows = Yii::$app->getDb()->createCommand(
            "SELECT *, p.id as prospecto_id FROM
            prospecto p
            join supervisor_supervisa s on s.id_vendedor = p.id_vendedor
            where s.id_supervisor = $id_super;")
            ->queryAll();
        return $rows;
    }

    public function getProspectoEdo(){
        $rows = Yii::$app->getDb()->createCommand(
            "SELECT *, p.id as prospecto_id FROM
            prospecto p
            join supervisor_supervisa s on s.id_vendedor = p.id_vendedor")
            ->queryAll();
        return $rows;
    }
}
