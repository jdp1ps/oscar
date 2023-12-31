import Vue from 'vue';
import VueResource from 'vue-resource';
import LocalDB from 'LocalDB';
import Bootbox from 'bootbox';
import moment from 'moment-timezone';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;

var Activity = Vue.extend({
    template: `<div class="activity">
    <header>
        <div class="container">
            <h4>
                <i class="icon-cubes"></i> 
                <span class="acronym project-acronym">[{{ infos.projectacronym }}]</span>
                <em class="project-label">{{ infos.project }}</em>
            </h4>
            <h2>
                <i class="icon-cube"></i>
                <em class="activity-label">{{ infos.label }}</em>
            </h2>
            <ul class="metas">
                <li><i class="icon-tags"></i>Type : <strong>{{ infos.type || "Aucun type" }}</strong></li>
                <li><i class="icon-database"></i>N° Oscar : <strong>{{ infos.numOscar }}</strong></li>
                <li><i class="icon-calculator"></i>PFI : <strong>{{ infos.PFI || "Pas de PFI" }}</strong></li>
                <li><i class="icon-calendar"></i>
                    Du <strong>{{ infos.dateStart | moment }}</strong> au <strong>{{ infos.dateEnd | moment }}</strong> ~ 
                    <template v-if="infos.dateSigned">
                     (Signé le {{ infos.dateSigned | moment }})
                    </template>
                    <template v-else><em>Non signé</em></template>
                </li>
            </ul>
            {{ infos.label }}
         </div>
        <i class="icon-rewind-outline" @click="fetch()"></i>
    </header>
    <div class="container">
        <div class="col-md-6">
            <section v-if="persons.readable">
                <h2> <i class="icon-group"></i>Membres</h2>
                <span :class="{ 'primary': person.main }" class="cartouche" v-for="person in persons.datas">
                    {{ person.displayName }}
                    <span class="addon">
                        {{ person.role }}
                        <a href="#" @click="handlerEdit"><i class="icon-pencil"></i></a>
                        <a href="#" @click="handlerDelete"><i class="icon-trash"></i></a>
                    </span>
                </span>
            </section>
            <section v-if="organizations.readable">
                <h2> <i class="icon-building-filled"></i>Partenaires</h2>
                <span :class="{ 'primary': organization.main }" class="cartouche" v-for="organization in organizations.datas">
                    {{ organization.displayName }}
                    <span class="addon">
                        {{ organization.role }}
                        <a href="#" @click="handlerEdit"><i class="icon-pencil"></i></a>
                        <a href="#" @click="handlerDelete"><i class="icon-trash"></i></a>
                    </span>
                </span>
            </section>
            <section v-if="milestones.readable">
                <h2> <i class="icon-calendar"></i> Jalons</h2>
                <form action="" v-if="milestoneEdit">
                    <select class="form-control" v-model="milestoneEdit.type_id">
                        <option v-for="type in milestones.types" :value="type.id">{{ type.label }}</option>
                    </select>
                </form>
                <article class="card xs jalon  past" v-for="milestone in milestones.datas">
                    <time :datetime="milestone.dateStart.date">
                        {{ milestone.dateStart.date | moment }}
                    </time>
                    <strong class="card-title">{{ milestone.type }}</strong>
                    <p class="details">{{ milestone.comment }}</p>
                    <nav>
                        <a href="#" class="btn-delete">
                            <i class="icon-trash"></i>
                        </a>
                        <a href="#" class="btn-edit">
                            <i class="icon-edit" @click="handlerEditMilestone(milestone)"></i>
                        </a>
                    </nav>
                </article>
            </section>
        </div>
        <div class="col-md-6">
            <pre>DATA: 
            {{ $data }}</pre>
        </div>
    </div>
</div>`,
    filters: {
      moment(date, format = 'D MMMM YYYY'){
          return moment(date).format(format);
      }
    },
    methods: {
        fetch(){
            this.loading = true;
          this.$http.get('fetch').then(
              (response) => {
                  console.log(response);
              },
              (error) => {
                  console.log(error);
              }
          ).then( m => this.loading = false );
        },
        ////////////////////////////////////////////////////////////// MILESTONE
        handlerEditMilestone(milestone){
            console.log("EDIT", milestone);
            this.milestoneEdit = milestone;
        },

        handlerEdit(){
            console.log('Écran de modification');
        },
        handlerDelete(){
            console.log('Écran de suppression');
        }
    }
});

export default Activity;