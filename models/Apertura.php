<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "apertura".
 *
 * @property integer $id
 * @property string $id_tango
 * @property string $id_sgi
 * @property string $id_gis
 * @property string $nombre_proyecto
 * @property string $direccion
 * @property string $nodo
 * @property string $subnodo
 * @property string $comuna
 * @property string $proyecto
 * @property integer $fecha_inicio_apertura
 * @property integer $fecha_inicio_basal
 * @property string $dealer
 * @property integer $fecha_liber_operador
 */
class Apertura extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'apertura';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_tango'], 'required'],
            [['id_tango'], 'unique'],
            [['fecha_inicio_apertura', 'fecha_inicio_basal', 'fecha_liber_operador'], 'integer'],
            [['id_tango', 'comuna', 'dealer'], 'string', 'max' => 100],
            [['id_sgi', 'id_gis'], 'string', 'max' => 45],
            [['nombre_proyecto', 'direccion', 'proyecto'], 'string', 'max' => 1000],
            [['nodo', 'subnodo'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_tango' => 'Id Tango',
            'id_sgi' => 'Id Sgi',
            'id_gis' => 'Id Gis',
            'nombre_proyecto' => 'Nombre Proyecto',
            'direccion' => 'Direccion',
            'nodo' => 'Nodo',
            'subnodo' => 'Subnodo',
            'comuna' => 'Comuna',
            'proyecto' => 'Proyecto',
            'fecha_inicio_apertura' => 'Fecha Inicio Apertura',
            'fecha_inicio_basal' => 'Fecha Inicio Basal',
            'dealer' => 'Dealer',
            'fecha_liber_operador' => 'Fecha Liber Operador',
        ];
    }
}
