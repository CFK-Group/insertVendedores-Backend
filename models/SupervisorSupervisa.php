<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "supervisor_supervisa".
 *
 * @property integer $id
 * @property string $id_supervisor
 * @property string $id_vendedor
 *
 * @property Vendedor $idSupervisor
 * @property Vendedor $idVendedor
 */
class SupervisorSupervisa extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'supervisor_supervisa';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_supervisor', 'id_vendedor'], 'integer'],
            [['id_supervisor'], 'exist', 'skipOnError' => true, 'targetClass' => Vendedor::className(), 'targetAttribute' => ['id_supervisor' => 'id']],
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
            'id_supervisor' => 'Id Supervisor',
            'id_vendedor' => 'Id Vendedor',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdSupervisor()
    {
        return $this->hasOne(Vendedor::className(), ['id' => 'id_supervisor'])->inverseOf('supervisorSupervisas');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdVendedor()
    {
        return $this->hasOne(Vendedor::className(), ['id' => 'id_vendedor'])->inverseOf('supervisorSupervisas0');
    }

    public function getIdSuper($id_vendedor){
        return $this->find()->where(['id_vendedor' => $id_vendedor])->one();
    }
}
