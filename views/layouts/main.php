<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
$asset = yadjet\rbac\RbacAsset::register($this);
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
                auths: '<?= \yii\helpers\Url::toRoute(['users/auths', 'id' => 0]) ?>',
                assign: '<?= \yii\helpers\Url::toRoute(['users/assign']) ?>',
                revoke: '<?= \yii\helpers\Url::toRoute(['users/revoke']) ?>',
                users: {
                    list: '<?= \yii\helpers\Url::toRoute(['users/index']) ?>'
                },
                user: {
                    roles: '<?= \yii\helpers\Url::toRoute(['users/roles', 'id' => 0]) ?>',
                    permissions: '<?= \yii\helpers\Url::toRoute(['users/permissions']) ?>'
                },
                roles: {
                    list: '<?= \yii\helpers\Url::toRoute(['roles/index']) ?>',
                    create: '<?= \yii\helpers\Url::toRoute(['roles/create']) ?>'
                },
                role: {
                    permissionsByRole: '<?= \yii\helpers\Url::toRoute(['roles/permissions-by-role', 'roleName' => 0]) ?>',
                    addChild: '<?= \yii\helpers\Url::toRoute(['roles/add-child']) ?>',
                },
                permissions: {
                    list: '<?= \yii\helpers\Url::toRoute(['permissions/index']) ?>',
                    create: '<?= \yii\helpers\Url::toRoute(['permissions/create']) ?>'
                },
                scanController: '<?= \yii\helpers\Url::toRoute(['default/scan']) ?>'
            };
            Vue.http.get(yadjet.rbac.urls.auths).then((res) => {
                vm.ownAuth.userId = res.data.userId;
                vm.ownAuth.roles = res.data.roles;
                vm.ownAuth.permissions = res.data.permissions;
            });
            Vue.http.get(yadjet.rbac.urls.users.list).then((res) => {
                vm.users = res.data;
            });
            Vue.http.get(yadjet.rbac.urls.roles.list).then((res) => {
                vm.roles = res.data;
            });
            Vue.http.get(yadjet.rbac.urls.permissions.list).then((res) => {
                vm.permissions = res.data;
            });
            Vue.http.get(yadjet.rbac.urls.scanController).then((res) => {
                vm.actions = res.data;
            });
        </script>
    </body>
</html>
<?php $this->endPage() ?>