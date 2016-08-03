<?php

namespace yadjet\rbac\controllers;

use Yii;
use yii\rbac\Item;
use yii\web\Response;

class UsersController extends Controller
{

    /**
     * 获取所有用户
     * @return Response
     */
    public function actionIndex()
    {
        $items = Yii::$app->getDb()->createCommand('SELECT * FROM {{%user}}')->queryAll();

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $items,
        ]);
    }

    /**
     * 用户分配的角色
     * @param integer|mixed $id 用户 id
     * @return Response
     */
    public function actionRoles($id = null)
    {
        if (!$id) {
            $id = Yii::$app->getUser()->getId();
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => array_values(Yii::$app->getAuthManager()->getRolesByUser($id)),
        ]);
    }

    /**
     * 用户分配的权限
     * @param integer|mixed $id
     * @return Response
     */
    public function actionPermissions($id = null)
    {
        if (!$id) {
            $id = Yii::$app->getUser()->getId();
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => Yii::$app->getAuthManager()->getPermissionsByUser($id),
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

    /**
     * 分配用户角色
     * @return Response
     */
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
            $errorMessage = 'Parameters error.';
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

    /**
     * 撤销用户角色
     * @return Response
     */
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
            $errorMessage = 'Parameters error.';
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
