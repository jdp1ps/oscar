<template>
    <section>
        <h1>Rôles</h1>
        <transition name="popup">
            <div class="form-wrapper" v-if="form" :class="{ loading: loading }">

                <form @submit.prevent="save" class="container oscar-form">
                    <div class="loadingMsg">
                        <span>Chargement</span>
                    </div>
                    <header>
                        <h1>
                            <span v-if="form.id">Modification de <strong>{{ form.roleId }}</strong></span>
                            <span v-else>Nouveau rôle</span>
                        </h1>
                        <a href="#" @click="form=null" class="closer">
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                    </header>

                    <div class="form-group">
                        <label>Nom du rôle (RoleId)</label>
                        <input id="role_roleid" type="text" class="form-control" placeholder="Role" v-model="form.roleId" name="roleid"/>
                    </div>
                    <div class="form-group">
                        <label>Principal</label>
                        <p class="help">
                            Un rôle définit comme principal sera traité spécifiquement dans l'interface
                        </p>
                        <input type="checkbox" class="form-control" v-model="form.principal"/>
                    </div>
                    <div class="form-group">
                        <label>Portée (spot)</label>
                        <p class="help">
                            La portée permet de définir le périmètre d'affectation d'un rôle.
                            Un rôle fixé au <strong>niveau application</strong> donne les droits de ce rôle sur l'application entière.
                            Un rôle définit au <strong>niveau organisation</strong> donne les droits de ce rôle sur les activités de cette organisation.
                            Un rôle définit au <strong>niveau activité</strong> ne donnera que les droits du rôle sur les projets/activités.
                        </p>
                        <div class="spots">
                            <span :class="{active: ((form.spot & 4) > 0)}" data-spot="4" @click="updateRoleSpotForm(4)">
                                <i class="icon-ok-circled"></i><i class="icon-minus-circled"></i>Application
                            </span>
                            <span :class="{active: ((form.spot & 2) > 0)}" data-spot="2" @click="updateRoleSpotForm(2)">
                                <i class="icon-ok-circled"></i><i class="icon-minus-circled"></i>Organisation
                            </span>
                            <span :class="{active: ((form.spot & 1) > 0)}" data-spot="1" @click="updateRoleSpotForm(1)">
                                <i class="icon-ok-circled"></i><i class="icon-minus-circled"></i>Activité
                            </span>
                        </div>
                        <input id="role_spot" type="hidden" v-model="form.spot" name="spot"/>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" v-model="form.description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Filtre LDAP</label>
                        <p class="help">
                            L'utilisation d'un filtre LDap affectera le rôle automatiquement sur l'application entière, peut importe le réglage de la portée.
                        </p>
                        <textarea class="form-control" v-model="form.ldapFilter" style="whitespace: wrap"></textarea>
                    </div>

                    <footer class="buttons-bar">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-floppy"></i>
                                Enregistrer
                            </button>
                            <button type="button" class="btn btn-default" @click="form=null">
                                <i class="icon-block"></i>
                                Annuler
                            </button>
                        </div>
                    </footer>
                </form>
            </div>
        </transition>

        <nav class="oscar-sorter">
            <i class="icon-sort"></i>
            Filtres :
            <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 4) > 0 }" @click="toggleFilter(4)">Application</a>
            <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 2) > 0 }" @click="toggleFilter(2)">Organization</a>
            <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 1) > 0 }" @click="toggleFilter(1)">Projet/Activité</a>
        </nav>

        <article v-for="role in roles" class="card role-list-item" :class="{'selected': common.roleSelected == role}" @mouseenter="handleHover(role)" @mouseleave="handleOut()" @click="handlerClick(role)">
            <div class="header role-header">
                <strong>
                    <i v-if="role.principal" class="icon-certificate"></i>
                    {{ role.roleId }}
                </strong>

                <nav class="nav-icons">
                    <a href="#" @click.prevent="remove(role)" v-if="role.deletable">
                        <i class="icon-trash"></i>
                    </a>
                    <a href="#" @click.prevent="formEdit(role)" v-if="role.editable">
                        <i class="icon-pencil"></i>
                    </a>
                </nav>
            </div>

            <p class="oscar-help" v-if="role.description">{{ role.description }}</p>

            <template v-if="role.ldapFilter">
                <p class="oscar-help">Les filtres LDAP s'appliquent sur l'application entière</p>
                <pre class="filtreLdap"><strong>Filtre LDAP</strong>:{{role.ldapFilter}}</pre>
            </template>
        </article>

        <nav class="buttons-bar">
            <a href="#" class="handler-add btn btn-primary" @click.prevent="newRoleForm">
                <i class="icon-plus-circled"></i> Ajouter un rôle
            </a>
        </nav>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName RolesAdminUI --filename.js RolesAdminUI.js --dist public/js/oscar/dist public/js/oscar/src/RolesAdminUI.vue

    export default {
        props: {
            privilegesDatas: {},
            url: null,
            Bootbox: null
        },

        data(){
            return {
                activeSpots: 7,
                form: null
            }
        },

        computed:{
            common(){
                return this.privilegesDatas;
            },

            roles(){
                var filteredRoles = [];
                this.common.roles.forEach((role)=>{
                    if( (role.spot & this.activeSpots) > 0 || role.spot == 0 )
                        filteredRoles.push(role);
                });
                return filteredRoles;
            }
        },

        methods: {
            handleHover: function(role){
                this.common.roleHighLight = role;
            },

            handlerClick: function(role){
                this.common.roleSelected = role == this.common.roleSelected ? null : role;
                this.common.roleHighLight = role;
            },

            handleOut: function(){
                this.common.roleHighLight = null;
            },

            toggleFilter(bit){
                if( (this.activeSpots & bit) > 0){
                    this.activeSpots -= bit;
                } else {
                    this.activeSpots += bit;
                }
            },

            formEdit (role) {
                this.form = JSON.parse(JSON.stringify(role));
            },

            remove (role) {
                this.Bootbox.confirm({
                    title: 'Supprimer définitivement le rôle ?',
                    message: 'Si vous souhaitez désactiver le rôle, décochez la portée dans le formulaire.',
                    callback: (response) => {
                        if (!response) return;
                        this.loading = true;
                        this.$http.delete(this.url + '/' + role.id).then(
                            (res) => {
                                console.log(res, this.common.roles);
                                for (let i = 0; i < this.common.roles.length; i++) {
                                    if (this.common.roles[i].id == role.id) {
                                        this.common.roles.splice(i, 1);
                                        flashMessage('success', "Le rôle a été supprimé");
                                        return;
                                    }
                                }
                            },
                            (err) => {
                                flashMessage('error', err.body);
                            }
                        ).then(()=> {
                            this.loading = false
                        });
                    }
                });
            },
            save: function(e){
                this.loading = true;
                if( this.form.id ){
                    this.$http.put(this.url + '/' +this.form.id, this.form).then(
                        (res) => {
                            for( let i=0; i<this.common.roles.length; i++ ){
                                if( this.common.roles[i].id == this.form.id ){
                                    this.common.roles.splice(i, 1, res.body);
                                    flashMessage('success', "Le rôle a été mis à jour");
                                    return;
                                }
                            }
                        },
                        (err) => {
                            flashMessage('error', err.body);
                        }
                    ).then(()=> { this.loading = false; this.form = null; });
                }
                else {
                    this.$http.post(this.url, this.form).then(
                        (res) => {
                            var added = res.body;
                            added.principal = (added.principal == 'true');
                            this.common.roles.push(added);
                            flashMessage('success', "Le rôle a été ajouté");
                            return;
                        },
                        (err) => {
                            flashMessage('error', err.body);
                        }
                    ).then(()=> { this.loading = false; this.form = null; });;
                }

            },

            newRoleForm(){
                this.form = {
                    roleId: "",
                    spot: 0,
                    ldapFilter: null,
                    description: "",
                    principal: false
                };
            },


            cssSpot: function(role){
                return 'spot'+role.spot;
            },

            updateRoleSpotForm ( spot ){
                if( (this.form.spot & spot) > 0){
                    this.form.spot -= spot;
                } else {
                    this.form.spot += spot;
                }
                console.log(this.form.spot);
            },

            updateRoleSpot: function(role, val){
                this.loading = true;
                var oldVal = role.spot;
                if( (role.spot & val) > 0){
                    oldVal -= val;
                } else {
                    oldVal += val;
                }

                var data = JSON.parse(JSON.stringify(role));
                data.spot = oldVal;


                this.$http.put(this.url + '/' +role.id, data).then(
                    (res) => {
                        flashMessage("success", "Le rôle a bien été modifié");
                        role.spot = oldVal;
                    },
                    (err) => {
                        flashMessage("error", "Impossible de modifier le rôle : " + err.body);
                    }
                ).then(()=> { this.loading = false });;
            }
        }
    }
</script>