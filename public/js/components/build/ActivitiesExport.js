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
        template: '\n        <form :action="urlPost" method="POST">\n            <input type="hidden" name="" id="" :value="ids" />\n            <div class="btn-group">\n                <button type="submit" class="btn btn-xs btn-default"> <i class="icon-download-outline"></i>T\xE9l\xE9charger le CSV</button>\n                <button type="button" class="btn btn-xs btn-default" disabled> <i class="icon-cog"></i>Configurer</button>\n            </div>\n            <section v-show="showConfiguration">\n                <label v-for="field, i in fields">\n                    {{ field }}\n                    \n                </label>\n            </section>\n        </form>\n    ',

        data: function data() {
            return {
                ids: [],
                fields: [],
                urlPost: null,
                showConfiguration: false
            };
        },


        computed: {},

        methods: {},

        created: function created() {
            console.log('IDS', this.ids);
            console.log('URL', this.urlPost);
            console.log('FIELDS', this.fields);
        }
    }); /**
         * Created by jacksay on 17-01-12.
         */
    exports.default = ActivitiesExport;
});