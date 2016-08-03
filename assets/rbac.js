var yadjet = yadjet || {};
yadjet.utils = yadjet.utils || {
    clone: function (myObj) {
        if (typeof(myObj) != 'object' || myObj == null) return myObj;
        var newObj = new Object();
        for (var i in myObj) {
            newObj[i] = clone(myObj[i]);
        }

        return newObj;
    }
};
/**
 * Check object is empty
 * @param e
 * @returns {boolean}
 */
function isEmptyObject(e) {
    var t;
    for (t in e)
        return !1;
    return !0
}
/**
 * Clone Object
 * @param myObj
 * @returns {*}
 */
function clone(myObj) {
    if (typeof(myObj) != 'object' || myObj == null) return myObj;
    var newObj = new Object();
    for (var i in myObj) {
        newObj[i] = clone(myObj[i]);
    }

    return newObj;
}

yadjet.rbac = yadjet.rbac || {};
yadjet.rbac.debug = yadjet.rbac.debug || true;
yadjet.rbac.urls = yadjet.rbac.urls || {
        auths: undefined,
        assign: undefined,
        revoke: undefined,
        users: {
            list: undefined
        },
        user: {
            roles: undefined,
            permissions: undefined
        },
        role: {
            permissionsByRole: undefined,
            addChild: undefined
        },
        roles: {
            list: undefined,
            create: undefined,
            read: undefined,
            update: undefined,
            delete: undefined
        },
        permissions: {
            create: undefined,
            read: undefined,
            update: undefined,
            delete: undefined
        },
        scanController: undefined
    };

var vm = new Vue({
    el: '#rbac-app',
    data: {
        activeObject: {
            userId: 0,
            role: undefined
        },
        ownAuth: {
            userId: 0,
            roles: {},
            permissions: {}
        },
        users: {},
        user: {
            roles: {},
            permissions: {}
        },
        roles: [],
        role: {
            permissions: {}
        },
        permissions: {
            count: 0,
            items: {}
        },
        actions: {},
        formVisible: {
            role: false,
            permission: false
        }
    },
    methods: {
        isEmptyObject: function (e) {
            var t;
            for (t in e)
                return !1;
            return !0
        },
        userRolesByUserId: function (userId) {
            Vue.http.get(yadjet.rbac.urls.user.roles.replace('0', userId)).then((res) => {
                this.user.roles = res.data;
                this.activeObject.userId = userId;
            });
        },
        // 根据角色获取关联的所有权限
        permissionsByRole: function (roleName, index) {
            Vue.http.get(yadjet.rbac.urls.role.permissionsByRole.replace(0, roleName)).then((res) => {
                this.activeObject.role = roleName;
                this.role.permissions = res.data;
            });
        },
        userOwnRole: function (userId) {
            Vue.http.get(yadjet.rbac.urls.auths.replace('0', userId)).then((res) => {
                vm.ownAuth.userId = res.data.userId;
                vm.ownAuth.roles = res.data.roles;
                vm.ownAuth.permissions = res.data.permissions;
            });
        },
        // 给用户授权
        assign: function (roleName, index) {
            Vue.http.post(yadjet.rbac.urls.assign, {roleName: roleName, userId: vm.activeObject.userId}).then((res) => {
                console.info(index);
                this.user.roles.push(this.roles[index]);
            });
        },
        // 撤销用户授权
        revoke: function (roleName, index) {
            Vue.http.post(yadjet.rbac.urls.revoke, { roleName: roleName, userId: vm.activeObject.userId }).then((res) => {
                for (var i in this.user.roles) {
                    console.info(this.user.roles[i].name);
                    if (this.user.roles[i].name === roleName) {
                        this.user.roles.splice(i, 1);
                        break;
                    }
                }
            });
        },
        // 删除角色
        deleteRole: function (url, index, event) {
            Vue.http.post(url).then((res) => {
                this.roles.splice(index, 1);
            });
            event.preventDefault();
        },
        // 分配权限给角色
        addChild: function (permissionName, index, event) {
            Vue.http.post(yadjet.rbac.urls.role.addChild, {roleName: this.activeObject.role, permissionName: permissionName}).then((res) => {
                //vm.$set(this.role.permissions[permissionName], this.permissions[permissionName]);
                console.info(this.rolePermissions[permissionName]);
                this.rolePermissions[permissionName].active = true;
                console.info(index);
                console.info(permissionName);
            });
        },
        // 切换添加表单是否可见
        toggleFormVisible: function (formName) {
            this.formVisible[formName] = !this.formVisible[formName];
        },
        savePermission: function (name, description, index, event) {
            Vue.http.post(yadjet.rbac.urls.permissions.create, {name: name, description: description}).then((res) => {
                if (res.data.success) {
                    this.permissions.push(res.data.data);
                    this.actions[index].active = false;
                }
            });
        },
        deletePermission: function (url, index, event) {
            Vue.http.post(url).then((res) => {
                this.permissions.splice(index, 1);
            });
            event.preventDefault();
        }
    },
    computed: {
        // 当前用户的角色
        userRoles: function () {
            var roles = [], role;
            for (var i in this.roles) {
                role = yadjet.utils.clone(this.roles[i]);
                role.active = false;
                for (var j in this.user.roles) {
                    if (role.name == this.user.roles[j].name) {
                        role.active = true;
                        break;
                    }
                }
                roles.push(role);
            }

            return roles;
        },
        rolePermissions: function () {
            // if (isEmptyObject(this.role.permissions)) {
            //     return {};
            // }
            var permissions = [], permission;
            for (var i in this.permissions) {
                permission = yadjet.utils.clone(this.permissions[i]);
                permission.active = false;
                for (var j in this.role.permissions) {
                    if (permission.active) {
                        break;
                    }
                    if (permission.name == j) {
                        permission.active = true;
                    }
                }

                permissions.push(permission);
            }

            return permissions;
        },
        userPermissionByRole: function () {
            var permissions = [], permission;
            for (var i in this.permissions) {
                permission = this.permissions.slice(i, 1);
                var isChild = false;
                for (var j in this.ownAuth.permissions) {
                    if (isChild) {
                        break;
                    }
                    if (this.permissions[i].name == this.ownAuth.permissions[j].name) {
                        isChild = true;
                    }
                }
                permission.isChild = isChild;
                permissions.push(permission);
            }

            return permissions;
        },
    }
});

//Vue.http.options.root = '/root';
//Vue.http.headers.common['Authorization'] = 'Basic YXBpOnBhc3N3b3Jk';
$(function () {
    $('.rbac-tabs-common li a').on('click', function () {
        var $t = $(this);
        $t.parent().addClass('active').siblings().removeClass('active');
        $('#rbac-app .panel').hide();
        $('#rbac-app #' + $t.attr('data-toggle')).show();

        return false;
    });

    $('#rbac-sumbit-role').on('click', function () {
        $.ajax({
            type: 'POST',
            url: yadjet.rbac.urls.roles.create,
            data: $('#rbac-role-form form').serialize(),
            returnType: 'json',
            success: function (response) {
                if (response.success) {
                    // vm.roles[response.data.name] = response.data;
                    vm.roles.push(response.data);
                } else {
                    alert(response.error.message);
                }
            }
        });

        return false;
    });

    $('#rbac-sumbit-permission').on('click', function () {
        $.ajax({
            type: 'POST',
            url: yadjet.rbac.urls.permissions.create,
            data: $('#rbac-persmission-form form').serialize(),
            returnType: 'json',
            success: function (response) {
                if (response.success) {
                    vm.permissions.push(response.data);
                } else {
                    alert(response.error.message);
                }
            }
        });

        return false;
    });
});