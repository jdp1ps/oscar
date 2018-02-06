/**
 * Created by jacksay on 17-01-20.
 */
import Vue from 'vue';
import VueResource from 'vue-resource';
import LocalDB from 'LocalDB';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;


var Roles = {
    template: `<div class="roles">
        <strong class="role"
            v-for="r in roles"
            :class="{ 'selected': roleSelected && roleSelected.id == r.id, 'highlight': roleHighLight && roleHighLight.id == r.id, 'role-selected': selected.indexOf(r.id)>-1, 'discret': (r.spot & activeSpots) == 0}"
            @click="$emit('toggle', r.id)"
            @mousehover="$emit('hover', r.roleId)">

            <i class="icon-ok-circled icon-on"></i>
            <i class=" icon-minus-circled icon-off"></i>

            <span>{{ r.roleId }}</span>
        </strong>
    </div>`,
    props: ['selected', 'roles', 'activeSpots', 'roleHighLight', 'roleSelected']
};

var prefs = new LocalDB('oscar_privileges', {
    openedGroup: [],
    activeSpots: 4
});


var Privilege = Vue.extend({
    components: {
        'roles': Roles
    },
    template: `<section>
    <transition name="fade">
        <div class="vue-loader" v-if="errors.length">
            <div class="alert alert-danger" v-for="error, i in errors">
                {{ error }}
                <a href="" @click.prevent="errors.splice(i,1)"><i class="glyphicon glyphicon-remove"></i></a>
            </div>
        </div>
    </transition>

    <div class="vue-loader" v-if="loading">
        <span>Chargement</span>
    </div>

    <nav class="oscar-sorter">
            <i class="icon-sort"></i>
            Filtres :
               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 4) > 0 }" @click="toggleFilter(4)">Application</a>
               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 2) > 0 }" @click="toggleFilter(2)">Organization</a>
               <a href="#" class="oscar-sorter-item" :class="{ active: (activeSpots & 1) > 0 }" @click="toggleFilter(1)">Projet/Activité</a>
        </nav>

    <section v-for="group in grouped" class="card group-privilege">
        <h1 class="card-title" @click="toggleGroup(group.categorie.id)">
            <strong>
               <i class="icon-right-dir" v-show="!group.open"></i>
                <i class="icon-down-dir" v-show="group.open"></i>
                {{ group.categorie.libelle }}
            </strong>
        </h1>
        <article v-for="privilege in group.privileges" 
                class="privilege" 
                v-show="group.open" 
                :key="'p'+privilege.id" 
                :class="{'discret': (privilege.spot & activeSpots) == 0}">
            <section class="droits">    
                <strong class="privilege-label-heading">{{ privilege.libelle }}</strong><br>
                <roles :roleHighLight="roleHighLight" :roleSelected="roleSelected" :activeSpots="activeSpots" :selected="privilege.roles" :roles="roles" @toggle="toggle(privilege.id, $event)" @hover="handlerRoleHover"></roles>
            </section>
            <section>
                 <article v-for="sub in privilege.children" 
                class="privilege" 
                :key="'p'+sub.id" 
                :class="{'discret': (sub.spot & activeSpots) == 0}">
            <section class="droits">    
                <strong class="privilege-label">{{ sub.libelle }}</strong><br>
                <roles :roleHighLight="roleHighLight" :roleSelected="roleSelected" :activeSpots="activeSpots" :selected="sub.roles" :roles="roles" @toggle="toggle(sub.id, $event)" @hover="handlerRoleHover"></roles>
            </section>
            <section>
                
            </section>
        </article>
            </section>
        </article>
    </section>

    </section>`,

    data(){
        return {
            privileges: [],
            roleHighLight: null,
            roleSelected: null,
            errors: [],
            roles: [],
            loading: true,
            ready: false,
            groupBy: 'categorie',
            activeSpots: prefs.get('activeSpots'),
            openedGroup: prefs.get('openedGroup')
        }
    },
    watch: {
        roleHighLight(){
            console.log(this.roleHighLight)
        },
        openedGroup(){
            prefs.set('openedGroup', this.openedGroup);
        },
        activeSpots(){
            prefs.set('activeSpots', this.activeSpots);
        }
    },
    computed: {
        grouped(){
            var grouped = {};
            this.privileges.forEach((p) => {
                if( !grouped[p.categorie.id] ){
                    grouped[p.categorie.id] = {
                        open: this.openedGroup.indexOf(p.categorie.id) > -1,
                        privileges: [],
                        categorie: p.categorie
                    }
                }
                grouped[p.categorie.id].privileges.push(p);
            });
            return grouped;
        }
    },

    created () {
        this.fetch();
    },

    methods: {
        toggleFilter(bit){
            if( (this.activeSpots & bit) > 0){
                this.activeSpots -= bit;
            } else {
                this.activeSpots += bit;
            }
        },

        handlerRoleHover: function(){
          console.log(arguments);
        },

        toggleGroup: function( idCategory ){
            if( this.openedGroup.indexOf(idCategory) > -1 ){
                this.openedGroup.splice(this.openedGroup.indexOf(idCategory), 1);
            } else {
                this.openedGroup.push(idCategory);
            }
        },

        /**
         * @deprecated
         * @param jsonData
         */
        updatePrivilege(jsonData){
            for( var i=0; i<this.privileges.length; i++ ){
                if( this.privileges[i].id == jsonData.id ) {
                    this.privileges.splice(i,1, jsonData);
                }
            }
        },
        /**
         * Parcourt récursivement les privilèges.
         *
         * @param jsonData
         */
        updateRecursive(privilegelist, jsonData){
            for( var i=0; i<privilegelist.length; i++ ){
                if( privilegelist[i].id == jsonData.id ) {
                    privilegelist.splice(i,1, jsonData);
                }
                if( privilegelist[i].children && privilegelist[i].children.length ){
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
        toggle: function(privilegeid, roleid){
            this.loading = true;

            this.$http.patch(this.$http.$options.root, {privilegeid, roleid}).then(
                (res) => {
                    console.log(res)
                    this.updateRecursive(this.privileges, res.body);
                },
                (err) => {
                    console.error(err)
                    this.errors.push(err.body);
                }
            ).then(()=>{
                this.loading = false;
            });
        },

        getRoleById: function( id ){
            for( let i=0; i<this.roles.length; i++ ){
                if(this.roles[i].id == id )
                    return this.roles[i];
            }
            return null;
        },

        fetch(){
            this.loading = true;
            this.$http.get(this.$http.$options.root).then(
                (res) => {
                    this.privileges = res.body.privileges;
                    this.roles = res.body.roles;
                },
                (err) => {

                }
            ).then(()=> this.loading = false );
        }
    }
});

export default Privilege;
