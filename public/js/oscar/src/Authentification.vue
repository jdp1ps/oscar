<template>
    <section id="authentifications">
        <div class="vue-loader" v-if="loading"><span>Chargement</span></div>
        <h1><i class="icon-group"></i> Comptes</h1>

        <p class="oscar-help">
            Seules les personnes qui se sont authentifiées au moins une fois et les comptes spéciaux sont affichés.
        </p>

        <div class="alert alert-danger" v-if="error">
            <p class="text-right">
            <i class=" icon-cancel-outline" @click="error = null" style="float: right; cursor: pointer"></i>
            </p>
            {{ error }}
        </div>

        <div class="overlay" v-if="deleteDatas">

                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <h3>
                            <i class="icon-attention-1"></i>
                            Supprimer le rôle <strong>{{ deleteDatas.role }}</strong> <br>
                            pour le compte <strong>{{ deleteDatas.user.displayName }}</strong> ?
                        </h3>

                        <p class="alert alert-danger">
                            Cette suppression <strong>est définitive</strong>, ce compte, si c'est le votre, pourrez ne plus pouvoir accéder à l'application.
                            Êtes-vous sûr ?
                        </p>
                        <hr>
                        <nav>
                            <button class="btn btn-danger" @click="performDeleteRole()">Supprimer définitivement</button>
                            <button @click="deleteDatas = null" class="btn btn-default">Annuler</button>
                        </nav>
                    </div>
                </div>
        </div>

        <div class="overlay" v-if="addRoleUser">
            <form action="" class="overlay-content container" @submit.prevent="performAddRole()">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <h3>Ajouter un rôle au compte : <strong>{{ addRoleUser.displayName }}</strong></h3>
                        <div class="alert alert-danger" v-if="addRoleError">{{ addRoleError }}</div>
                        Rôle :

                        <select v-model="addRoleId">
                            <option v-for="r in roles" :value="r.id">{{ r.roleId }}</option>
                        </select>
                        <hr>
                        <nav>
                            <button type="submit" class="btn btn-default">Ajouter</button>
                            <button type="reset" @click="addRoleUser = null" class="btn btn-primary">Annuler</button>
                        </nav>
                    </div>
                </div>

            </form>
        </div>

        <div class="row">
            <div class="col-md-4">
                <nav class="oscar-sorter row">
                    <i class="icon-sort"></i>
                    Trie :
                    <a href="#" class="oscar-sorter-item" :class="{ active: orderby == 'lastLogin'}"
                       @click="updateOrderBy('lastLogin')">Dernière connexion</a>
                    <a href="#" class="oscar-sorter-item" :class="{ active: orderby == 'email'}"
                       @click="updateOrderBy('email')">Email</a>
                    <a href="#" class="oscar-sorter-item" :class="{ active: orderby == 'displayName'}"
                       @click="updateOrderBy('displayName')">Nom</a>
                </nav>
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1"><i class="icon-zoom-in-outline"></i></span>
                    <input type="search" v-model="search" class="form-control"
                           placeholder="Recherche dans les authentification"/>
                </div>
            </div>
            <div class="col-md-8">
                &nbsp;
            </div>
        </div>


        <div class="ui-user">

            <section class="users">

                <hr>
                <article v-for="(user,i) in usersFiltered" class="card xs" @click="handlerClick(user)"
                         :class="{'selected': selectedUser == user}">
                    <h3 class="card-title">
                        {{ user.displayName }} ({{ user.username }})
                    </h3>

                    <div class="card-content">
                        <small class="text-highlight">
                            <i class="icon-mail"></i>
                            <small>{{user.email}}</small>
                            <br>
                            Dernière connexion :
                            <span v-if="user.lastLogin">
                                {{ user.lastLogin | sinceFull }}
                            </span>
                            <span v-else>
                                Jamais
                            </span>
                            <br>
                            <template v-if="user.person">
                                Dans les activités oscar : <strong>{{ user.person.displayname }}</strong>
                            </template>
                        </small>

                        <div class="roles">
                            Rôles (implicites) :
                            <span class="cartouche" v-for="role in user.roles">
                                {{ role }}
                                <span class="addon">
                                    <i class="icon-trash" @click.prevent.stop="handlerDeleteRole(user, role)"></i>
                                </span>
                            </span>
                            <div class="btn btn-default btn-xs" @click="addRoleUser = user">
                                <i class="icon-user"></i>
                                Ajouter un rôle
                            </div>
                        </div>
                    </div>
                </article>
            </section>
            <section v-if="logs" class="logs">
                <h2>
                    <i class="icon-signal"></i>
                    Activités du compte
                </h2>

                <template v-if="logsFiltered.length">
                    <section v-for="stack in logsFiltered">
                        <h3>{{ stack.day }}</h3>
                        <article class="card" v-for="log in stack.logs">
                            <div class="text-highlight">
                                <small>{{ log.dateCreated.date | sinceFull }}</small>
                                - depuis l'IP <strong>{{ log.ip }}</strong>
                            </div>
                            <p>{{ log.message | logMessage }}</p>
                        </article>
                    </section>
                </template>
                <section v-else>
                    <p class="alert alert-info">Aucune donnée</p>
                </section>
            </section>
        </div>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  Authentification --filename.css Authentification.css --filename.js Authentification.js --dist public/js/oscar/dist public/js/oscar/src/Authentification.vue
    export default {
        props: {
            'users': {required: true},
            'roles': {required: true},
            'urlLogs': {required: true},
            'urlRoles': {required: true},
            'moment': {required: true},
        },

        data() {
            return {
                orderby: "lastLogin",
                orderbyDirection: -1,
                search: "",
                logs: null,
                selectedUser: null,
                loading: false,
                error: null,
                addRoleUser: null,
                addRoleId: 1,
                addRoleError: null,
                deleteDatas: null,
                deleteError: null
            }
        },

        methods: {
            /**
             * Retourne le Role à partir du Label.
             **/
            getRoleId(role){
                return this.roles.find( r => r.roleId == role );
            },

            /**
             * Affichage de l'écran de confirmation de suppression.
             *
             * @param user
             * @param role
             */
            handlerDeleteRole(user, role) {
                this.deleteError = null;
                this.deleteDatas = {
                    user, role
                };
            },

            /**
             * Déclenche la requête pour la suppression.
             */
            performDeleteRole() {
                let authentification_id = this.deleteDatas.user.id, role_id = this.getRoleId(this.deleteDatas.role).id;
                this.loading = true;
                this.addRoleError = null;
                this.$http.delete(
                    this.urlRoles,
                    { body: { 'authentification_id': authentification_id, 'role_id': role_id } })
                    .then(
                    res => {
                        this.deleteDatas.user.roles = res.data.roles;
                        this.deleteDatas = null;
                        this.deleteError = null;
                    },
                    err => {
                        this.deleteError = err.body;
                    }
                ).then(
                    foo => {
                        this.loading = false;
                    })
            },

            /**
             * Déclenche la requête pour l'ajout d'un rôle..
             */
            performAddRole() {

                this.loading = true;
                this.addRoleError = null;

                if (this.addRoleUser.id && this.addRoleId) {

                    var form = new FormData();
                    form.append('authentification_id', this.addRoleUser.id);
                    form.append('role_id', this.addRoleId);


                    this.$http.post(this.urlRoles, form).then(
                        res => {
                            this.addRoleUser.roles = res.data.roles;
                            this.addRoleUser = null;
                            this.addRoleError = null;
                        },
                        err => {
                            this.addRoleError = err.body;
                        }
                    ).then(foo => {
                        this.loading = false;
                    })
                }
            },

            updateOrderBy(order) {
                if (order == this.orderby) {
                    this.orderbyDirection *= -1;
                } else {
                    this.orderbyDirection = -1;
                    this.orderby = order;
                }
            },

            handlerClick(user) {
                this.selectedUser = user;
                this.loading = true;
                this.$http.get(this.urlLogs + user.id).then(
                    (res) => {
                        this.logs = res.body;

                    },
                    err => {
                        console.log(err)
                    }
                ).then(() => {
                    this.loading = false;
                });
            }
        },

        computed: {
            usersFiltered() {

                var users = [];

                if (this.search != "") {
                    this.users.forEach((u) => {
                        var corpus = u.displayName + u.email + u.username;
                        if (corpus.indexOf(this.search) > -1) {
                            users.push(u);
                        }
                    });
                } else {
                    users = this.users;
                }

                users.sort((a, b) => {
                    if (a[this.orderby] == b[this.orderby]) return 0
                    if (a[this.orderby] < b[this.orderby]) {
                        return -this.orderbyDirection;
                    } else {
                        return this.orderbyDirection;
                    }
                });

                return users;
            },
            logsFiltered() {
                var logsGroup = [];

                if (this.logs) {
                    var dayStack = null;
                    this.logs.forEach(log => {
                        var m = this.moment(log.dateCreated.date);
                        var currentDay = m.format('dddd D MMMM YYYY') + ', ' + m.fromNow();
                        if (dayStack == null || dayStack.day != currentDay) {
                            dayStack = {
                                day: currentDay,
                                logs: []
                            };
                            logsGroup.push(dayStack);
                        }
                        dayStack.logs.push(log);
                    });
                }
                return logsGroup;
            }
        }
    }
</script>