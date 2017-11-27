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

    _vue2.default.use(_vueResource2.default);

    _vue2.default.http.options.emulateJSON = true;
    _vue2.default.http.options.emulateHTTP = true;

    var Activity = _vue2.default.extend({
        template: '<div>\n    <h1>\n        {{ infos.label }}\n        <i class="icon-rewind-outline" @click="fetch()"></i>\n    </h1>\n    <div class="container">\n        <div class="col-md-6">\n            <section v-if="persons.readable">\n                <h2> <i class="icon-group"></i>Membres</h2>\n                <span :class="{ \'primary\': person.main }" class="cartouche" v-for="person in persons.datas">\n                    {{ person.displayName }}\n                    <span class="addon">\n                        {{ person.role }}\n                        <a href="#" @click="handlerEdit"><i class="icon-pencil"></i></a>\n                        <a href="#" @click="handlerDelete"><i class="icon-trash"></i></a>\n                    </span>\n                </span>\n            </section>\n            <section v-if="organizations.readable">\n                <h2> <i class="icon-building-filled"></i>Partenaires</h2>\n                <span :class="{ \'primary\': organization.main }" class="cartouche" v-for="organization in organizations.datas">\n                    {{ organization.displayName }}\n                    <span class="addon">\n                        {{ organization.role }}\n                        <a href="#" @click="handlerEdit"><i class="icon-pencil"></i></a>\n                        <a href="#" @click="handlerDelete"><i class="icon-trash"></i></a>\n                    </span>\n                </span>\n            </section>\n            <section v-if="milestones.readable">\n                <h2> <i class="icon-calendar"></i> Jalons</h2>\n                <form action="" v-if="milestoneEdit">\n                    <select class="form-control" v-model="milestoneEdit.type_id">\n                        <option v-for="type in milestones.types" :value="type.id">{{ type.label }}</option>\n                    </select>\n                </form>\n                <article class="card xs jalon  past" v-for="milestone in milestones.datas">\n                    <time :datetime="milestone.dateStart.date">\n                        {{ milestone.dateStart.date | moment }}\n                    </time>\n                    <strong class="card-title">{{ milestone.type }}</strong>\n                    <p class="details">{{ milestone.comment }}</p>\n                    <nav>\n                        <a href="#" class="btn-delete">\n                            <i class="icon-trash"></i>\n                        </a>\n                        <a href="#" class="btn-edit">\n                            <i class="icon-edit" @click="handlerEditMilestone(milestone)"></i>\n                        </a>\n                    </nav>\n                </article>\n            </section>\n        </div>\n        <pre class="col-md-6">{{ $data.milestoneEdit }}</pre>\n    </div>\n</div>',
        filters: {
            moment: function moment(date) {
                var format = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'D MMMM YYYY';

                return (0, _momentTimezone2.default)(date).format(format);
            }
        },
        methods: {
            fetch: function fetch() {
                this.$http.get('fetch').then(function (response) {
                    console.log(response);
                }, function (error) {
                    console.log(error);
                });
            },
            handlerEditMilestone: function handlerEditMilestone(milestone) {
                console.log("EDIT", milestone);
                this.milestoneEdit = milestone;
            },
            handlerEdit: function handlerEdit() {
                console.log('Écran de modification');
            },
            handlerDelete: function handlerDelete() {
                console.log('Écran de suppression');
            }
        }
    });

    exports.default = Activity;
});