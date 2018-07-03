<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jefatura".
 *
 * @property integer $id
 * @property string $nombre
 * @property string $apellido
 * @property string $username
 * @property string $email
 * @property string $hash
 * @property integer $tipo_usuario
 */
class Jefatura extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jefatura';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nombre', 'apellido', 'username', 'hash'], 'required'],
            [['tipo_usuario'], 'integer'],
            [['nombre', 'apellido', 'username', 'email'], 'string', 'max' => 45],
            [['hash'], 'string', 'max' => 255],
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
            'apellido' => 'Apellido',
            'username' => 'Username',
            'email' => 'Email',
            'hash' => 'Hash',
            'tipo_usuario' => 'Tipo Usuario',
        ];
    }

    public static function getByUsername($userCode){
        $user = Jefatura::findOne(["username" => $userCode]);
        if( !isset($user))
            return null;
        return $user;
    }


}
