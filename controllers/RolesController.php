<?php

namespace yadjet\rbac\controllers;

use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\web\Response;

class RolesController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'add-child' => ['post'],
                    'create' => ['post'],
                    'delete' => ['post'],
                    'remove-child' => ['post'],
                    'remove-children' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $items = array_values($this->auth->getRoles());

        if ($this->getModuleOptions()['selfish']) {
            $appId = Yii::$app->id;
            $len = strlen($appId);
            foreach ($items as $key => $item) {
                if (strncmp($item->name, $appId, $len) !== 0) {
                    unset($items[$key]);
                }
            }
        }

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
                $role = $this->auth->createRole(Yii::$app->id . '@' . $name);
                $role->description = trim($request->post('description'));
                $this->auth->add($role);
            }

            $responseBody = [
                'success' => $success,
            ];
            if (!$success) {
                $responseBody['error']['message'] = $errorMessage;
            } else {
                $role = (array) $role;
                $responseBody['data'] = $role;
            }

            return new Response([
                'format' => Response::FORMAT_JSON,
                'data' => $responseBody
            ]);
        }
    }

    /**
     * 删除角色
     * @param string $name
     * @return Response
     */
    public function actionDelete($name)
    {
        try {
            $role = $this->auth->getRole(trim($name));
            $this->auth->remove($role);
            $responseBody = [
                'success' => true,
                'data' => $role,
            ];
        } catch (Exception $exc) {
            $responseBody = [
                'success' => false,
                'error' => [
                    'message' => $exc->getMessage(),
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
        $permissions = array_values($this->auth->getPermissionsByRole($roleName));

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
        } catch (Exception $exc) {
            $responseBody = [
                'success' => false,
                'error' => ['message' => $exc->getMessage()],
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
            $this->auth->removeChild($this->auth->getRole($roleName), $this->auth->getPermission($permissionName));
            $responseBody = ['success' => true];
        } catch (Exception $exc) {
            $responseBody = [
                'success' => false,
                'error' => [
                    'message' => $exc->getMessage(),
                ]
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

    /**
     * 删除角色关联的所有权限
     * @param string $name
     * @return Response
     */
    public function actionRemoveChildren($name)
    {
        try {
            $role = $this->auth->getRole(trim($name));
            $result = $this->auth->removeChildren($role);
            $responseBody = [
                'success' => $result
            ];
            if (!$result) {
                $responseBody['error']['message'] = 'Unknown Error.';
            }
        } catch (Exception $exc) {
            $responseBody = [
                'success' => false,
                'error' => [
                    'message' => $exc->getMessage(),
                ]
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseBody,
        ]);
    }

}
