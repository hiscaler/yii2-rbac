<?php

namespace yadjet\rbac\controllers;

use Yii;
use yii\rbac\Item;
use yii\web\Response;

class UsersController extends Controller
{

    public function actionIndex()
    {
        $items = Yii::$app->getDb()->createCommand('SELECT * FROM {{%user}}')->queryAll();

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $items,
        ]);
    }

    public function actionRoles($id = null)
    {
        $items = [];
        if (!$id) {
            $user = Yii::$app->getUser();
            $id = $user->getIsGuest() ? 0 : $user->getId();
        }
        $auth = Yii::$app->getAuthManager();
        $items = array_values($auth->getRolesByUser($id));

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $items,
        ]);
    }

    public function actionPermissions($id = null)
    {
        $items = [];
        if (!$id) {
            $user = Yii::$app->getUser();
            $id = $user->getIsGuest() ? 0 : $user->getId();
        }
        $auth = Yii::$app->getAuthManager();
        $items = $auth->getPermissionsByUser($id);

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $items,
        ]);
    }

    public function actionAuths($id = null)
    {
        $items = [];
        if (!$id) {
            $user = Yii::$app->getUser();
            $id = $user->getIsGuest() ? 0 : $user->getId();
        }
        if ($id) {
            $auth = Yii::$app->getAuthManager();
            $db = Yii::$app->getDb();
            $ownRoles = $auth->getRolesByUser($id);
            $ownPermissions = $auth->getPermissionsByUser($id);

            $itemCommand = $db->createCommand('SELECT * FROM {{%auth_item}} WHERE [[type]] = :type');
            $roles = $itemCommand->bindValue(':type', Item::TYPE_ROLE)->queryAll();
            $permissions = $itemCommand->bindValue(':type', Item::TYPE_PERMISSION)->queryAll();

            foreach ($roles as $key => $role) {
                $roles[$key]['own'] = isset($ownRoles[$role['name']]);
            }

            foreach ($permissions as $key => $permission) {
                $permissions[$key]['own'] = isset($ownPermissions[$permission['name']]);
            }
            $items = [
                'userId' => $id,
                'roles' => $roles,
                'permissions' => $permissions,
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $items,
        ]);
    }

    public function actionAssign()
    {
        $success = true;
        $errorMessage = null;
        $rawBody = Yii::$app->getRequest()->getRawBody();
        $rawBody = json_decode($rawBody, true);
        if ($rawBody !== null) {
            $roleName = isset($rawBody['roleName']) ? $rawBody['roleName'] : null;
            $userId = isset($rawBody['userId']) ? $rawBody['userId'] : null;

            $auth = Yii::$app->getAuthManager();
            $auth->assign($auth->getRole($roleName), $userId);
        } else {
            $success = false;
            $errorMessage = '参数错误。';
        }

        $responseBody = ['success' => $success];
        if (!$success) {
            $responseBody['error']['message'] = $errorMessage;
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

    public function actionRevoke()
    {
        $success = true;
        $errorMessage = null;
        $rawBody = Yii::$app->getRequest()->getRawBody();
        $rawBody = json_decode($rawBody, true);
        if ($rawBody !== null) {
            $roleName = isset($rawBody['roleName']) ? $rawBody['roleName'] : null;
            $userId = isset($rawBody['userId']) ? $rawBody['userId'] : null;

            $auth = Yii::$app->getAuthManager();
            $auth->revoke($auth->getRole($roleName), $userId);
        } else {
            $success = false;
            $errorMessage = '参数错误。';
        }

        $responseBody = ['success' => $success];
        if (!$success) {
            $responseBody['error']['message'] = $errorMessage;
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

}
