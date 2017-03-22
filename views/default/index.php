<div id="rbac-app">
    <div class="rbac-tabs-common">
        <ul>
            <li class="active"><a data-toggle="rbac-users" href="<?= \yii\helpers\Url::toRoute('users') ?>"><?= Yii::t('rbac', 'Users') ?></a></li>
            <li><a data-toggle="rbac-roles" href="<?= \yii\helpers\Url::toRoute('roles') ?>"><?= Yii::t('rbac', 'Roles') ?></a></li>
            <li><a data-toggle="rbac-permissions" href="<?= \yii\helpers\Url::toRoute('permissions') ?>"><?= Yii::t('rbac', 'Permissions') ?></a>
            </li>
            <li><a data-toggle="rbac-pending-permissions" href="<?= \yii\helpers\Url::toRoute('default/scan') ?>"><?= Yii::t('rbac', 'Permissions Scan') ?></a></li>
        </ul>
    </div>

    <div id="rbac-panels" class="rbac-grid-view">

        <div id="rbac-users" class="panel">
            <table class="table">
                <thead>
                    <tr class="clear-border-top">
                        <th class="serial-number">#</th>
                        <th><?= Yii::t('rbac', 'Username') ?></th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in users" v-bind:class="{'selected': item.id == activeObject.userId}">
                        <td class="serial-number">{{ item.id }}</td>
                        <td>{{ item.username }}</td>
                        <td class="btn-1">
                            <button class="button-rbac" v-on:click="userRolesByUserId(item.id, $index)"><?= Yii::t('rbac', 'Roles') ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="rbac-pop-window" v-show="activeObject.userId">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= Yii::t('rbac', 'Role Name') ?></th>
                            <th><?= Yii::t('rbac', 'Description') ?></th>
                            <th><?= Yii::t('rbac', 'Rule Name') ?></th>
                            <th><?= Yii::t('rbac', 'Role Data') ?></th>
                            <th class="actions last"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in userRoles">
                            <td class="role-name">{{ item.name }}</td>
                            <td>{{ item.description }}</td>
                            <td>{{ item.rule_name }}</td>
                            <td>{{ item.data }}</td>
                            <td class="btn-1">
                                <button class="button-rbac" v-show="!item.active" v-on:click="assign(item.name, $index)">+</button>
                                <button class="button-rbac" v-show="item.active" v-on:click="revoke(item.name, $index)">X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="rbac-roles" class="panel" style="display: none;">

            <fieldset>
                <legend>
                    <button class="button-rbac" @click="toggleFormVisible('role')">{{ formVisible.role ? '<?= Yii::t('rbac', 'Hide Form') ?>' : '<?= Yii::t('rbac', 'Show Form') ?>' }}</button>
                </legend>
                <div class="form-rbac" id="rbac-role-form" v-show="formVisible.role">
                    <form action="<?= \yii\helpers\Url::toRoute(['roles/create']) ?>">
                        <div class="row">
                            <label><?= Yii::t('rbac', 'Role Name') ?>:</label><input type="text" class="rbac-input" id="name" name="name" value=""/>
                        </div>
                        <div class="row">
                            <label><?= Yii::t('rbac', 'Description') ?>:</label><input type="text" class="rbac-input" id="description" name="description" value="" />
                        </div>
                        <div class="row last-row">
                            <input class="button-rbac" id="rbac-sumbit-role" type="submit" value="<?= Yii::t('rbac', 'Save') ?>"/>
                        </div>
                    </form>
                </div>
            </fieldset>

            <table class="table">
                <thead>
                    <tr>
                        <th><?= Yii::t('rbac', 'Role Name') ?></th>
                        <th><?= Yii::t('rbac', 'Description') ?></th>
                        <th><?= Yii::t('rbac', 'Rule Name') ?></th>
                        <th><?= Yii::t('rbac', 'Role Data') ?></th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in roles" v-bind:class="{'selected': item.name == activeObject.role}">
                        <td class="role-name">{{ item.name }}</td>
                        <td>{{ item.description }}</td>
                        <td>{{ item.rule_name }}</td>
                        <td>{{ item.data }}</td>
                        <td class="btn-3">
                            <button class="button-rbac" data-confirm="删除该角色？" v-on:click="roleDelete(item.name, $index, $event)">X</button>
                            <button class="button-rbac" data-confirm="删除该角色关联的所有权限？" v-on:click="roleRemoveChildren(item.name)"><?= Yii::t('rbac', 'Remove Children') ?></button>
                            <button class="button-rbac" v-on:click="permissionsByRole(item.name, $index)"><?= Yii::t('rbac', 'Permissions') ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="rbac-permissions-by-role" v-show="activeObject.role">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= Yii::t('rbac', 'Role Name') ?></th>
                            <th><?= Yii::t('rbac', 'Description') ?></th>
                            <th><?= Yii::t('rbac', 'Rule Name') ?></th>
                            <th><?= Yii::t('rbac', 'Role Data') ?></th>
                            <th class="actions last"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in rolePermissions">
                            <td class="role-name">{{ item.name }}</td>
                            <td>{{ item.description }}</td>
                            <td>{{ item.rule_name }}</td>
                            <td>{{ item.data }}</td>
                            <td class="btn-1">
                                <button class="button-rbac" v-show="!item.active" v-on:click="roleAddChild(item.name, $index, $event)">+</button>
                                <button class="button-rbac" v-show="item.active" data-confirm="删除该权限？" v-on:click="roleRemoveChild(item.name, $index, $event)">X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>


        <div id="rbac-permissions" class="panel" style="display: none;">

            <fieldset class="wrapper">
                <legend>
                    <button class="button-rbac" @click="toggleFormVisible('permission')">{{ formVisible.permission ? '<?= Yii::t('rbac', 'Hide Form') ?>' : '<?= Yii::t('rbac', 'Show Form') ?>' }}</button>
                </legend>

                <div id="rbac-persmission-form" v-show="formVisible.permission">
                    <form class="form-rbac" action="<?= \yii\helpers\Url::toRoute(['permission/create']) ?>">
                        <div class="row">
                            <label><?= Yii::t('rbac', 'Permission Name') ?>:</label><input type="text" class="rbac-input" id="name" name="name" value=""/>
                        </div>
                        <div class="row">
                            <label><?= Yii::t('rbac', 'Permission Description') ?>:</label><input type="text" class="rbac-input" id="description" name="description" value="" />
                        </div>
                        <div class="row last-row">
                            <input class="button-rbac" id="rbac-sumbit-permission" type="submit" value="<?= Yii::t('rbac', 'Save') ?>"/>
                        </div>
                    </form>
                </div>
            </fieldset>

            <table class="table">
                <thead>
                    <tr>
                        <th><?= Yii::t('rbac', 'Permission Name') ?></th>
                        <th><?= Yii::t('rbac', 'Permission Description') ?></th>
                        <th><?= Yii::t('rbac', 'Rule Name') ?></th>
                        <th><?= Yii::t('rbac', 'Permission Data') ?></th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in permissions">
                        <td class="permission-name">{{ item.name }}</td>
                        <td>{{ item.description }}</td>
                        <td>{{ item.rule_name }}</td>
                        <td>{{ item.data }}</td>
                        <td class="btn-1">
                            <button class="button-rbac" data-confirm="删除该权限？" v-on:click="permissionDelete(item.name, $index, $event)">X</button>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div id="rbac-pending-permissions" class="panel" style="display: none;">
            <table class="table">
                <thead>
                    <tr class="clear-border-top">
                        <th><?= Yii::t('rbac', 'Action') ?></th>
                        <th><?= Yii::t('rbac', 'Permission Description') ?></th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pendingPermissions" v-bind:class="{ 'disabled': !item.active, 'enabled': item.active }">
                        <td class="permission-name">{{ item.name }}</td>
                        <td><input type="text" name="description" :disabled="!item.active" :value="item.description" v-model="item.description"/></td>
                        <td class="btn-1">
                            <button class="button-rbac" :disabled="!item.active" @click="permissionSave(item.name, item.description, $index, $event)"><?= Yii::t('rbac', 'Save') ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</div>