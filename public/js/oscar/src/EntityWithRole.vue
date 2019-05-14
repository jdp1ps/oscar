<template>
    <div>
        <code>{{ entityEdited }}</code>
        <div class="overlay" v-if="entityDelete">
            <div class="overlay-content">
                <i class="icon-cancel-outline overlay-closer" @click="entityDelete = null"></i>

                <h2>Supprimer le rôle <strong>{{ entityDelete.role }}</strong> de <strong>{{ entityDelete.enrolledLabel }}</strong> ?</h2>

                <nav class="admin-bar">
                    <button class="btn btn-default button-back" @click="entityDelete = null">
                        <i class="icon-angle-left"></i>
                        Annuler
                    </button>
                    <button class="btn btn-primary" @click="performDelete">
                        <i class="icon-trash"></i>
                        Confirmer la suppression
                    </button>
                </nav>
            </div>
        </div>

        <div class="overlay" v-if="entityEdited">
            <div class="overlay-content">
                <i class="icon-cancel-outline overlay-closer" @click="entityEdited = null"></i>

                <form :action="entityEdited.urlEdit" method="post" @submit.prevent="performEdit">
                    <h2>
                        <span v-if="entityEdited.enrolledLabel">
                            Modifier <strong>{{ entityEdited.enrolledLabel }}</strong>
                            en tant que <em>{{ entityEdited.role }}</em>
                        </span>
                    </h2>
                    <input type="hidden" name="enroled" class="form-control select2" v-model="entityEdited.enrolled" />

                    <div class="form-group">
                        <label class=" control-label" for="role">Rôle</label>
                        <select name="role" class=" form-control" v-model="entityEdited.roleId">
                            <option :value="roleId" v-for="role, roleId in roles">
                                {{ role }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label control-label" for="dateStart">Date de début</label>
                        <input type="text" name="dateStart" class="form-control datepicker form-control" placeholder="Date de début" v-model="entityEdited.start">
                    </div>
                    <div class="form-group">
                        <label class="form-label control-label" for="dateEnd">Date de fin</label>
                        <input type="text" name="dateEnd" class="form-control datepicker form-control" placeholder="Date de fin" v-model="entityEdited.end">
                    </div>

                    <nav class="admin-bar">
                        <button class="btn btn-default button-back" @click="entityEdited = null">
                            <i class="icon-angle-left"></i>
                            Annuler
                        </button>
                        <button class="btn btn-primary" type="submit">
                            <i class="icon-floppy"></i>
                            Enregistrer
                        </button>
                    </nav>
                </form>
            </div>
        </div>

        <div class="overlay" v-if="entityNew">
            <div class="overlay-content">
                <i class="icon-cancel-outline overlay-closer" @click="entityNew = null"></i>

                <h2>Rôle de <strong>{{ entityNew.enroledLabel }}</strong> : </h2>
                <input type="hidden" name="enroled" class="form-control select2" v-model="entityNew.enrolled" />

                <span v-if="entityNew.enroledLabel" class="cartouche">
                    {{ entityNew.enroledLabel }}
                    <i class="icon-cancel-alt icon-clickable" @click="handlerCancel"></i>
                    <span class="addon" v-if="entityNew.role">
                        {{ roles[entityNew.role] }}
                    </span>
                </span>
                <div class="form-group" v-else>
                    <label class=" control-label" for="enroled">{{ title }}</label>
                    <personselector @change="handlerEnrolledSelected($event)" v-if="title == 'Personne'"/>
                    <organizationselector @change="handlerEnrolledSelected($event)" v-else/>
                </div>

                <div class="form-group">
                    <label class=" control-label" for="role">Rôle</label>
                    <select name="role" class=" form-control" v-model="entityNew.role">
                        <option :value="roleId" v-for="role, roleId in roles">
                            {{ role }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label control-label" for="dateStart">Date de début</label>
                    <input type="text" name="dateStart" class="form-control datepicker form-control" placeholder="Date de début" v-model="entityNew.start">
                </div>
                <div class="form-group">
                    <label class="form-label control-label" for="dateEnd">Date de fin</label>
                    <input type="text" name="dateEnd" class="form-control datepicker form-control" placeholder="Date de fin" v-model="entityNew.end">
                </div>

                <nav class="admin-bar">
                    <button class="btn btn-default button-back" @click="entityNew = null">
                        <i class="icon-angle-left"></i>
                        Annuler
                    </button>
                    <button class="btn btn-primary" type="submit" @click="performNew">
                        <i class="icon-floppy"></i>
                        Enregistrer
                    </button>
                </nav>
            </div>
        </div>

        <nav class="admin-bar text-right">
            <a class="oscar-link" @click="handlerNew()">
                <i class="icon-doc-add"></i>
                Nouveau
            </a>
        </nav>
        <span class="cartouche" v-for="e in entities" :class="{'primary': e.rolePrincipal }">
            <i v-if="e.context == 'activity'" class="icon-cube"></i>
            <i v-else class="icon-cubes"></i>
            <span class="text-clickable" @click="open(e.urlShow)" v-if="e.urlShow">{{ e.enrolledLabel }}</span>
            <span v-else>{{ e.enrolledLabel }}</span>
            <span class="addon">
                {{ e.roleLabel }}
                <i class="icon-pencil-1 icon-clickable" v-if="e.urlEdit" @click="handlerEdit(e)"></i>
                <i class="icon-trash icon-clickable" v-if="e.urlDelete" @click="handlerDelete(e)"></i>
            </span>
        </span>
    </div>
</template>
<script>
// nodejs node_modules/.bin/poi watch --format umd --moduleName  EntityWithRole --filename.js EntityWithRole.js --dist public/js/oscar/dist public/js/oscar/src/EntityWithRole.vue
import OrganizationAutoCompleter from "./OrganizationAutoCompleter";
import PersonAutoCompleter from "./PersonAutoCompleter";

export default {
    components: {
        organizationselector: OrganizationAutoCompleter,
        personselector: PersonAutoCompleter
    },

    props: {
        url: { required: true },
        urlNew: { required: true },
        roles: { required: true },
        manage: { required: true, default: false },
        title: { required: true }
    },

    data(){
        return {
            entities: [],
            entityEdited: null,
            entityDelete: null,
            entityNew: null
        };
    },



    methods: {
        handlerEdit(item) {
            console.log("EDIT", item);
            this.entityEdited = item;
        },

        handlerDelete(item) {
            this.entityDelete = item;
        },

        handlerNew(){
            console.log(this.urlNew);
            this.entityNew = {
                end: '',
                start: '',
                role: null,
                enroled: null,
                enroledLabel: ""
            };
        },

        handlerEnrolledSelected(data){
            this.entityNew.enroled = data.id;
            this.entityNew.enroledLabel = data.label;
        },

        handlerCancel(){
            this.entityNew.enroled = null;
            this.entityNew.enroledLabel = "";
            this.entityNew.role = null;
        },

        open(url){
          document.location = url;
        },

        performDelete(){
            this.$http.post(this.entityDelete.urlDelete, {}).then( ok => {
                console.log("OK", ok);
            }, ko => {
                console.log('ERROR', ko);
            }).then( foo => {
                this.entityDelete = null;
                this.fetch();
            })
        },

        performEdit(){
            let data = new FormData();
            data.append('dateStart', '');
            data.append('dateEnd', '');
            data.append('role', this.entityEdited.roleId);
            data.append('enrolled', this.entityEdited.enrolled);
            this.$http.post(this.entityEdited.urlEdit, data).then( ok => {
                console.log("OK", ok);
            }, ko => {
                console.log('ERROR', ko);
            }).then( foo => {
                this.entityEdited = null;
                this.fetch();
            })
          console.log(arguments);
        },

        performNew(){
            let data = new FormData();
            data.append('dateStart', this.entityNew.start);
            data.append('dateEnd', this.entityNew.end);
            data.append('role', this.entityNew.role);
            data.append('enroled', this.entityNew.enroled);
            this.$http.post(this.urlNew+'/'+this.entityNew.enroled, data).then( ok => {
                console.log("OK", ok);
            }, ko => {
                console.log('ERROR', ko);
            }).then( foo => {
                this.entityNew = null;
                this.fetch();
            })
            console.log(arguments);
        },

        fetch(){
            this.$http.get(this.url).then(ok => {
                console.log(ok)
                this.entities = ok.body;
            },
            ko => {
                console.error(ko)
            });
        }
    },

    mounted(){
        this.fetch();
    }
}

</script>