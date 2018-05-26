<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "accion_vendedor".
 *
 * @property integer $id
 * @property string $id_vendedor
 * @property integer $timestamp
 * @property string $accion
 * @property double $lat
 * @property double $lon
 *
 * @property Vendedor $idVendedor
 */
class AccionVendedor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'accion_vendedor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_vendedor', 'timestamp'], 'required'],
            [['id_vendedor', 'timestamp'], 'integer'],
            [['lat', 'lon'], 'number'],
            [['accion'], 'string', 'max' => 45],
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
            'timestamp' => 'Timestamp',
            'accion' => 'Accion',
            'lat' => 'Lat',
            'lon' => 'Lon',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdVendedor()
    {
        return $this->hasOne(Vendedor::className(), ['id' => 'id_vendedor'])->inverseOf('accionVendedors');
    }

    public static function getLastlogin($id_vendedor){
        $logins = AccionVendedor::find()->where(['id_vendedor' => $id_vendedor])->andWhere(['accion' => 'login'])->max('timestamp');
        if(!isset($logins)){
            return null;
        }
        return $logins;
    }

    public static function getLastaccion($id_vendedor){
        $logins = AccionVendedor::find()->select(['accion', 'timestamp'])->where(['id_vendedor' => $id_vendedor])->orderBy('timestamp DESC')->one();
        if(!isset($logins)){
            return null;
        }
        return $logins;
    }

    public static function getLastaccionsuper($id_vendedor){
        $logins = AccionVendedor::find()->select(['accion', 'timestamp'])->where(['id_vendedor' => $id_vendedor])->andWhere(['not like', 'accion', 'Update ubicacion'])->orderBy('timestamp DESC')->one();
        if(!isset($logins)){
            return null;
        }
        return $logins;
    }

    public static function getLastposition($id_vendedor){
        $logins = AccionVendedor::find()->select(['timestamp','lat', 'lon'])->where(['id_vendedor' => $id_vendedor])->andWhere(['like', 'accion', 'Update ubicacion'])->orderBy('timestamp DESC')->one();
        if(!isset($logins)){
            return null;
        }
        return $logins;
    }

    public static function getAccionesPorDia(){
        date_default_timezone_set("Chile/Continental");
        $diaInicio = strtotime("-1 week midnight");
        $hoy = strtotime("today midnight");
        $accionesPorDia = [];
        $acciones = Yii::$app->db->createCommand(
            "SELECT DISTINCT accion FROM accion_vendedor WHERE accion NOT LIKE '%Actualiza%' AND accion NOT LIKE '%Nuevo%' and accion not like 'update ubicacion'"
        )->queryAll();
        for ($j = 0; $j < count($acciones); $j++){
            //var_dump("inicio ciclo i ".$i);
            for ($i = $diaInicio ; $i <= $hoy ; $i+=86400){
              //  var_dump("inicio ciclo j ".$j);
               // var_dump($acciones[$j]);
                $accionesPorDia[array_values($acciones[$j])[0]] [date('d-m', $i)]= array_values(Yii::$app->db->createCommand(
                    "SELECT COUNT(accion) as counter FROM accion_vendedor WHERE accion LIKE :accion AND timestamp between :i and :j"
                )->bindValues(array(':accion' => array_values($acciones[$j])[0], ':i' => $i, ':j' =>($i+86400)))->queryAll())[0]['counter'];
            }
        }
        return $accionesPorDia;
    }

}
