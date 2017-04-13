define(['exports', 'vue', 'vue-resource', 'bootbox'], function (exports, _vue, _vueResource, _bootbox) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    var _vueResource2 = _interopRequireDefault(_vueResource);

    var _bootbox2 = _interopRequireDefault(_bootbox);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    _vue2.default.use(_vueResource2.default); /**
                                               * Created by jacksay on 17-01-20.
                                               */


    _vue2.default.http.options.emulateJSON = true;
    _vue2.default.http.options.emulateHTTP = true;

    var Roles = _vue2.default.extend({
        template: '<section>\n        <transition name="popup">\n            <div class="form-wrapper" v-if="form" :class="{ loading: loading }">\n\n                <form @submit.prevent="save" class="container oscar-form">\n                    <div class="loadingMsg">\n                        <span>Chargement</span>\n                    </div>\n                    <header>\n                        <h1>\n                            <span v-if="form.id">Modification de <strong>{{ form.roleId }}</strong></span>\n                            <span v-else>Nouveau r\xF4le</span>\n                        </h1>\n                        <a href="#" @click="form=null" class="closer">\n                            <i class="glyphicon glyphicon-remove"></i>\n                        </a>\n                    </header>\n\n                    <div class="form-group">\n                      <label>Nom du r\xF4le (RoleId)</label>\n                      <input id="role_roleid" type="text" class="form-control" placeholder="Role" v-model="form.roleId" name="roleid"/>\n                    </div>\n                    <div class="form-group">\n                      <label>Principal</label>\n                      <p class="help">\n                        Un r\xF4le d\xE9finit comme principal sera trait\xE9 sp\xE9cifiquement dans l\'interface\n                      </p>\n                      <input type="checkbox" class="form-control" v-model="form.principal"/>\n                    </div>\n                    <div class="form-group">\n                      <label>Port\xE9e (spot)</label>\n                      <p class="help">\n                        La port\xE9e permet de d\xE9finir le p\xE9rim\xE8tre d\'affectation d\'un r\xF4le.\n                        Un r\xF4le fix\xE9 au <strong>niveau application</strong> donne les droits de ce r\xF4le sur l\'application enti\xE8re.\n                        Un r\xF4le d\xE9finit au <strong>niveau organisation</strong> donne les droits de ce r\xF4le sur les activit\xE9s de cette organisation.\n                        Un r\xF4le d\xE9finit au <strong>niveau activit\xE9</strong> ne donnera que les droits du r\xF4le sur les projets/activit\xE9s.\n                      </p>\n                      <div class="spots">\n                            <span :class="{active: ((form.spot & 4) > 0)}" data-spot="4" @click="updateRoleSpotForm(4)">\n                                <i class="icon-ok-circled"></i><i class="icon-minus-circled"></i>Application\n                            </span>\n                            <span :class="{active: ((form.spot & 2) > 0)}" data-spot="2" @click="updateRoleSpotForm(2)">\n                                <i class="icon-ok-circled"></i><i class="icon-minus-circled"></i>Organisation\n                            </span>\n                            <span :class="{active: ((form.spot & 1) > 0)}" data-spot="1" @click="updateRoleSpotForm(1)">\n                                <i class="icon-ok-circled"></i><i class="icon-minus-circled"></i>Activit\xE9\n                            </span>\n                        </div>\n                      <input id="role_spot" type="hidden" v-model="form.spot" name="spot"/>\n                    </div>\n                    <div class="form-group">\n                      <label>Description</label>\n                      <textarea class="form-control" v-model="form.description"></textarea>\n                    </div>\n                    <div class="form-group">\n                      <label>Filtre LDAP</label>\n                      <p class="help">\n                        L\'utilisation d\'un filtre LDap affectera le r\xF4le automatiquement sur l\'application enti\xE8re, peut importe le r\xE9glage de la port\xE9e.\n                      </p>\n                      <textarea class="form-control" v-model="form.ldapFilter"></textarea>\n                    </div>\n\n                    <footer class="buttons-bar">\n                        <div class="btn-group">\n                            <button type="submit" class="btn btn-primary">\n                                <i class="icon-floppy"></i>\n                                Enregistrer\n                            </button>\n                            <button type="button" class="btn btn-default" @click="form=null">\n                                <i class="icon-block"></i>\n                                Annuler\n                            </button>\n                        </div>\n                    </footer>\n                </form>\n            </div>\n        </transition>\n\n        <nav class="oscar-sorter">\n            <i class="icon-sort"></i>\n            Filtres :\n               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 4) > 0 }" @click="toggleFilter(4)">Application</a>\n               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 2) > 0 }" @click="toggleFilter(2)">Organization</a>\n               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 1) > 0 }" @click="toggleFilter(1)">Projet/Activit\xE9</a>\n        </nav>\n\n        <article v-for="role in roles" class="card role-list-item" :class="{\'selected\': common.roleSelected == role}" @mouseenter="handleHover(role)" @mouseleave="handleOut()" @click="handlerClick(role)">\n            <div class="header role-header">\n                <strong>\n                    <i v-if="role.principal" class="icon-certificate"></i>\n                    {{ role.roleId }}\n                </strong>\n\n                <nav class="nav-icons">\n                    <a href="#" @click.prevent="remove(role)" v-if="role.deletable">\n                        <i class="icon-trash"></i>\n                    </a>\n                    <a href="#" @click.prevent="formEdit(role)" v-if="role.editable">\n                        <i class="icon-pencil"></i>\n                    </a>\n                </nav>\n            </div>\n\n            <p class="oscar-help" v-if="role.description">{{ role.description }}</p>\n\n            <template v-if="role.ldapFilter">\n            <p class="oscar-help">Les filtres LDAP s\'appliquent sur l\'application enti\xE8re</p>\n            <pre class="filtreLdap"><strong>Filtre LDAP</strong>:{{role.ldapFilter}}</pre>\n            </template>\n        </article>\n\n        <nav class="buttons-bar">\n            <a href="#" class="handler-add btn btn-primary" @click.prevent="newRoleForm">\n                <i class="icon-plus-circled"></i> Ajouter un r\xF4le\n            </a>\n        </div>\n        </section>',

        data: function data() {
            return {
                // Modèle
                common: {
                    privileges: [],
                    roles: [],
                    roleHighLight: null,
                    roleSelected: null
                },

                // Données du formulaire
                form: null,

                // ---- FLAGS
                // Affichage de l'écran de chargement
                loading: false,

                activeSpots: 7,

                click: false
            };
        },


        computed: {
            roles: function roles() {
                var _this = this;

                var filteredRoles = [];
                this.common.roles.forEach(function (role) {
                    if ((role.spot & _this.activeSpots) > 0 || role.spot == 0) filteredRoles.push(role);
                });
                return filteredRoles;
            }
        },

        methods: {
            handleHover: function handleHover(role) {
                this.common.roleHighLight = role;
            },

            handlerClick: function handlerClick(role) {
                this.common.roleSelected = role == this.common.roleSelected ? null : role;
                this.common.roleHighLight = role;
            },

            handleOut: function handleOut() {
                this.common.roleHighLight = null;
            },

            toggleFilter: function toggleFilter(bit) {
                if ((this.activeSpots & bit) > 0) {
                    this.activeSpots -= bit;
                } else {
                    this.activeSpots += bit;
                }
            },
            formEdit: function formEdit(role) {
                this.form = JSON.parse(JSON.stringify(role));
            },
            remove: function remove(role) {
                var _this2 = this;

                _bootbox2.default.confirm({
                    title: 'Supprimer définitivement le rôle ?',
                    message: 'Si vous souhaitez désactiver le rôle, décochez la portée dans le forumaire.',
                    callback: function callback(response) {
                        if (!response) return;
                        _this2.loading = true;
                        _this2.$http.delete(_this2.$http.$options.root + '/' + role.id).then(function (res) {
                            console.log(res, _this2.common.roles);
                            for (var i = 0; i < _this2.common.roles.length; i++) {
                                if (_this2.common.roles[i].id == role.id) {
                                    _this2.common.roles.splice(i, 1);
                                    flashMessage('success', "Le rôle a été supprimé");
                                    return;
                                }
                            }
                        }, function (err) {
                            flashMessage('error', err.body);
                        }).then(function () {
                            _this2.loading = false;
                        });
                    }
                });
            },

            save: function save(e) {
                var _this3 = this;

                this.loading = true;
                if (this.form.id) {
                    this.$http.put(this.$http.$options.root + '/' + this.form.id, this.form).then(function (res) {
                        for (var i = 0; i < _this3.common.roles.length; i++) {
                            if (_this3.common.roles[i].id == _this3.form.id) {
                                _this3.common.roles.splice(i, 1, res.body);
                                flashMessage('success', "Le rôle a été mis à jour");
                                return;
                            }
                        }
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this3.loading = false;
                    });;
                } else {
                    this.$http.post(this.$http.$options.root, this.form).then(function (res) {
                        _this3.common.roles.push(res.body);
                        flashMessage('success', "Le rôle a été ajouté");
                        return;
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this3.loading = false;
                    });;
                }
            },

            newRoleForm: function newRoleForm() {
                this.form = {
                    roleId: "",
                    spot: 0,
                    ldapFilter: null,
                    description: "",
                    principal: false
                };
            },


            cssSpot: function cssSpot(role) {
                return 'spot' + role.spot;
            },

            updateRoleSpotForm: function updateRoleSpotForm(spot) {
                if ((this.form.spot & spot) > 0) {
                    this.form.spot -= spot;
                } else {
                    this.form.spot += spot;
                }
            },


            updateRoleSpot: function updateRoleSpot(role, val) {
                var _this4 = this;

                this.loading = true;
                var oldVal = role.spot;
                if ((role.spot & val) > 0) {
                    oldVal -= val;
                } else {
                    oldVal += val;
                }

                var data = JSON.parse(JSON.stringify(role));
                data.spot = oldVal;

                this.$http.put(this.$http.$options.root + '/' + role.id, data).then(function (res) {
                    flashMessage("success", "Le rôle a bien été modifié");
                    role.spot = oldVal;
                }, function (err) {
                    flashMessage("error", "Impossible de modifier le rôle : " + err.body);
                }).then(function () {
                    _this4.loading = false;
                });;
            }
        }
    });

    exports.default = Roles;
});