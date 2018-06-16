<?php

namespace app\models;

use Yii;
use app\commands\Utils;

/**
 * This is the model class for table "vendedor".
 *
 * @property integer $id
 * @property string $username
 * @property string $zona
 * @property string $codi_pertenece
 * @property string $canal
 * @property string $codi_equipo
 * @property string $nuevo_equipo
 * @property string $jefaturas
 * @property string $coordinadores
 * @property string $rut
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string $desc_cargo
 * @property string $rut_empresa
 * @property string $desc_empresa
 * @property integer $smartflex
 * @property string $estado
 * @property integer $fecha_ini_lic
 * @property integer $fecha_ter_lic
 * @property string $nps
 * @property string $ilumina
 * @property string $observacion
 * @property string $ciudad
 * @property string $territorios
 * @property string $hire_date
 * @property integer $dismissal_date
 * @property string $telefono
 * @property string $android_gcm_token
 * @property string $hash
 * @property string $salt
 * @property integer $tipo_usuario
 * @property string $api_token
 * @property integer $api_token_create_date
 * @property string $device_id
 * @property string $device_model
 * @property integer $dias_trabajados
 *
 * @property AccionVendedor[] $accionVendedors
 * @property Chat[] $chats
 * @property Chat[] $chats0
 * @property Prospecto[] $prospectos
 * @property SupervisorSupervisa[] $supervisorSupervisas
 * @property SupervisorSupervisa[] $supervisorSupervisas0
 * @property Venta[] $ventas
 * @property VentaPerdida[] $ventaPerdidas
 */
class Vendedor extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const TOKEN_ALIVE_TIME = 86400; // 1 dia

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendedor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['fecha_ini_lic', 'fecha_ter_lic', 'hire_date', 'dismissal_date', 'tipo_usuario', 'api_token_create_date', 'dias_trabajados'], 'integer'],
            [['username', 'zona', 'canal', 'codi_equipo', 'rut', 'nombre', 'apellido', 'rut_empresa', 'desc_empresa', 'estado', 'observacion', 'ciudad', 'territorios', 'telefono', 'smartflex'], 'string', 'max' => 45],
            [['codi_pertenece', 'nuevo_equipo', 'jefaturas'], 'string', 'max' => 100],
            [['coordinadores', 'email'], 'string', 'max' => 200],
            [['desc_cargo'], 'string', 'max' => 50],
            [['nps', 'ilumina'], 'string', 'max' => 2],
            [['android_gcm_token'], 'string', 'max' => 256],
            [['hash'], 'string', 'max' => 255],
            [['salt'], 'string', 'max' => 128],
            [['api_token', 'device_id', 'device_model'], 'string', 'max' => 64],
            [['username'], 'unique', 'targetClass' => 'app\models\Vendedor'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Codigo Tango',
            'zona' => 'Zona',
            'codi_pertenece' => 'Codi Pertenece',
            'canal' => 'Canal',
            'codi_equipo' => 'Codi Equipo',
            'nuevo_equipo' => 'Nuevo Equipo',
            'jefaturas' => 'Jefaturas',
            'coordinadores' => 'Coordinadores',
            'rut' => 'Rut',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'email' => 'Email',
            'desc_cargo' => 'Desc Cargo',
            'rut_empresa' => 'Rut Empresa',
            'desc_empresa' => 'Desc Empresa',
            'smartflex' => 'Smartflex',
            'estado' => 'Estado',
            'fecha_ini_lic' => 'Fecha Ini Lic',
            'fecha_ter_lic' => 'Fecha Ter Lic',
            'nps' => 'Nps',
            'ilumina' => 'Ilumina',
            'observacion' => 'Observacion',
            'ciudad' => 'Ciudad',
            'territorios' => 'Territorios',
            'hire_date' => 'Hire Date',
            'dismissal_date' => 'Dismissal Date',
            'telefono' => 'Telefono',
            'android_gcm_token' => 'Android Gcm Token',
            'hash' => 'Hash',
            'salt' => 'Salt',
            'tipo_usuario' => 'Tipo Usuario',
            'api_token' => 'Api Token',
            'api_token_create_date' => 'Api Token Create Date',
            'device_id' => 'Device ID',
            'device_model' => 'Device Model',
            'dias_trabajados' => 'Dias Trabajados',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccionVendedors()
    {
        return $this->hasMany(AccionVendedor::className(), ['id_vendedor' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChats()
    {
        return $this->hasMany(Chat::className(), ['idEjecutivo' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChats0()
    {
        return $this->hasMany(Chat::className(), ['idVendedor' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProspectos()
    {
        return $this->hasMany(Prospecto::className(), ['id_vendedor' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupervisorSupervisas()
    {
        return $this->hasMany(SupervisorSupervisa::className(), ['id_supervisor' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupervisorSupervisas0()
    {
        return $this->hasMany(SupervisorSupervisa::className(), ['id_vendedor' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Venta::className(), ['id_vendedor' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentaPerdidas()
    {
        return $this->hasMany(VentaPerdida::className(), ['id_vendedor' => 'id']);
    }

    public static function getBySessionToken($token){
        $user = Vendedor::findOne(["api_token" => $token]);
        if($user == null)
            return null;
        if($user->validateSession())
            return $user;
        return null;
    }

    public function validateSession(){
        if($this->api_token_create_date == null)
            return false;
        if($this->api_token_create_date < time() - Vendedor::TOKEN_ALIVE_TIME)
            return false;
        return true;
    }

    /**
     * Obtiene un vendedor en base a su codigo de usuario
     * @param string $userCode
     * @return NULL|\app\models\Vendedor
     */
    public static function getByUsername($userCode){
        $user = Vendedor::findOne(["username" => $userCode]);
        if( !isset($user))
            return null;
        return $user;
    }

    public static function getById($id){
        $user = Vendedor::findOne(["id" => $id]);
        if( !isset($user))
            return null;
        return $user;
    }


    public static function getAllSupers(){
        $supers = Vendedor::find()->where(['tipo_usuario'=> '2'])->all();
        if (!isset($supers))
            return null;
        return $supers;
    }

    public function getUsersbysuper($id_super){
        $rows = Yii::$app->getDb()->createCommand(
            "SELECT * FROM
            vendedor v
            join supervisor_supervisa s on s.id_vendedor =v.id
            where s.id_supervisor = $id_super;")
            ->queryAll();
        return $rows;
    }

    public static function isSuper($descripcion_cargo){
        $pattern_super =  '/.supervisor./im';
        $pattern_vendedor = '/.ejecutivo./im';
        $isSuper = preg_match($pattern_super, $descripcion_cargo);
        $isvendedor = preg_match($pattern_vendedor, $descripcion_cargo);
        if($isSuper){
            return 2;
        }elseif ($isvendedor){
            return 3;
        }else{
            return 0;
        }
    }

    /**
     * Interface
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

    public static function findIdentityByAccessToken($token, $type = null)
    {
        //return $this->findOne(['api_token' => $username]);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
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
        Utils::log("psw desde clase vendedor=" . $psw);
        return password_verify($psw, $this->hash);
    }

    public function getTipoUsuario(){
        return $this->tipo_usuario;
    }
}
