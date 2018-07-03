<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\commands\Utils;

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



class User extends \yii\db\ActiveRecord  implements IdentityInterface
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendedor';
    }

    public static function primaryKey(){
        return 'id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
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

    /**
     * USER LOGIN VALIDATION METHODS
     */


    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Serach user by username
     */
    public function findByUsername($username){
    	Utils::log("User::findByUsername para " . $username);
        return $this->findOne(['username' => $username]);
    }
    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    	return User::findOne(['api_token' => $token]);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->getPrimaryKey();
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
     * @throws NotSupportedException
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        
    }

    /**
     * Validates the given auth key.
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @throws NotSupportedException
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        
    }

    public function validatePassword($psw){
    	Utils::log("psw desde clase user=" . $psw);
    	return password_verify($psw, $this->hash);
    }
}
