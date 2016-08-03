<div id="rbac-app">
    <div class="rbac-tabs-common">
        <ul>
            <li class="active"><a data-toggle="rbac-users" href="<?= \yii\helpers\Url::toRoute('users') ?>">Users</a></li>
            <li><a data-toggle="rbac-roles" href="<?= \yii\helpers\Url::toRoute('roles') ?>">Roles</a></li>
            <li><a data-toggle="rbac-permissions" href="<?= \yii\helpers\Url::toRoute('permissions') ?>">Permissions</a>
            </li>
            <li><a data-toggle="rbac-pending-permissions" href="<?= \yii\helpers\Url::toRoute('default/scan') ?>">Scan Permissions</a></li>
        </ul>
    </div>

    <div id="rbac-panels" class="rbac-grid-view">

        <div id="rbac-users" class="panel">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>姓名</th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in users" v-bind:class="{'selected': item.id == activeObject.userId}">
                        <td class="serial-number">{{ item.id }}</td>
                        <td>{{ item.username }}</td>
                        <td class="btn-1">
                            <button v-on:click="userRolesByUserId(item.id)">Roles</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="rbac-pop-window" v-show="activeObject.userId">
                <table class="table">
                    <thead>
                        <tr>
                            <th>角色</th>
                            <th>描述</th>
                            <th>规则</th>
                            <th>数据</th>
                            <th class="actions last"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in userRoles">
                            <td>{{ item.name }}</td>
                            <td>{{ item.description }}</td>
                            <td>{{ item.rule_name }}</td>
                            <td>{{ item.data }}</td>
                            <td class="btn-1">
                                <button v-show="!item.active" v-on:click="assign(item.name, $index)">+</button>
                                <button v-show="item.active" v-on:click="revoke(item.name, $index)">X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="rbac-roles" class="panel" style="display: none;">

            <button @click="toggleFormVisible('role')">{{ formVisible.role ? 'Hide Form' : 'Show Form' }}</button>

            <div id="rbac-role-form" v-show="formVisible.role">
                <form action="<?= \yii\helpers\Url::toRoute(['roles/create']) ?>">
                    <p>
                        Name: <input type="text" id="name" name="name" value="" placeholder="Role name"/>
                        Description: <input type="text" id="description" name="description" value="" placeholder="Description"/>
                        <input id="rbac-sumbit-role" type="submit" value="Save"/>
                    </p>

                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>角色</th>
                        <th>描述</th>
                        <th>规则</th>
                        <th>数据</th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in roles" v-bind:class="{'selected': item.name == activeObject.role}">
                        <td>{{ item.name }}</td>
                        <td>{{ item.description }}</td>
                        <td>{{ item.rule_name }}</td>
                        <td>{{ item.data }}</td>
                        <td class="btn-3">
                            <button data-confirm="删除该角色？" v-on:click="roleDelete(item.name, $index, $event)">X</button>
                            <button data-confirm="删除该角色关联的所有权限？" v-on:click="roleRemoveChildren(item.name)">Remove Children</button>
                            <button v-on:click="permissionsByRole(item.name, $index)">Permissions</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="rbac-permissions-by-role" v-show="activeObject.role">
                <table class="table">
                    <thead>
                        <tr>
                            <th>权限</th>
                            <th>描述</th>
                            <th>规则</th>
                            <th>数据</th>
                            <th class="actions last"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in rolePermissions">
                            <td>{{ item.name }}</td>
                            <td>{{ item.description }}</td>
                            <td>{{ item.rule_name }}</td>
                            <td>{{ item.data }}</td>
                            <td class="btn-1">
                                <button v-show="!item.active" v-on:click="roleAddChild(item.name, $index, $event)">+</button>
                                <button v-show="item.active" data-confirm="删除该权限？" v-on:click="roleRemoveChild(item.name, $index, $event)">X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>


        <div id="rbac-permissions" class="panel" style="display: none;">

            <button @click="toggleFormVisible('permission')">{{ formVisible.permission ? 'Hide Form' : 'Show Form' }}</button>

            <div id="rbac-persmission-form" v-show="formVisible.permission">
                <form action="<?= \yii\helpers\Url::toRoute(['permission/create']) ?>">
                    <p>
                        Name: <input type="text" id="name" name="name" value="" placeholder="Permission name"/>                  
                        Description: <input type="text" id="description" name="description" value="" placeholder="Description"/>
                        <input id="rbac-sumbit-permission" type="submit" value="Save"/>
                    </p>
                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>权限</th>
                        <th>描述</th>
                        <th>规则</th>
                        <th>数据</th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in permissions">
                        <td>{{ item.name }}</td>
                        <td>{{ item.description }}</td>
                        <td>{{ item.rule_name }}</td>
                        <td>{{ item.data }}</td>
                        <td class="btn-1">
                            <button data-confirm="删除该权限？" v-on:click="permissionDelete(item.name, $index, $event)">X</button>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div id="rbac-pending-permissions" class="panel" style="display: none;">
            <table class="table">
                <thead>
                    <tr>
                        <th>action</th>
                        <th>description</th>
                        <th class="actions last"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pendingPermissions" v-bind:class="{ 'disabled': !item.active, 'enabled': item.active }">
                        <td>{{ item.name }}</td>
                        <td><input type="text" name="description" :disabled="!item.active" :value="item.description" placeholder="请填写该权限的描述内容" v-model="item.description"/></td>
                        <td class="btn-1">
                            <button :disabled="!item.active" @click="permissionSave(item.name, item.description, $index, $event)">Save</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</div>