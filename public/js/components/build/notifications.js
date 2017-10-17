define(['exports', 'vue', 'vue-resource', 'mm'], function (exports, _vue, _vueResource, _mm) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    var _vueResource2 = _interopRequireDefault(_vueResource);

    var _mm2 = _interopRequireDefault(_mm);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    _vue2.default.use(_vueResource2.default); /**
                                               *
                                               */


    _vue2.default.http.options.emulateJSON = true;
    _vue2.default.http.options.emulateHTTP = true;

    var notifications = _vue2.default.extend({
        template: ' <p class="navbar-text navbar-right" id="notifications-area" >\n                        <a class="navbar-link" @click="open = !open">\n                            <i class="icon-bell"></i> Notifications \n                            <span class="notifications-total" :class="notifications.length ? \'unread\' : \'\'">{{ notifications.length }}</span>\n                        </a>\n                        <section class="list" v-show="open">\n                            <header class="control">\n                                <h3>\n                                    <span class="intitule">\n                                        <i class="icon-bell"></i>\n                                        {{ notifications.length }} notifications\n                                        <i class="animate-spin icon-asterisk" v-show="loading"></i>\n                                    </span>\n                                    <a href="#" title="Tous effacer" @click="deleteNotification(notifications)"><i class="icon-trash-empty"></i></a>\n                                    <a href="#" title="Tous marquer comme lu"><i class="icon-ok-circled"></i></a>\n                                </h3>\n                            </header>\n                            <article v-for="notification in orderedNotifications" :class="{ \'read\': notification.read, \'fresh\' : notification.fresh }" class="notification">\n                                <h4>\n                                    <i :class="\'icon-\'+notification.context"></i>\n                                    <time datetime="">{{ notification.dateReal | moment }}</time>\n                                    <a href="#" @click="deleteNotification([notification])"><i class="icon-trash-empty"></i></a>\n                                </h4>                              \n                                <p v-html="messageHTML(notification.message)" @click.prevent.stop="handlerClickNotification($event, notification)"></p>\n                            </article>\n                            <footer class="control">\n                                <a :href="urlHistory">Historique des notifications</a>\n                            </footer>\n                        </section>\n                        \n                    </p>',

        data: function data() {
            return {
                open: false,
                loading: true,
                notifications: [],
                urlActivityShow: "/activites-de-recherche/fiche-detaillee/"
            };
        },


        filters: {
            moment: function moment(data) {
                var m = (0, _mm2.default)(data);
                return m.format('dddd D MMMM YYYY') + ", " + m.fromNow();
            }
        },

        computed: {
            orderedNotifications: function orderedNotifications() {
                return this.notifications.sort(function (n1, n2) {
                    return n1.dateEffective > n2.dateEffective ? -1 : n1.dateEffective < n2.dateEffective ? 1 : 0;
                });
            }
        },

        methods: {
            handlerClickNotification: function handlerClickNotification(evt, notification) {
                var _this = this;

                var redirect = null;
                if (evt.target.href) redirect = evt.target.href;

                this.$http.delete(this.$http.$options.root + '?ids=' + notification.id).then(function (res) {
                    document.location = redirect;
                }, function (err) {
                    console.log("ERROR");
                }).then(function () {
                    _this.loading = false;
                });
            },
            messageHTML: function messageHTML(message) {
                var reg = /(.*)\[Activity:([0-9]*):(.*)\](.*)/,
                    match;
                if (match = reg.exec(message)) {
                    return message.replace(reg, "$1" + '<a href="' + this.urlActivityShow + '$2">$3</a> $4');
                }
                return message;
            },
            deleteNotification: function deleteNotification(notifs) {
                var _this2 = this;

                this.loading = true;
                var ids = [];
                notifs.forEach(function (n) {
                    ids.push(n.id);
                });
                this.$http.delete(this.$http.$options.root + '?ids=' + ids.join(',')).then(function (res) {
                    console.log("A supprimer", notifs, "notifications", _this2.notifications);
                    _this2.fetch();
                }, function (err) {
                    console.log("ERROR");
                }).then(function () {
                    _this2.loading = false;
                });
            },
            fetch: function fetch() {
                var _this3 = this;

                this.loading = true;
                this.$http.get(this.$http.$options.root).then(function (res) {
                    _this3.notifications = res.body.notifications;
                    if (_this3.notifications.length == 0) _this3.open = false;
                }, function (err) {}).then(function () {
                    return _this3.loading = false;
                });
            }
        }
    });

    exports.default = notifications;
});