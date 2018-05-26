<?php

namespace app\commands;

class Utils {
	
	public static function log($log){
		\Yii::info("=====>" . $log);
	}
		
}
