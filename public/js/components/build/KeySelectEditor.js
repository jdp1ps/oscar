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

    var KeySelectEditor = {
        props: ['values', 'datas', 'name'],

        data: function data() {
            return {
                newData: ""
            };
        },


        'template': '<section>\n    <hr>\n    <article class="card card-xs" v-for="value, key in values">\n        Clef : {{ key }} = {{ value }}\n        <select v-model="value" @change="handlerUpdate(key, $event)" :name="name+\'[\' +key +\']\'">\n            <option value="">Ignorer</option>\n            <option value="" v-for="v,l in datas" :value="l">{{ v }}</option>\n        </select>\n        <button class="btn btn-default" @click="handlerDelete(key)" type="button">Supprimer cette correspondance</button>\n    </article>\n    <input type="text" v-model="newData" placeholder="Nouvelle clef..." />\n    <button class="btn btn-default" type="button" @click="handlerAddKey">Ajouter une correspondance</button>\n    </section>\n    ',

        methods: {
            handlerUpdate: function handlerUpdate(key, event) {

                this.values[key] = event.target.value;
            },
            handlerDelete: function handlerDelete(key) {
                console.log(this, key);
                _vue2.default.delete(this.values, key);
            },
            handlerAddKey: function handlerAddKey() {
                console.log(this.newData);
                _vue2.default.set(this.values, this.newData, "");
                this.newData = "";
            }
        }
    }; /**
        * Created by jacksay on 17-05-12.
        */
    exports.default = KeySelectEditor;
});