<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "accion_comercial".
 *
 * @property integer $id
 * @property integer $id_prospecto
 * @property string $accion
 * @property integer $timestamp
 *
 * @property Prospecto $idProspecto
 */
class AccionComercial extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'accion_comercial';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_prospecto'], 'required'],
            [['id_prospecto', 'timestamp'], 'integer'],
            [['accion'], 'string', 'max' => 45],
            [['id_prospecto'], 'exist', 'skipOnError' => true, 'targetClass' => Prospecto::className(), 'targetAttribute' => ['id_prospecto' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_prospecto' => 'Id Prospecto',
            'accion' => 'Accion',
            'timestamp' => 'Timestamp',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProspecto()
    {
        return $this->hasOne(Prospecto::className(), ['id' => 'id_prospecto'])->inverseOf('accionComercials');
    }

    public static function getAccionesComercialesPorProspecto($id_prospecto, $id_super){
        $rows = Yii::$app->getDb()->createCommand(
            "SELECT * FROM 
             accion_comercial a 
             join prospecto p on p.id=a.id_prospecto 
             join supervisor_supervisa s on s.id_vendedor = p.id_vendedor
             where s.id_supervisor = $id_super
             and a.id_prospecto = $id_prospecto;")
            ->queryAll();
        return $rows;
    }

    public static function getLastAccionComercial($id_prospecto){
        $accions = AccionComercial::find()->where(["id_prospecto"=>$id_prospecto])->orderBy('timestamp DESC')->limit(5)->all();
        if(!isset($accions)){
            return null;
        }
        return $accions;
    }

    public static function getAccionesComercialesEdo($id_prospecto){
        $rows = Yii::$app->getDb()->createCommand(
            "SELECT * FROM 
             accion_comercial a 
             join prospecto p on p.id=a.id_prospecto 
             and a.id_prospecto = $id_prospecto;")
            ->queryAll();
        return $rows;
    }
}
