<?php

use yadjet\rbac\RbacAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
$asset = RbacAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <div class="container-fluid page-container">
            <?php $this->beginBody() ?>
            <div class="container content-container">
                <?= $content ?>
            </div>
            <div class="footer-fix"></div>
        </div>
        <footer class="footer">
            <div class="container">

            </div>
        </footer>
        <?php $this->endBody() ?>
        <script type="text/javascript">
            yadjet.rbac.urls = {
                assign: '<?= Url::toRoute(['users/assign']) ?>',
                revoke: '<?= Url::toRoute(['users/revoke']) ?>',
                users: {
                    list: '<?= Url::toRoute(['users/index']) ?>'
                },
                user: {
                    roles: '<?= Url::toRoute(['users/roles', 'id' => '_id']) ?>',
                    permissions: '<?= Url::toRoute(['users/permissions']) ?>'
                },
                roles: {
                    list: '<?= Url::toRoute(['roles/index']) ?>',
                    create: '<?= Url::toRoute(['roles/create']) ?>',
                    'delete': '<?= Url::toRoute(['roles/delete', 'name' => '_name']) ?>',
                    permissions: '<?= Url::toRoute(['roles/permissions-by-role', 'roleName' => '_roleName']) ?>',
                    addChild: '<?= Url::toRoute(['roles/add-child', 'roleName' => '_roleName', 'permissionName' => '_permissionName']) ?>',
                    removeChild: '<?= Url::toRoute(['roles/remove-child', 'roleName' => '_roleName', 'permissionName' => '_permissionName']) ?>',
                    removeChildren: '<?= Url::toRoute(['roles/remove-children', 'name' => '_name']) ?>'
                },
                permissions: {
                    list: '<?= Url::toRoute(['permissions/index']) ?>',
                    create: '<?= Url::toRoute(['permissions/create']) ?>',
                    'delete': '<?= Url::toRoute(['permissions/delete', 'name' => '_name']) ?>',
                   scan: '<?= Url::toRoute(['default/scan']) ?>'
                }
            };
//            Vue.http.get(yadjet.rbac.urls.auths).then((res) => {
//            console.info(res.data);
//                if (res.data) {
//                vm.ownAuth.userId = res.data.userId;
//                vm.ownAuth.roles = res.data.roles;
//                vm.ownAuth.permissions = res.data.permissions;
//                }
//            });
            // 获取用户数据
            Vue.http.get(yadjet.rbac.urls.users.list).then((res) => {
                vm.users.items = res.data.items;
                vm.users.extras = res.data.extras;
            });
            Vue.http.get(yadjet.rbac.urls.roles.list).then((res) => {
                vm.roles = res.data;
            });
            Vue.http.get(yadjet.rbac.urls.permissions.list).then((res) => {
                vm.permissions = res.data;
            });
            Vue.http.get(yadjet.rbac.urls.permissions.scan).then((res) => {
                vm.pendingPermissions = res.data;
            });
        </script>
    </body>
</html>
<?php $this->endPage() ?>