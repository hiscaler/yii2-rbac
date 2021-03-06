<?php

namespace yadjet\rbac\controllers;

use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\Response;

class DefaultController extends \yii\web\Controller
{

    use \yadjet\rbac\helpers\ModuleHelper;

    public $layout = 'main';

    /** @var \yii\rbac\DbManager $auth */
    protected $auth;

    public function init()
    {
        parent::init();
        $this->auth = \Yii::$app->getAuthManager();
        if ($this->auth === null) {
            throw new Exception('Please setting authManager component in config file.');
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 解析控制器中的动作代码
     * @param $file
     * @return array
     */
    private function _parseControllerFile($file)
    {
        $count = 0;
//        $controller = ($moduleId) ? "{$moduleId}.{$controller}" : $controller;
        $h = file($file);
        $rows = count($h);
        $actions = $descriptions = [];
        for ($i = 0; $i < $rows; $i++) {
            $line = trim($h[$i]);
            if (in_array($line, ['', '/**', '*', '*/', '{', '}', '<?php', '?>']) || strpos($line, 'actions()') || (strpos($line, 'description') === false && strpos($line, 'function') === false)) {
                continue;
            }
            if (preg_match("/^(.+)function( +)action*/", $line)) {
                $posAct = strpos(trim($line), "action");
                $posPar = strpos(trim($line), "(");
                $patterns[0] = '/\s*/m';
                $patterns[1] = '#\((.*)\)#';
                $patterns[2] = '/\{/m';
                $replacements[2] = '';
                $replacements[1] = '';
                $replacements[0] = '';
                $action = preg_replace($patterns, $replacements, trim(trim(substr(trim($line), $posAct, $posPar - $posAct))));
                $actions[$i] = preg_replace("/action/", "", $action, 1);
            } elseif (preg_match("/^\*( +)@description( +)*/", $line)) {
                $descriptions[$i] = trim(str_replace('* @description', '', $line));
            }

            $count = count($actions);
            if ($count != count($descriptions)) {
                $descriptions = array_pad($descriptions, $count, null);
            }
        }

        return ($count) ? array_combine($actions, $descriptions) : [];
    }

    public function actionScan()
    {
        $options = $this->getModuleOptions();
        $appId = Yii::$app->id;
        $actions = $files = [];
        $paths = [
            "{$appId}@" => Yii::$app->getControllerPath()
        ];

        foreach (Yii::$app->getModules() as $key => $config) {
            $moduleId = Yii::$app->getModule($key)->getUniqueId();
            if (empty($moduleId) || in_array($moduleId, $options['disabledScanModules'])) {
                continue;
            }
            $paths["{$appId}@{$moduleId}@"] = Yii::$app->getModule($moduleId)->getControllerPath();
        }

        foreach ($paths as $moduleId => $path) {
            if (!isset($files[$moduleId])) {
                $files[$moduleId] = [];
            }
            $files[$moduleId] = FileHelper::findFiles($path);
        }

        $existsActions = $this->auth->getPermissions();
        foreach ($files as $moduleId => $items) {
            foreach ($items as $file) {
                $parseActions = $this->_parseControllerFile($file);
                foreach ($parseActions as $key => $description) {
                    $name = $moduleId . Inflector::camelize(str_replace('Controller', '', basename($file, '.php')) . '.' . Inflector::camel2id($key));
                    $actions[] = [
                        'name' => $name,
                        'description' => isset($existsActions[$name]) ? $existsActions[$name]->description : $description,
                        'active' => isset($existsActions[$name]) ? false : true,
                    ];
                }
            }
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $actions,
        ]);
    }

}
