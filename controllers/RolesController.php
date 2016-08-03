<?php

namespace yadjet\rbac\controllers;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\Response;

class RolesController extends Controller
{

//    public function behaviors()
//    {
//        return [
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'create' => ['post'],
//                    'delete' => ['post'],
//                ],
//            ],
//        ];
//    }

    public function actionIndex()
    {
//        $items = Yii::$app->getDb()->createCommand('SELECT * FROM {{%auth_item}} WHERE [[type]] = :type', [':type' => Item::TYPE_ROLE])->queryAll();
//        foreach ($items as $key => $item) {
//            $items[$key]['deleteUrl'] = Url::toRoute(['roles/delete', 'name' => $item['name']]);
//        }
        $items = array_values(Yii::$app->getAuthManager()->getRoles());

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $items,
        ]);
    }

    public function actionCreate()
    {
        $request = Yii::$app->getRequest();
        if ($request->isPost) {
            $success = true;
            $errorMessage = null;
            $name = trim($request->post('name'));
            if (empty($name)) {
                $success = false;
                $errorMessage = '名称不能为空。';
            } else {
                $description = trim($request->post('description'));
                $auth = Yii::$app->getAuthManager();
                $role = $auth->createRole($name);
                $role->description = $description;
                $auth->add($role);
            }

            $responseBody = [
                'success' => $success,
            ];
            if (!$success) {
                $responseBody['error']['message'] = $errorMessage;
            } else {
                $role = (array) $role;
                $role['deleteUrl'] = Url::toRoute(['roles/delete', 'name' => $role['name']]);
                $responseBody['data'] = $role;
            }

            return new Response([
                'format' => Response::FORMAT_JSON,
                'data' => $responseBody
            ]);
        }
    }

    public function actionDelete($name)
    {
        try {
            $name = trim($name);
            $auth = Yii::$app->getAuthManager();
            $role = $auth->getRole($name);
            $auth->remove($role);
            $responseBody = [
                'success' => true,
                'data' => $role,
            ];
        } catch (Exception $ex) {
            $responseBody = [
                'success' => false,
                'error' => [
                    'message' => $ex->getMessage(),
                ]
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

    /**
     * 获取角色关联的权限
     * @param string $roleName
     * @return Response
     */
    public function actionPermissionsByRole($roleName)
    {
        $auth = Yii::$app->getAuthManager();
        $permissions = array_values($auth->getPermissionsByRole($roleName));

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $permissions,
        ]);
    }

    /**
     * 添加角色和权限关联关系
     * @param string $roleName
     * @param string $permissionName
     * @return Response
     */
    public function actionAddChild($roleName, $permissionName)
    {
        try {
            $role = $this->auth->getRole($roleName);
            $permission = $this->auth->getPermission($permissionName);
            $this->auth->addChild($role, $permission);
            $responseBody = [
                'success' => true,
            ];
        } catch (Exception $ex) {
            $responseBody = [
                'success' => false,
                'error' => ['message' => $ex->getMessage()],
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

    /**
     * 移除角色和权限关联关系
     * @param string $roleName
     * @param string $permissionName
     * @return Response
     */
    public function actionRemoveChild($roleName, $permissionName)
    {
        try {
            $auth = Yii::$app->getAuthManager();
            $auth->removeChild($this->auth->getRole($roleName), $this->auth->getPermission($permissionName));
            $responseBody = ['success' => true];
        } catch (Exception $ex) {
            $responseBody = [
                'success' => false,
                'error' => [
                    'message' => $ex->getMessage(),
                ]
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

}
