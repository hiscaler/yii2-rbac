# yii2-rbac
RBAC For Yii2

# 安装
composer require yadjet/yii2-rbac


# 配置
安装完毕后，你需要配置控制文件中的 authManager 组件属性，例如

	'authManager' => [
        'class' => 'yii\rbac\DbManager',
        'defaultRoles' => [],
		......
    ],


#实现访问控制
创建自己的控制器，并确保从 \yadjet\rbac\controllers\RbacController 类继承，例如：

	namespace backend\controllers;
	
	class Controller extends \yadjet\rbac\controllers\RbacController
	{

		public function actionIndex() {
			......
		}
	
	}
