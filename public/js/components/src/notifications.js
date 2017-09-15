/**
 *
 */
import Vue from 'vue';
import VueResource from 'vue-resource';
import moment from 'mm';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;

var notifications = Vue.extend({
    template: ` <p class="navbar-text navbar-right" id="notifications-area" >
                        <a class="navbar-link" @click="open = !open">
                            <i class="icon-bell"></i> Notifications 
                            <span class="notifications-total" :class="notifications.length ? 'unread' : ''">{{ notifications.length }}</span>
                        </a>
                        <section class="list" v-show="open">
                            <header class="control">
                                <h3>
                                    <span class="intitule">
                                        <i class="icon-bell"></i>
                                        {{ notifications.length }} notifications
                                        <i class="animate-spin icon-asterisk" v-show="loading"></i>
                                    </span>
                                    <a href="#" title="Tous effacer" @click="deleteNotification(notifications)"><i class="icon-trash-empty"></i></a>
                                    <a href="#" title="Tous marquer comme lu"><i class="icon-ok-circled"></i></a>
                                </h3>
                            </header>
                            <article v-for="notification in orderedNotifications" :class="{ 'read': notification.read, 'fresh' : notification.fresh }" class="notification">
                                <h4>
                                    <time datetime="">{{ notification.dateEffective | moment }}</time>
                                    <a href="#" @click="deleteNotification([notification])"><i class="icon-trash-empty"></i></a>
                                </h4>
                                <p>{{ notification.message }}</p>
                            </article>
                        </section>
                    </p>`,

    data() {
        return {
            open: false,
            loading: true,
            notifications: []
        }
    },

    filters: {
        moment( data ){
            let m = moment(data);
            return m.format('dddd D MMMM YYYY') + ", " + m.fromNow();
        }
    },

    computed: {
        orderedNotifications(){
            return this.notifications.sort((n1, n2) => {
                return n1.dateEffective > n2.dateEffective ? -1 :
                    n1.dateEffective < n2.dateEffective ? 1 : 0;

            })
        }
    },

    methods: {
        deleteNotification(notifs) {
            this.loading = true;
            var ids = [];
            notifs.forEach((n) => {
                ids.push(n.id);
            })
            this.$http.delete(this.$http.$options.root+'?ids=' + ids.join(',')).then(
                (res) => {
                    console.log("A supprimer",notifs, "notifications", this.notifications);
                    this.fetch();
                },
                (err) => {
                    console.log("ERROR");
                }).then(()=>{this.loading = false;});
        },

        fetch(){
            this.loading = true;
            this.$http.get(this.$http.$options.root).then(
                (res) => {
                    this.notifications = res.body.notifications;
                },
                (err) => {

                }
            ).then(()=> this.loading = false );
        }
    }
});

export default notifications;