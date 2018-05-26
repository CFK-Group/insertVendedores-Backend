<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\commands\Utils;

/**
 * LoginForm is the model behind the login form.
 *
 * @property Vendedor|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser($this->username);            
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
    	
        if ($this->validate()) {
        	Utils::log("Usuario validado, creando login...");
            $authStatus = Yii::$app->user->login($this->getUser($this->username), $this->rememberMe ? 3600*24*30 : 0);
            return $authStatus;
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser($username)
    {
    	Utils::log("usernameBuscando=" . $username);
        $model = new Vendedor;
        if ($this->_user === false) {
            $this->_user = $model->findByUsername($username);
        }
        return $this->_user;
    }
}
