define(['exports', 'vue', 'vue-resource', 'LocalDB', 'bootbox', 'moment-timezone'], function (exports, _vue, _vueResource, _LocalDB, _bootbox, _momentTimezone) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    var _vueResource2 = _interopRequireDefault(_vueResource);

    var _LocalDB2 = _interopRequireDefault(_LocalDB);

    var _bootbox2 = _interopRequireDefault(_bootbox);

    var _momentTimezone2 = _interopRequireDefault(_momentTimezone);

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

    var WorkpackagePerson = {
        template: '<article class="workpackage-person">\n                <div class="displayname">\n                    <strong>{{ person.person.displayname }}</strong>\n                </div>\n                <div class="tempsdeclare temps">\n                    <div v-if="editable && mode == \'edit\'">\n                        Heures pr\xE9vues :\n                        <input type="integer" v-model="durationForm" style="width: 5em" @keyup.13="handlerUpdate"/>\n                        <a href="#" @click.prevent="handlerUpdate" title="Appliquer la modification des heures pr\xE9vues"><i class="icon-floppy"></i></a>\n                        <a href="#" @click.prevent="handlerCancel" title="Annuler la modification des heures pr\xE9vues"><i class="icon-cancel-outline"></i></a>\n                    </div>\n                    <span v-else>\n                        <strong >{{person.hours}}/{{ person.duration }}</strong> heure(s)\n                    </span>\n                    <a href="#" @click.prevent="handlerEdit" v-if="editable && mode == \'read\'" title="Modifier les heures pr\xE9vues"><i class="icon-pencil"></i></a>\n                </div>\n                <a href="#" @click.prevent="handlerRemove(person)" class="link" v-if="editable && mode == \'read\'"><i class="icon-trash"></i> Retirer</a>\n            </article>',
        props: {
            'person': { default: function _default() {
                    return {};
                } },
            'editable': false
        },
        computed: {
            duration: function duration() {
                return this.person.duration;
            }
        },
        data: function data() {
            return {
                'canSave': false,
                'mode': 'read',
                'durationForm': 666
            };
        },

        methods: {
            handlerKeyUp: function handlerKeyUp() {
                console.log(arguments);
            },
            handlerUpdate: function handlerUpdate() {
                this.$emit('workpackagepersonupdate', this.person, this.durationForm);
                this.mode = 'read';
            },
            handlerEdit: function handlerEdit() {
                this.mode = 'edit';
                this.durationForm = this.person.duration;
            },
            handlerCancel: function handlerCancel() {
                this.mode = 'read';
                this.durationForm = this.person.duration;
            },
            handlerRemove: function handlerRemove() {
                this.$emit('workpackagepersondelete', this.person);
            }
        }
    };

    var Workpackage = {
        components: {
            workpackageperson: WorkpackagePerson
        },
        template: '<article class="workpackage">\n        <form action="" @submit.prevent="handlerUpdateWorkPackage" v-if="mode == \'edit\'">\n            <h4><span v-if="workpackage.id > 0">Modification du lot</span><span v-else>Nouveau lot</span> {{ formData.label }}</h4>\n            <div class="form-group">\n                <label for="">Intitul\xE9</label>\n                <input type="text" placeholder="Intitul\xE9" v-model="formData.label" class="form-control" />\n            </div>\n            <div class="form-group">\n                <label for="">Code</label>\n                <p class="help">Le code est utilis\xE9 pour l\'affichage des cr\xE9neaux</p>\n                <input type="text" placeholder="Intitul\xE9" v-model="formData.code" class="form-control" />\n            </div>\n            <div class="form-group">\n                <label for="">P\xE9riode</label>\n                <div class="row">\n                    <div class="col-md-6">\n                        du <input type="date" placeholder="D\xE9but" v-model="formData.start" class="form-control" />\n\n                    </div>\n                    <div class="col-md-6">\n                        du <input type="date" placeholder="Fin" v-model="formData.end" class="form-control" />\n                    </div>\n                </div>\n\n            </div>\n            <div class="form-group">\n                <label for="">Description</label>\n                <textarea type="text" placeholder="Description" v-model="formData.description" class="form-control"></textarea>\n            </div>\n            <div class="buttons">\n                <button type="submit" class="btn btn-default">Enregistrer</button>\n                <button type="button" class="btn btn-default" @click="handlerCancelEdit">Annuler</button>\n            </div>\n\n        </form>\n        <div v-if="mode == \'read\'">\n            <h3>[{{ workpackage.code }}] {{ workpackage.label }}</h3>\n            <small>Du\n                <strong v-if="!workpackage.start">d\xE9but de l\'activit\xE9</strong>\n                <strong v-else>{{ workpackage.start }}</strong>\n                au\n                <strong v-if="!workpackage.end">fin de l\'activit\xE9</strong>\n                <strong v-else>{{ workpackage.end }}</strong>\n            </small>\n            <p>{{ workpackage.description }}</p>\n\n            <section class="workpackage-persons">\n                <h4><i class="icon-calendar"></i>D\xE9clarants </h4>\n                <workpackageperson v-for="person in workpackage.persons"\n                    :person="person"\n                    :editable="editable"\n                    @workpackagepersondelete="handlerDelete"\n                    @workpackagepersonupdate="handlerUpdate"></workpackageperson>\n            </section>\n\n            <div class="buttons" v-if="editable">\n                <div class="btn-group">\n                    <button type="button" class="btn btn-default  btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\n                        Ajouter un d\xE9clarant <span class="caret"></span>\n                    </button>\n                    <ul class="dropdown-menu">\n                        <li v-for="person in persons"><a href="#" @click.prevent="$emit(\'addperson\', person.id, workpackage.id)">{{ person.displayname }}</a></li>\n                    </ul>\n                </div>\n                <a href="#" class="btn btn-default btn-xs" @click.prevent="handlerEditWorkPackage"><i class="icon-pencil"></i>Modifier</a>\n                <a href="#" class="btn btn-default btn-xs" @click.prevent="handlerDeleteWorkPackage"><i class="icon-trash"></i>Supprimer</a>\n            </div>\n        </div>\n    </article>',
        data: function data() {
            return {
                mode: "read",
                canSave: false,
                formData: {
                    id: -1,
                    code: "",
                    label: "",
                    description: "",
                    start: (0, _momentTimezone2.default)().format(),
                    end: (0, _momentTimezone2.default)().format()
                }
            };
        },
        created: function created() {
            console.log("created", this.workpackage.id);
            if (this.workpackage.id < 0) {
                this.mode = "edit";
            }
        },

        props: {
            'workpackage': null,
            'persons': { default: function _default() {
                    return [];
                } },
            'editable': false,
            'isValidateur': false
        },

        watch: {
            'person.duration': function personDuration() {
                console.log('Modification de la durée');
            }
        },

        methods: {
            handlerEditWorkPackage: function handlerEditWorkPackage() {
                this.formData = JSON.parse(JSON.stringify(this.workpackage));
                this.mode = 'edit';
            },
            handlerCancelEdit: function handlerCancelEdit() {
                if (this.workpackage.id < 0) {
                    this.$emit('workpackagecancelnew', this.workpackage);
                } else {
                    this.mode = 'read';
                }
            },
            handlerDeleteWorkPackage: function handlerDeleteWorkPackage() {
                var _this = this;

                _bootbox2.default.confirm("Souhaitez-vous supprimer ce lot ?", function (result) {
                    if (result) _this.$emit('workpackagedelete', _this.workpackage);
                });
            },
            handlerUpdateWorkPackage: function handlerUpdateWorkPackage() {
                this.$emit('workpackageupdate', this.formData);
                this.mode = 'read';
            },
            handlerUpdate: function handlerUpdate(person, duration) {
                this.$emit('workpackagepersonupdate', person, duration);
            },
            handlerDelete: function handlerDelete(person) {
                var _this2 = this;

                _bootbox2.default.confirm("Souhaitez-vous supprimer cette personne de la liste des déclarants ?", function (result) {
                    if (result) _this2.$emit('workpackagepersondelete', person);
                });
            },
            roles: function roles(person) {
                return person.roles.join(',');
            },
            tempsPrevu: function tempsPrevu(person) {
                return 0;
            },
            tempsDeclare: function tempsDeclare(person) {
                return 0;
            }
        }
    };

    var Workpackageperson = _vue2.default.extend({
        components: {
            'workpackage': Workpackage
        },
        template: '<section>\n        <transition name="fade">\n            <div class="vue-loader" v-if="errors.length">\n                <div class="alert alert-danger" v-for="error, i in errors">\n                    {{ error }}\n                    <a href="" @click.prevent="errors.splice(i,1)"><i class="icon-cancel-outline"></i></a>\n                </div>\n            </div>\n        </transition>\n\n        <div class="vue-loader-component" v-if="loading">\n            <span>Chargement</span>\n        </div>\n\n        \n\n        <nav class="buttons">\n            <a href="" class="btn btn-primary" @click.prevent="handlerWorkPackageNew" v-if="editable">Nouveau lot</a>\n        </nav>\n\n        <section class="workpackages">\n            <workpackage v-for="wp in workpackages"\n                :workpackage="wp"\n                :persons="persons"\n                :editable="editable"\n                :is-validateur="isValidateur"\n                @addperson="addperson"\n                @workpackageupdate="handlerWorkPackageUpdate"\n                @workpackagepersonupdate="handlerUpdateWorkPackagePerson"\n                @workpackagepersondelete="handlerWorkPackagePersonDelete"\n                @workpackagedelete="handlerWorkPackageDelete"\n                @workpackagecancelnew="handlerWorkPackageCancelNew"\n                ></workpackage>\n        </section>\n    </section>',

        data: function data() {
            return {
                loading: false,
                errors: [],
                workpackages: [],
                persons: [],
                editable: false,
                isDeclarant: false,
                isValidateur: false,
                token: 'DEFAULT_TKN'
            };
        },


        watch: {},
        computed: {},

        created: function created() {
            var _this3 = this;

            _vue2.default.http.interceptors.push(function (request, next) {
                request.headers.set('X-CSRF-TOKEN', _this3.token);
                request.headers.set('Authorization', 'OSCAR TOKEN');
                next();
            });
            this.fetch();
        },


        methods: {
            handlerWorkPackageCancelNew: function handlerWorkPackageCancelNew(workpackage) {
                this.workpackages.splice(this.workpackages.indexOf(workpackage), 1);
            },
            handlerWorkPackageNew: function handlerWorkPackageNew() {
                this.workpackages.push({
                    id: -1,
                    code: "Nouveau Lot",
                    label: "",
                    persons: [],
                    description: "",
                    start: (0, _momentTimezone2.default)().format(),
                    end: (0, _momentTimezone2.default)().format()

                });
            },
            handlerWorkPackagePersonDelete: function handlerWorkPackagePersonDelete(workpackageperson) {
                var _this4 = this;

                this.$http.delete(this.$http.$options.root + "?workpackagepersonid=" + workpackageperson.id).then(function (res) {
                    _this4.fetch();
                }, function (err) {
                    _this4.errors.push("Impossible de supprimer le déclarant : " + err.body);
                });
            },
            handlerWorkPackageDelete: function handlerWorkPackageDelete(workpackage) {
                var _this5 = this;

                this.$http.delete(this.$http.$options.root + "?workpackageid=" + workpackage.id).then(function (res) {
                    _this5.fetch();
                }, function (err) {
                    _this5.errors.push("Impossible de supprimer le lot : " + err.body);
                });
            },
            handlerWorkPackageUpdate: function handlerWorkPackageUpdate(workPackageData) {
                var _this6 = this;

                var datas = new FormData();
                for (var key in workPackageData) {
                    datas.append(key, workPackageData[key]);
                }
                if (workPackageData.id > 0) {
                    // Mise à jour
                    datas.append('workpackageid', workPackageData.id);
                    this.$http.post(this.$http.$options.root, datas).then(function (res) {
                        _this6.fetch();
                    }, function (err) {
                        _this6.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
                    });
                } else {
                    datas.append('workpackageid', -1);
                    this.$http.put(this.$http.$options.root, datas).then(function (res) {
                        _this6.fetch();
                    }, function (err) {
                        _this6.errors.push("Impossible de créer le lot de travail : " + err.body);
                    });
                }
            },
            handlerUpdateWorkPackagePerson: function handlerUpdateWorkPackagePerson(workpackageperson, duration) {
                var _this7 = this;

                var datas = new FormData();
                datas.append('workpackagepersonid', workpackageperson.id);
                datas.append('duration', duration);
                this.$http.post(this.$http.$options.root, datas).then(function (res) {
                    workpackageperson.duration = duration;
                }, function (err) {
                    _this7.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
                });
            },
            addperson: function addperson(personid, workpackageid) {
                var _this8 = this;

                console.log(arguments);
                var data = new FormData();
                data.append('idworkpackage', workpackageid);
                data.append('idperson', personid);

                this.$http.put(this.$http.$options.root, data).then(function (res) {
                    _this8.fetch();
                }, function (err) {
                    _this8.errors.push("Impossible d'ajouter le déclarant : " + err.body);
                }).then(function () {
                    return _this8.loading = false;
                });
            },
            fetch: function fetch() {
                var _this9 = this;

                this.loading = true;

                this.$http.get(this.$http.$options.root).then(function (res) {
                    _this9.workpackages = res.body.workpackages;
                    _this9.persons = res.body.persons;
                    _this9.editable = res.body.editable;
                    _this9.isDeclarant = res.body.isDeclarant;
                    _this9.isValidateur = res.body.isValidateur;
                }, function (err) {
                    _this9.errors.push("Impossible de charger les lots de travail : " + err.body);
                }).then(function () {
                    return _this9.loading = false;
                });
            }
        }
    });

    exports.default = Workpackageperson;
});