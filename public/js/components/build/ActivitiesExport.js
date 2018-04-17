define(['exports', 'vue'], function (exports, _vue) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    var ActivitiesExport = _vue2.default.extend({
        template: '\n        <form :action="urlPost" method="POST">\n            <input type="hidden" name="ids" :value="ids" />\n            <div class="btn-group">\n                <button type="submit" class="btn btn-xs btn-default"> <i class="icon-download-outline"></i>T\xE9l\xE9charger le CSV</button>\n                <button type="button" class="btn btn-xs btn-default" @click="showConfiguration = !showConfiguration"> <i class="icon-cog"></i>Configurer</button>\n            </div>\n            <section v-show="showConfiguration" class="vue-loader text-small export-config">\n                <div class="content-loader">\n                <h2></i>Champs \xE0 exporter</h2>\n                <input type="hidden" :value="selectedFields" name="fields">\n                <hr>\n                <h3><i class="icon-cube">Champs de base</h3>\n                <div class="cols">\n                    <label v-for="field, i in fieldsUI.core" class="col3">\n                        <input type="checkbox" :checked="field.selected" @click="toggleField(field.label)"/>\n                        {{ field.label }}\n                    </label>\n                </div>\n                <h3><i class="icon-building-filled"></i>Organisations</h3>\n                <div class="cols">\n                    <label v-for="field, i in fieldsUI.organizations" class="col3">\n                        <input type="checkbox" :checked="field.selected"  @click="toggleField(field.label)"/>\n                        {{ field.label }}\n                    </label>\n                </div>\n                <h3><i class="icon-user"></i>Membres</h3>\n                <div class="cols">\n                    <label v-for="field, i in fieldsUI.persons" class="col3">\n                        <input type="checkbox" :checked="field.selected"  @click="toggleField(field.label)"/>\n                        {{ field.label }}\n                    </label>\n                </div>\n                <h3><i class="icon-calendar"></i>Jalons</h3>\n                <div class="cols">\n                    <label v-for="field, i in fieldsUI.milestones" class="col3">\n                        <input type="checkbox" :checked="field.selected"  @click="toggleField(field.label)"/>\n                        {{ field.label }}\n                    </label>\n                </div>\n                <hr>\n                <button class="btn btn-default" type="button" @click="showConfiguration = false">Fermer</button>\n                <button class="btn btn-primary" type="submit" @click="showConfiguration = false">Exporter</button>\n                </div>\n                \n            </section>\n        </form>\n    ',

        data: function data() {
            return {
                ids: [],
                fields: [],
                urlPost: null,
                selectedFields: [],
                showConfiguration: false
            };
        },


        computed: {
            fieldsUI: function fieldsUI() {
                var _this = this;

                var fieldsUi = [];
                for (var p in this.fields) {
                    if (this.fields.hasOwnProperty(p)) {
                        fieldsUi[p] = [];
                        this.fields[p].forEach(function (field) {
                            fieldsUi[p].push({
                                label: field,
                                selected: _this.selectedFields.indexOf(field) > -1
                            });
                        });
                    }
                }

                return fieldsUi;
            }
        },

        methods: {
            toggleField: function toggleField(field) {
                console.log('toggle', field);
                if (this.selectedFields.indexOf(field) > -1) {
                    this.selectedFields.splice(this.selectedFields.indexOf(field), 1);
                } else {
                    this.selectedFields.push(field);
                }
            }
        },

        created: function created() {
            console.log('IDS', this.ids);
            console.log('URL', this.urlPost);
            console.log('FIELDS', this.fields);
            if (window.localStorage && window.localStorage.getItem('export_fields')) {
                this.selectedFields = JSON.parse(window.localStorage.getItem('export_fields'));
            } else {
                this.selectedFields = [];
            }
        },


        watch: {
            selectedFields: function selectedFields(newVal) {
                console.log('Mémorisation de ', newVal);
                if (window.localStorage) {
                    window.localStorage.setItem('export_fields', JSON.stringify(this.selectedFields));
                }
            }
        }
    }); /**
         * Created by jacksay on 17-01-12.
         */
    exports.default = ActivitiesExport;
});