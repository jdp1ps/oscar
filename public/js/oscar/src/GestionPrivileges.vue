<template>
    <section class="privileges">
        <h2><i class="icon-calculator"></i>Privileges</h2>

        <nav class="tabs">
            <div class="tab" :class="{'selected': selectedSpot == 4 }" @click="selectedSpot = 4">Application</div>
            <div class="tab" :class="{'selected': selectedSpot == 2 }" @click="selectedSpot = 2">Organisation</div>
            <div class="tab" :class="{'selected': selectedSpot == 1 }" @click="selectedSpot = 1">Activités</div>
        </nav>
        <section class="tab-content">
            <p class="alert alert-info" v-if="selectedSpot == 4">
                Les privilèges proposés ici ne concernent QUE les rôles obtenues via les filtres LDAP (ou accordé manuellement).
            </p>

            <p class="alert alert-info" v-if="selectedSpot == 2">
                Rôles accordès au personnes dans les organisations. <br>Accorde ainsi des accès aux activités
                <strong>si l'organisation est impliquée <br>avec un <i>rôle d'organisation</i> marqué comme principal.</strong>
            </p>

            <section v-if="privileges" class="privileges-ui">

                <header class="privilege">
                    <div class="content">
                        <div class="intitule">
                            <p>&nbsp;</p>
                        </div>
                        <div v-for="r in roles" v-if="r.spot & selectedSpot" class="role" :title="r.roleId">
                            <div class="role-label">{{ r.roleId }}</div>
                            <div class="square">&nbsp;</div>
                        </div>
                    </div>

                </header>

                <section class="scrollable">
                    <div v-for="g in filteredPrivileges">
                        <h2>
                            <i class="icon-sort-down"></i>
                            {{ g.label }}
                        </h2>
                        <article class="privilege" v-for="p in g.privileges">
                            <div class="content">
                                <div class="intitule">
                                    <strong class="privilege-label">{{ p.libelle }}</strong>
                                    <small>{{ p.description }}</small>
                                </div>
                                <div v-for="r in roles" v-if="r.spot & selectedSpot" class="role" :title="r.roleId">
                                    <i class="icon-valid text-success" v-if="p.roles.indexOf(r.id) >= 0"></i>
                                    <i class="icon-block text-error" v-else></i>
                                </div>
                            </div>
                            <section v-if="p.children">
                                <article class="privilege" v-for="sp in p.children">
                                    <div class="content">
                                        <div class="intitule">
                                            <strong class="privilege-label">{{ sp.libelle }}</strong><small>{{ sp.description }}</small>
                                        </div>
                                        <div v-for="r in roles" v-if="r.spot & selectedSpot" class="role" :title="r.roleId">
                                            <i class="icon-valid text-success" v-if="sp.roles.indexOf(r.id) >= 0"></i>
                                            <i class="icon-block text-error" v-else></i>
                                        </div>
                                    </div>
                                </article>
                            </section>
                        </article>
                    </div>
                </section>
            </section>

            <!--
            <div class="columns">
                <div class="col2">



                    <table v-if="privileges" class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th v-for="r in roles" v-if="r.spot & selectedSpot" style="width: 24px">
                                    <strong style="transform: rotate(-90deg); display: block; width: 24px" >
                                        {{ r.roleId }}
                                    </strong>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr v-for="p in filteredPrivileges">
                            <td>
                                <strong>{{ p.libelle }}</strong><br>
                                <code>{{ p }}</code>
                            </td>


                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            -->
        </section>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  GestionPrivileges --filename.js GestionPrivileges.js --dist public/js/oscar/dist public/js/oscar/src/GestionPrivileges.vue


    var descriptions = {
        "PROJECT_INDEX": {
            "2" : "Peut recherche parmis les projets de l'organisation",
            "4" : "Peut recherche parmis tous les projets référencés dans l'application"
        },
        "PROJECT_SHOW": {
            "*": "Voir la fiche détaillée d'un projet"
        },
        "PROJECT_EDIT": {
            "*": "Peut modifier les informations d'un projet"
        },
    };

    export default {
        name: "GestionPrivileges",
        props: ['url'],

        data() {
            return {
                selectedSpot: 4,
                error: null,
                pendingMsg: "",
                privileges: null,
                roles: null
            }
        },

        computed: {
            filteredPrivileges(){
                let out = {};
                let spot = this.selectedSpot;
                let category = null;

                this.privileges.forEach(p => {
                    if( p.spot & spot ){
                        let currentCategory = p.categorie.libelle;
                        if( !out.hasOwnProperty(currentCategory) ){
                            out[currentCategory] = {
                                "label": currentCategory,
                                "privileges" : {}
                            };
                        }
                        out[currentCategory].privileges[p.id] = p;
                    }
                });
                return out;
            }
        },

        methods: {
            ////////////////////////////////////////////////////////////////
            //
            // HANDLERS
            //
            ////////////////////////////////////////////////////////////////


            ////////////////////////////////////////////////////////////////
            //
            // OPERATIONS REST
            //
            ////////////////////////////////////////////////////////////////

            /**
             * Chargement des jalons depuis l'API
             */
            fetch() {
                this.pendingMsg = "Chargement des droits";

                this.$http.get(this.url).then(
                    success => {
                        this.privileges = success.data.privileges;
                        this.roles = success.data.roles;
                    },
                    error => {
                        if( error.status == 403 ){
                            this.error = "Vous n'avez pas l'autorisation d'accès à ces informations.";
                        } else {
                            this.error = "Impossible de charger les dépenses pour ce PFI : " + error
                        }
                    }
                ).then(n => { this.pendingMsg = ""; });
            },
        },

        mounted() {
            this.fetch()
        }
    }
</script>