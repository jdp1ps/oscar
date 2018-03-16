<template>
    <p class="navbar-text navbar-right" id="notifications-area" >
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
                    <a href="#" title="Tous marquer comme lu" @click="deleteNotification(notifications)"><i class="icon-ok-circled"></i></a>
                </h3>
            </header>
            <article v-for="notification in orderedNotifications" :class="{ 'read': notification.read, 'fresh' : notification.fresh }" class="notification">
                <h4>
                    <i :class="'icon-'+notification.context"></i>
                    réçue le
                    <time datetime="">{{ notification.dateEffective | displayDate }}</time>
                    <a href="#" @click="deleteNotification([notification])"><i class="icon-trash-empty"></i></a>
                </h4>
                <p><span v-html="messageHTML(notification.message)" @click.prevent.stop="handlerClickNotification($event, notification)" /> le {{ notification.dateReal | displayDate }}</p>
            </article>
            <footer class="control">
                <a :href="urlHistory">Historique des notifications</a>
            </footer>
        </section>
    </p>
</template>

<script>
    export default {
        props: {
            url: { required: true },
            urlHistory: { required: true }
        },

        data() {
            return {
                open: false,
                loading: true,
                notifications: [],
                urlActivityShow: "/activites-de-recherche/fiche-detaillee/"
            }
        },

        computed: {
            /**
             * Retourne les notifications rangées par date effective.
             * @returns {Array.<T>}
             */
            orderedNotifications(){
                return this.notifications.sort((n1, n2) => {
                    return n1.dateEffective > n2.dateEffective ? -1 :
                        n1.dateEffective < n2.dateEffective ? 1 : 0;
                });
            }
        },

        methods: {
            handlerClickNotification(evt, notification){
                var redirect = null;
                console.log(evt.target.href);
                if( evt.target.href )
                    redirect = evt.target.href;


                this.$http.delete(this.url+'?ids=' + notification.id).then(
                    (res) => {
                        document.location = redirect;
                    },
                    (err) => {
                        console.log("ERROR");
                    }).then(()=>{this.loading = false;});
            },

            messageHTML(message){
                var reg = /(.*)\[Activity:([0-9]*):(.*)\](.*)/, match;
                if( match = reg.exec(message) ){
                    return message.replace(reg, "$1"+'<a href="' + this.urlActivityShow +'$2">$3</a> $4');
                }
                return message;
            },

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
                this.$http.get(this.url).then(
                    (res) => {
                        this.notifications = res.body.notifications;
                        if( this.notifications.length == 0 )
                            this.open = false;
                    },
                    (err) => {

                    }
                ).then(()=> this.loading = false );
            }
        },

        mounted(){
            this.fetch();
        }
    }
</script>