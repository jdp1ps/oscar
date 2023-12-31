define(['exports', 'vue', 'vue-resource', 'LocalDB'], function (exports, _vue, _vueResource, _LocalDB) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    var _vueResource2 = _interopRequireDefault(_vueResource);

    var _LocalDB2 = _interopRequireDefault(_LocalDB);

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

    var Roles = {
        template: '<div class="roles">\n        <strong class="role"\n            v-for="r in roles"\n            :class="{ \'selected\': roleSelected && roleSelected.id == r.id, \'highlight\': roleHighLight && roleHighLight.id == r.id, \'role-selected\': selected.indexOf(r.id)>-1, \'discret\': (r.spot & activeSpots) == 0}"\n            @click="$emit(\'toggle\', r.id)"\n            @mousehover="$emit(\'hover\', r.roleId)">\n\n            <i class="icon-ok-circled icon-on"></i>\n            <i class=" icon-minus-circled icon-off"></i>\n\n            <span>{{ r.roleId }}</span>\n        </strong>\n    </div>',
        props: ['selected', 'roles', 'activeSpots', 'roleHighLight', 'roleSelected']
    };

    var prefs = new _LocalDB2.default('oscar_privileges', {
        openedGroup: [],
        activeSpots: 4
    });

    var Privilege = _vue2.default.extend({
        components: {
            'roles': Roles
        },
        template: '<section>\n    <transition name="fade">\n        <div class="vue-loader" v-if="errors.length">\n            <div class="alert alert-danger" v-for="error, i in errors">\n                {{ error }}\n                <a href="" @click.prevent="errors.splice(i,1)"><i class="glyphicon glyphicon-remove"></i></a>\n            </div>\n        </div>\n    </transition>\n\n    <div class="vue-loader" v-if="loading">\n        <span>Chargement</span>\n    </div>\n    \n    <div class="row">\n        <div class="col-md-6">\n        <nav class="oscar-sorter">\n            <i class="icon-sort"></i>\n            Filtres :\n               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 4) > 0 }" @click="toggleFilter(4)">Application</a>\n               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 2) > 0 }" @click="toggleFilter(2)">Organization</a>\n               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 1) > 0 }" @click="toggleFilter(1)">Projet/Activit\xE9</a>\n              \n        </nav>\n        </div>\n        <div class="col-md-6">\n            <div class="input-group input">\n                <input type="search" v-model="filter" class="form-control input-sm" placeholder="Recherche dans les privil\xE8ges" />\n                <div class="input-group-addon"><i class="icon-search-outline"></i></div>\n            </div>\n        </div>\n    </div>\n    \n    <hr>\n\n    <section v-for="group in grouped" class="card group-privilege">\n        <h1 class="card-title" @click="toggleGroup(group.categorie.id)">\n            <strong>\n               <i class="icon-right-dir" v-show="!group.open"></i>\n                <i class="icon-down-dir" v-show="group.open"></i>\n                {{ group.categorie.libelle }}\n            </strong>\n        </h1>\n        <article v-for="privilege in group.privileges" \n                class="privilege" \n                v-show="group.open" \n                :key="\'p\'+privilege.id" \n                :class="{\'discret\': (privilege.spot & activeSpots) == 0}">\n            <section class="droits">    \n                <strong class="privilege-label-heading" :title="\'CODE: \' + privilege.categorie.code + \'_\' + privilege.code">{{ privilege.libelle }}</strong><br>\n                <roles :roleHighLight="roleHighLight" :roleSelected="roleSelected" :activeSpots="activeSpots" :selected="privilege.roles" :roles="roles" @toggle="toggle(privilege.id, $event)" @hover="handlerRoleHover"></roles>\n            </section>\n            <section>\n                 <article v-for="sub in privilege.children" \n                class="privilege" \n                :key="\'p\'+sub.id" \n                :class="{\'discret\': (sub.spot & activeSpots) == 0}">\n            <section class="droits">    \n                <strong class="privilege-label" :title="\'CODE: \' + sub.categorie.code + \'_\' + sub.code">{{ sub.libelle }}</strong><br>\n                <roles :roleHighLight="roleHighLight" :roleSelected="roleSelected" :activeSpots="activeSpots" :selected="sub.roles" :roles="roles" @toggle="toggle(sub.id, $event)" @hover="handlerRoleHover"></roles>\n            </section>\n            <section>\n                \n            </section>\n        </article>\n            </section>\n        </article>\n    </section>\n\n    </section>',

        data: function data() {
            return {
                privileges: [],
                roleHighLight: null,
                roleSelected: null,
                errors: [],
                roles: [],
                loading: true,
                ready: false,
                filter: "",
                groupBy: 'categorie',
                activeSpots: prefs.get('activeSpots'),
                openedGroup: prefs.get('openedGroup')
            };
        },

        watch: {
            roleHighLight: function roleHighLight() {},
            openedGroup: function openedGroup() {
                prefs.set('openedGroup', this.openedGroup);
            },
            activeSpots: function activeSpots() {
                prefs.set('activeSpots', this.activeSpots);
            }
        },
        computed: {
            grouped: function grouped() {
                var _this = this;

                var grouped = {};
                this.privileges.forEach(function (p) {
                    var forceOpen = false;
                    if (_this.filter) {
                        if (p.libelle.toLowerCase().indexOf(_this.filter.toLowerCase()) == -1) return;
                        forceOpen = true;
                    }
                    if (!grouped[p.categorie.id]) {
                        grouped[p.categorie.id] = {
                            open: _this.openedGroup.indexOf(p.categorie.id) > -1 || forceOpen,
                            privileges: [],
                            categorie: p.categorie
                        };
                    }
                    grouped[p.categorie.id].privileges.push(p);
                });
                return grouped;
            }
        },

        created: function created() {
            this.fetch();
        },


        methods: {
            toggleFilter: function toggleFilter(bit) {
                if ((this.activeSpots & bit) > 0) {
                    this.activeSpots -= bit;
                } else {
                    this.activeSpots += bit;
                }
            },


            handlerRoleHover: function handlerRoleHover() {},

            toggleGroup: function toggleGroup(idCategory) {
                if (this.openedGroup.indexOf(idCategory) > -1) {
                    this.openedGroup.splice(this.openedGroup.indexOf(idCategory), 1);
                } else {
                    this.openedGroup.push(idCategory);
                }
            },

            updatePrivilege: function updatePrivilege(jsonData) {
                for (var i = 0; i < this.privileges.length; i++) {
                    if (this.privileges[i].id == jsonData.id) {
                        this.privileges.splice(i, 1, jsonData);
                    }
                }
            },
            updateRecursive: function updateRecursive(privilegelist, jsonData) {
                for (var i = 0; i < privilegelist.length; i++) {
                    if (privilegelist[i].id == jsonData.id) {
                        privilegelist.splice(i, 1, jsonData);
                    }
                    if (privilegelist[i].children && privilegelist[i].children.length) {
                        this.updateRecursive(privilegelist[i].children, jsonData);
                    }
                }
            },


            /**
             * Permutte les droits.
             *
             * @param privilegeid
             * @param roleid
             */
            toggle: function toggle(privilegeid, roleid) {
                var _this2 = this;

                this.loading = true;

                this.$http.patch(this.$http.$options.root, { privilegeid: privilegeid, roleid: roleid }).then(function (res) {
                    _this2.updateRecursive(_this2.privileges, res.body);
                }, function (err) {
                    _this2.errors.push(err.body);
                }).then(function () {
                    _this2.loading = false;
                });
            },

            getRoleById: function getRoleById(id) {
                for (var i = 0; i < this.roles.length; i++) {
                    if (this.roles[i].id == id) return this.roles[i];
                }
                return null;
            },

            fetch: function fetch() {
                var _this3 = this;

                this.loading = true;
                this.$http.get(this.$http.$options.root).then(function (res) {
                    _this3.privileges = res.body.privileges;
                    _this3.roles = res.body.roles;
                }, function (err) {}).then(function () {
                    return _this3.loading = false;
                });
            }
        }
    });

    exports.default = Privilege;
});