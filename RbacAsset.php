<?php

namespace yadjet\rbac;

use yii\web\AssetBundle;

class RbacAsset extends AssetBundle
{

    public $sourcePath = '@vendor/yadjet/yii2-rbac/assets';
    public $css = [
        'rbac.css',
    ];
    public $js = [
        'vue.js',
        'vue-resource.js',
        'rbac.js',
    ];
    public $depends = [];

}
