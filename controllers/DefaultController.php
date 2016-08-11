<?php

namespace yadjet\rbac\controllers;

use yii\base\Exception;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\rbac\Item;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{

    public $layout = 'main';

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
        $configs = [
            'disableScanModules' => ['gii', 'rbac'], // 禁止扫描的模块
        ];
        $actions = $files = [];
        $paths = [
            'app' => \Yii::$app->getControllerPath()
        ];

        foreach (\Yii::$app->getModules() as $module) {
            $moduleId = $module->getUniqueId();
            if (empty($moduleId) || in_array($moduleId, $configs['disableScanModules'])) {
                continue;
            }
            try {
                $paths[$moduleId] = FileHelper::findFiles(\Yii::$app->getModule($module->getUniqueId())->getControllerPath());
            } catch (Exception $ex) {
                
            }
        }
        foreach ($paths as $moduleId => $path) {
            if (!isset($files[$moduleId])) {
                $files[$moduleId] = [];
            }
            $files[$moduleId] = FileHelper::findFiles($path);
        }

        $existsActions = (new Query())->select(['name'])->from('{{%auth_item}}')->where(['type' => Item::TYPE_PERMISSION])->column();
        foreach ($files as $moduleId => $items) {
            foreach ($items as $file) {
                $parseActions = $this->_parseControllerFile($file);
                foreach ($parseActions as $key => $description) {
                    $name = $moduleId . Inflector::camelize(str_replace('Controller', '', basename($file, '.php')) . '.' . Inflector::camel2id($key));
                    $actions[] = [
                        'name' => $name,
                        'description' => $description,
                        'active' => in_array($name, $existsActions) ? false : true,
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
