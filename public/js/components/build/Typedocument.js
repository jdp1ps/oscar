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

    _vue2.default.use(_vueResource2.default);

    _vue2.default.http.options.emulateJSON = true;
    _vue2.default.http.options.emulateHTTP = true;

    var Typedocument = _vue2.default.extend({
        template: '\n        <section>\n            <div class="vue-loader" v-if="loading">\n                <span> {{ loadingMsg }}</span>\n            </div> \n            <transition name="popup">\n                <div class="form-wrapper" v-if="form">\n                    <form action="" @submit.prevent="save" class="container oscar-form">\n                        <header>\n                            <h1>\n                                <span v-if=\'form-id\'>Modification de <strong>{{ form.label }}</strong></span>\n                                <span v-else>Nouveau type de documents</span> \n                            </h1>\n                        </header>\n                        <div class="form-group">\n                            <label>Nom du type de document</label>\n                            <input id=\'typedoc_label\' type="text" class="form-control" v-model="form.label" name="label"/>                  \n                        </div>\n                        <footer class="buttons-bar">\n                            <div class="btn-group">\n                                <button type="submit" class="btn btn-primary">\n                                    <i class="icon-floppy"></i>\n                                    Enregistrer\n                                </button>\n                                <button type="submit" class="btn btn-default" @click="form=null">\n                                    <i class="icon-floppy"></i>\n                                    Annuler\n                                </button>\n                            </div>\n                        </footer>\n                    </form>\n                </div>\n            </transition> \n            <!-- Vue principale pour les types de documents -->\n            <article v-for="typedoc in types" class="card xs">\n                <h1 class="card-title">\n                    <span>\n                        {{ typedoc.label }}\n                    </span>\n                </h1>\n                <nav class="card-footer" v-if="manage">\n                    <button class="btn btn-xs btn-primary" @click="form=JSON.parse(JSON.stringify(typedoc))">\n                        <i class="icon-pencil"></i>\n                    \xC9diter\n                    </button>\n                    <button class="btn btn-xs btn-default" @click="remove(typedoc)">\n                        <i class="icon-trash"></i>\n                    Supprimer\n                    </button>\n                </nav>\n            </article>\n            <button @click="formNew" class="btn btn-primary" v-if="manage">\n            <i class="icon-circled-plus"></i>\n                Ajouter \n            </button>\n        </section>      \n    ',
        data: function data() {
            return {
                types: [],
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
                    description: ""
                };
            },
            save: function save() {
                var _this = this;

                if (this.form.id) {
                    this.loadingMsg = "Mise à jour du type de document...";
                    this.$http.put(this.form.id + "", this.form).then(function (res) {
                        console.log(res.body, _this.form);
                        for (var i = 0; i < _this.types.length; i++) {
                            if (_this.types[i].id == _this.form.id) {
                                _this.types.splice(i, 1, res.body);
                                flashMessage('success', 'le type de document a bien été mis à jour.');
                            }
                        }
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this.loadingMsg = null;_this.form = null;
                    });
                } else {
                    this.loadingMsg = "Ajout d'un nouveau type de document...";
                    this.$http.post('', this.form).then(function (res) {
                        _this.types.push(res.body);
                        flashMessage('success', 'le type de document a bien été ajouté.');
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this.loadingMsg = null;_this.form = null;
                    });
                }
            },
            remove: function remove(typedoc) {
                var _this2 = this;

                _bootbox2.default.confirm("Êtes-vous sûr de supprimer : " + typedoc.label + "?", function (res) {
                    if (!res) return;
                    _this2.loadingMsg = "Suppression du type de document...";
                    _this2.$http.delete(typedoc.id + '', _this2.form).then(function (res) {
                        _this2.types.splice(_this2.types.indexOf(typedoc), 1);
                        flashMessage('success', 'le type de document a bien été supprimé.');
                    }, function (err) {
                        flashMessage('error', err.body);
                    }).then(function () {
                        _this2.loadingMsg = null, _this2.form = null;
                    });
                });
            },
            fetch: function fetch() {
                var _this3 = this;

                this.loadingMsg = "Chargement des types de documents";
                this.$http.get().then(function (res) {
                    _this3.types = res.body;
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

    exports.default = Typedocument;
});