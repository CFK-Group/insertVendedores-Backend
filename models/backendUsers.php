<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property string $nombre
 * @property string $apellido
 * @property string $username
 * @property string $email
 * @property string $hash
 * @property integer $rol
 */
class backendUsers extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rol'], 'integer'],
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
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'username' => 'Username',
            'email' => 'Email',
            'hash' => 'Hash',
            'rol' => 'Rol',
        ];
    }

    public static function primaryKey()
    {
        return array('username');
    }

    public static function getUsers(){
        $user = backendUsers::find()->all();
        if( !isset($user))
            return null;
        return $user;
    }

    public function validatePassword($entry_password){
        return $this->hash === $entry_password;
    }

    public static function findByUsername($username){
        return self::findOne($username);
    }
    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($username)
    {
        // TODO: Implement findIdentity() method.
        return self::findOne($username);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
        throw new NotSupportedException();

    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
        $username = $this->username;
        $user = "";
        switch (self::findOne($username)['rol']){
            case "":
                $user = Vendedor::getByUsername($username)['id'];
                break;
            case 1:
                $user = Jefatura::getByUsername($username)['id'];
                break;
            case 2:
                $user = Vendedor::getByUsername($username)['id'];
                break;
            case 3:
                $user = Vendedor::getByUsername($username)['id'];
                break;
        }
        return $user;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
        throw new NotSupportedException();
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
        throw new NotSupportedException();
    }
}
