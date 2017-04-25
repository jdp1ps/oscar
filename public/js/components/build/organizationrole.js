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
                                               * Created by jacksay on 17-01-30.
                                               */

    _vue2.default.http.options.emulateJSON = true;
    _vue2.default.http.options.emulateHTTP = true;

    var OrganizationRole = _vue2.default.extend({
        template: '<section>\n    <div class="vue-loader" v-if="loading">\n        <span>{{ loadingMsg }}</span>\n    </div>\n        <transition name="popup">\n            <div class="form-wrapper" v-if="form">\n                <form action="" @submit.prevent="save" class="container oscar-form">\n                    <header>\n                        <h1>\n                            <span v-if="form.id">Modification de <strong>{{ form.label }}</strong></span>\n                            <span v-else>Nouveau r\xF4le</span>\n                        </h1>\n                        <a href="#" @click="form=null" class="closer">\n                            <i class="glyphicon glyphicon-remove"></i>\n                        </a>\n                    </header>\n\n                    <div class="form-group">\n                      <label>Nom du r\xF4le</label>\n                      <input id="role_roleid" type="text" class="form-control" placeholder="Role" v-model="form.label" name="label"/>\n                    </div>\n                    <div class="form-group">\n                      <label>Principal</label>\n                      <p class="help">\n                        Un r\xF4le d\xE9finit comme principal sera trait\xE9 sp\xE9cifiquement dans l\'interface\n                      </p>\n                      <input type="checkbox" class="form-control" v-model="form.principal"/>\n                    </div>\n\n                    <div class="form-group">\n                      <label>Description</label>\n                      <textarea class="form-control" v-model="form.description"></textarea>\n                    </div>\n\n                    <footer class="buttons-bar">\n                        <div class="btn-group">\n                            <button type="submit" class="btn btn-primary">\n                                <i class="icon-floppy"></i>\n                                Enregistrer\n                            </button>\n                            <button type="button" class="btn btn-default" @click="form=null">\n                                <i class="icon-block"></i>\n                                Annuler\n                            </button>\n                        </div>\n                    </footer>\n                </form>\n            </div>\n        </transition>\n        <article v-for="role in roles" class="card xs">\n            <h1 class="card-title">\n                <span>\n                    <i v-if="role.principal" class="icon-asterisk"></i>\n                    {{ role.label }}\n                </span>\n            </h1>\n            <p>{{ role.description }}</p>\n            <nav class="card-footer" v-if="manage">\n                <button class="btn btn-xs btn-primary" @click="form=JSON.parse(JSON.stringify(role))">\n                    <i class="icon-pencil"></i>\n                    \xC9diter\n                </button>\n                <button class="btn btn-xs btn-default" @click="remove(role)">\n                    <i class="icon-trash"></i>\n                    Supprimer\n                </button>\n            </nav>\n        </article>\n        <button @click="formNew" class="btn btn-primary" v-if="manage">\n          <i class="icon-circled-plus"></i>\n           Ajouter un nouveau r\xF4le\n        </button>\n    </section>',
        data: function data() {
            return {
                roles: [],
                loadingMsg: null,
                form: null,
                manage: false
            };
        },

        computed: {
            loading: function loading() {
                return this.loadingMsg != null;
            }
        },
        methods: {
            formNew: function formNew() {
                this.form = {
                    label: "",
                    description: "",
                    principal: false
                };
            },
            save: function save() {
                var _this = this;

                if (this.form.id) {
                    this.loadingMsg = "Mise à jour du rôle...";
                    this.$http.put(this.form.id + "", this.form).then(function (res) {
                        console.log(res.body, _this.form);
                        for (var i = 0; i < _this.roles.length; i++) {
                            if (_this.roles[i].id == _this.form.id) {
                                _this.roles.splice(i, 1, res.body);
                                flashMessage('success', 'le rôle a bien été mis à jour.');
                            }
                        }
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this.loadingMsg = null;_this.form = null;
                    });
                } else {
                    this.loadingMsg = "Ajout du nouveau rôle...";
                    this.$http.post('', this.form).then(function (res) {
                        _this.roles.push(res.body);
                        flashMessage('success', 'le rôle a bien été ajouté.');
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this.loadingMsg = null;_this.form = null;
                    });
                }
            },
            remove: function remove(role) {
                var _this2 = this;

                _bootbox2.default.confirm('Êtes-vous bien sûr de votre coup ?', function (res) {
                    if (!res) return;
                    _this2.loadingMsg = "Suppression du rôle...";
                    _this2.$http.delete(role.id + '', _this2.form).then(function (res) {
                        _this2.roles.splice(_this2.roles.indexOf(role), 1);
                        flashMessage('success', 'le rôle a bien été supprimé.');
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this2.loadingMsg = null;_this2.form = null;
                    });
                });
            },
            fetch: function fetch() {
                var _this3 = this;

                this.loadingMsg = "Chargement des rôles";
                this.$http.get().then(function (res) {
                    _this3.roles = res.body;
                }, function (err) {
                    flashMessage('error', err.body);
                }).then(function () {
                    return _this3.loadingMsg = null;
                });
            }
        },
        created: function created() {
            this.fetch();
        }
    });

    exports.default = OrganizationRole;
});