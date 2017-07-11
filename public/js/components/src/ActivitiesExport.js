/**
 * Created by jacksay on 17-01-12.
 */
import Vue from "vue";

var ActivitiesExport = Vue.extend({
    template: `
        <form :action="urlPost" method="POST">
            <input type="hidden" name="ids" :value="ids" />
            <div class="btn-group">
                <button type="submit" class="btn btn-xs btn-default"> <i class="icon-download-outline"></i>Télécharger le CSV</button>
                <button type="button" class="btn btn-xs btn-default" @click="showConfiguration = !showConfiguration"> <i class="icon-cog"></i>Configurer</button>
            </div>
            <section v-show="showConfiguration" class="vue-loader text-small export-config">
                <div class="content-loader">
                <h2></i>Champs à exporter</h2>
                <input type="hidden" :value="selectedFields" name="fields">
                <hr>
                <h3><i class="icon-cube">Champs de base</h3>
                <div class="cols">
                    <label v-for="field, i in fieldsUI.core" class="col3">
                        <input type="checkbox" :checked="field.selected" @click="toggleField(field.label)"/>
                        {{ field.label }}
                    </label>
                </div>
                <h3><i class="icon-building-filled"></i>Organisations</h3>
                <div class="cols">
                    <label v-for="field, i in fieldsUI.organizations" class="col3">
                        <input type="checkbox" :checked="field.selected"  @click="toggleField(field.label)"/>
                        {{ field.label }}
                    </label>
                </div>
                <h3><i class="icon-user"></i>Membres</h3>
                <div class="cols">
                    <label v-for="field, i in fieldsUI.persons" class="col3">
                        <input type="checkbox" :checked="field.selected"  @click="toggleField(field.label)"/>
                        {{ field.label }}
                    </label>
                </div>
                <hr>
                <button class="btn btn-default" type="button" @click="showConfiguration = false">Fermer</button>
                <button class="btn btn-primary" type="submit" @click="showConfiguration = false">Exporter</button>
                </div>
                
            </section>
        </form>
    `,

    data(){
        return {
            ids: [],
            fields: [],
            urlPost: null,
            selectedFields: [],
            showConfiguration: false
        }
    },

    computed: {
        fieldsUI(){
            let fieldsUi = [];
            for( var p in this.fields ){
                if( this.fields.hasOwnProperty(p) ){
                    fieldsUi[p] = [];
                    this.fields[p].forEach((field) => {
                        fieldsUi[p].push({
                            label: field,
                            selected: this.selectedFields.indexOf(field) > -1
                        });
                    })
                }
            }

            return fieldsUi;
        }
    },

    methods:{
        toggleField(field){
            console.log('toggle', field);
            if( this.selectedFields.indexOf(field) > -1 ){
                this.selectedFields.splice(this.selectedFields.indexOf(field), 1);
            } else {
                this.selectedFields.push(field);
            }
        }
    },

    created(){
        console.log('IDS', this.ids);
        console.log('URL', this.urlPost);
        console.log('FIELDS', this.fields);
        if( window.localStorage && window.localStorage.getItem('export_fields') ){
            this.selectedFields = JSON.parse(window.localStorage.getItem('export_fields'));
        } else {
            this.selectedFields = [];
        }
    },

    watch: {
        selectedFields(newVal){
            console.log('Mémorisation de ', newVal);
            if( window.localStorage ){
                window.localStorage.setItem('export_fields', JSON.stringify(this.selectedFields));
            }
        }
    }
});

export default ActivitiesExport;
