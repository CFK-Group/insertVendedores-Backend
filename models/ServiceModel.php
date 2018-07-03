<?php

namespace app\models;

use Yii;

class ServiceModel extends \yii\db\ActiveRecord {
	
	public $serviceToken;
	public $status;
	
	public function validateToken(){
		return true;
	}
	
}