<?php

namespace yadjet\rbac\controllers;

use Exception;
use Yii;
use yii\rest\Controller;
use yii\web\Response;

class PermissionsController extends Controller
{

    /**
     * 返回定义的所有权限
     * @return Response
     */
    public function actionIndex()
    {
        $items = Yii::$app->getAuthManager()->getPermissions();

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => array_values($items),
        ]);
    }

    /**
     * 添加权限
     * @return Response
     */
    public function actionCreate()
    {
        $request = Yii::$app->getRequest();
        if ($request->isPost) {
            $success = true;
            $errorMessage = null;
            $rawBody = $request->getRawBody();
            $rawBody = json_decode($rawBody, true);
            if ($rawBody !== null) {
                // is post json value
                $name = isset($rawBody['name']) ? $rawBody['name'] : null;
                $description = isset($rawBody['description']) ? $rawBody['description'] : null;
            } else {
                $name = trim($request->post('name'));
                $description = trim($request->post('description'));
            }
            if (empty($name)) {
                $success = false;
                $errorMessage = '名称不能为空。';
            } else {
                $auth = \Yii::$app->getAuthManager();
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }

            $responseBody = [
                'success' => $success,
            ];
            if (!$success) {
                $responseBody['error']['message'] = $errorMessage;
            } else {
                $permission = (array) $permission;
                $responseBody['data'] = $permission;
            }

            return new Response([
                'format' => Response::FORMAT_JSON,
                'data' => $responseBody
            ]);
        }
    }

    /**
     * 删除权限
     * @param string $name
     * @return Response
     */
    public function actionDelete($name)
    {
        try {
            $name = trim($name);
            $auth = Yii::$app->getAuthManager();
            $permission = $auth->getPermission($name);
            $auth->remove($permission);
            $responseBody = [
                'success' => true,
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

}
