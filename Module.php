<?php

namespace yadjet\rbac;

use Yii;
use yii\base\BootstrapInterface;
use yii\web\ForbiddenHttpException;

class Module extends \yii\base\Module implements BootstrapInterface
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'yadjet\rbac\controllers';

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app instanceof \yii\web\Application && !$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        return true;
    }

    /**
     * @return boolean whether the module can be accessed by the current user
     */
    protected function checkAccess()
    {
        return true;
    }

}
