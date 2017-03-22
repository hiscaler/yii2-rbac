var yadjet = yadjet || {};
yadjet.utils = yadjet.utils || {
    clone: function (myObj) {
        if (typeof (myObj) != 'object' || myObj == null)
            return myObj;
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
    if (typeof (myObj) != 'object' || myObj == null)
        return myObj;
    var newObj = new Object();
    for (var i in myObj) {
        newObj[i] = clone(myObj[i]);
    }

    return newObj;
}

yadjet.rbac = yadjet.rbac || {};
yadjet.rbac.debug = yadjet.rbac.debug || true;
yadjet.rbac.urls = yadjet.rbac.urls || {
    assign: undefined,
    revoke: undefined,
    users: {
        list: undefined
    },
    user: {
        roles: undefined,
        permissions: undefined
    },
    roles: {
        list: undefined, // 角色列表
        create: undefined, // 添加角色
        read: undefined, // 查看角色
        update: undefined, // 更新角色
        'delete': undefined, // 删除角色
        permissions: undefined, // 角色对应的权限
        addChild: undefined, // 角色关联权限操作
        removeChild: undefined, // 删除角色中的某个关联权限
        removeChildren: undefined, // 删除角色关联的所有权限
    },
    permissions: {
        create: undefined,
        read: undefined,
        update: undefined,
        'delete': undefined,
        scan: undefined
    }
};

var vm = new Vue({
    el: '#rbac-app',
    data: {
        activeObject: {
            userId: 0,
            role: undefined
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
        permissions: [],
        pendingPermissions: {},
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
        userRolesByUserId: function (userId, index) {
            Vue.http.get(yadjet.rbac.urls.user.roles.replace('_id', userId)).then((res) => {
                this.user.roles = res.data;
                this.activeObject.userId = userId;
                var $tr = $('#rbac-users > table tr:eq(' + (index + 1) + ')');
                var offset = $tr.offset();
                $('#rbac-pop-window').css({
                    position: 'absolute',
                    left: offset.left + 40,
                    top: offset.top + $tr.find('td').outerHeight()
                });
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
            Vue.http.post(yadjet.rbac.urls.revoke, {roleName: roleName, userId: vm.activeObject.userId}).then((res) => {
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
        roleDelete: function (roleName, index, event) {
            Vue.http.post(yadjet.rbac.urls.roles.delete.replace('_name', roleName)).then((res) => {
                this.roles.splice(index, 1);
            });
            event.preventDefault();
        },
        // 删除角色关联的所有权限
        roleRemoveChildren: function (roleName) {
            Vue.http.post(yadjet.rbac.urls.roles.removeChildren.replace('_name', roleName)).then((res) => {
                this.role.permissions = [];
            });
        },
        // 根据角色获取关联的所有权限
        permissionsByRole: function (roleName, index) {
            Vue.http.get(yadjet.rbac.urls.roles.permissions.replace('_roleName', roleName)).then((res) => {
                this.activeObject.role = roleName;
                this.role.permissions = res.data;
            });
        },
        // 分配权限给角色
        roleAddChild: function (permissionName, index, event) {
            Vue.http.post(yadjet.rbac.urls.roles.addChild.replace('_roleName', vm.activeObject.role).replace('_permissionName', permissionName)).then((res) => {
                for (var i in this.permissions) {
                    if (this.permissions[i].name == permissionName) {
                        this.role.permissions.push(this.permissions[i]);
                        break;
                    }
                }
            });
        },
        // 从角色中移除权限
        roleRemoveChild: function (permissionName, index, event) {
            Vue.http.post(yadjet.rbac.urls.roles.removeChild.replace('_roleName', vm.activeObject.role).replace('_permissionName', permissionName)).then((res) => {
                for (var i in this.role.permissions) {
                    if (this.role.permissions[i].name == permissionName) {
                        this.role.permissions.splice(i, 1);
                        break;
                    }
                }
            });
        },
        // 切换添加表单是否可见
        toggleFormVisible: function (formName) {
            this.formVisible[formName] = !this.formVisible[formName];
        },
        // 保存扫描的权限
        permissionSave: function (name, description, index, event) {
            Vue.http.post(yadjet.rbac.urls.permissions.create, {name: name, description: description}).then((res) => {
                if (res.data.success) {
                    this.permissions.push(res.data.data);
                    this.pendingPermissions[index].active = false;
                }
            });
        },
        // 删除单个权限
        permissionDelete: function (name, index, event) {
            Vue.http.post(yadjet.rbac.urls.permissions.delete.replace('_name', name)).then((res) => {
                this.permissions.splice(index, 1);
                for (var i in this.pendingPermissions) {
                    if (this.pendingPermissions[i].name == name) {
                        this.pendingPermissions[i].active = true;
                        break;
                    }
                }
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
        // 当前操作角色关联的权限
        rolePermissions: function () {
            var permissions = [], permission;
            for (var i in this.permissions) {
                permission = yadjet.utils.clone(this.permissions[i]);
                permission.active = false;
                for (var j in this.role.permissions) {
                    if (permission.name == this.role.permissions[j].name) {
                        permission.active = true;
                        break;
                    }
                }
                permissions.push(permission);
            }

            return permissions;
        }
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
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert('ERROR ' + XMLHttpRequest.status + ' 错误信息： ' + XMLHttpRequest.responseText);
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
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert('ERROR ' + XMLHttpRequest.status + ' 错误信息： ' + XMLHttpRequest.responseText);
            }
        });

        return false;
    });
});
