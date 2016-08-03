<?php

namespace yadjet\rbac\controllers;

use yii\base\Exception;

class Controller extends \yii\rest\Controller
{

    /**
     *
     * @return \yii\rbac\DbManager
     */
    protected $auth;

    public function init()
    {
        parent::init();
        $this->auth = \Yii::$app->getAuthManager();
        if ($this->auth === false) {
            throw new Exception('Please setting authManager component');
        }
    }

}
