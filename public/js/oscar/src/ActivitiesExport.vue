<template>
    <form :action="urlPost" method="POST">
        <input type="hidden" name="ids" :value="ids" />
        <div class="btn-group">
            <button type="submit" class="btn btn-xs btn-default"> <i class="icon-download-outline"></i>Télécharger le CSV</button>
            <button type="button" class="btn btn-xs btn-default" @click="showConfiguration = !showConfiguration"> <i class="icon-cog"></i>Configurer</button>
        </div>
        <section v-show="showConfiguration" class="vue-loader text-small export-config">
            <div class="content-loader">
                <h2>Champs à exporter</h2>
                <input type="hidden" :value="selectedFields" name="fields">
                <hr>
                <h3><i class="icon-cube"></i>Champs de base</h3>
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
                <h3><i class="icon-calendar"></i>Jalons</h3>
                <div class="cols">
                    <label v-for="field, i in fieldsUI.milestones" class="col3">
                        <input type="checkbox" :checked="field.selected"  @click="toggleField(field.label)"/>
                        {{ field.label }}
                    </label>
                </div>
                <hr>
                <nav>
                    <a class="btn btn-default" @click="selectAll()">
                        <small v-if="switchSelect">Sélectionner tous les champs</small>
                        <small v-else>Déselectionner tous les champs</small>
                    </a>
                    <button class="btn btn-default" type="button" @click="showConfiguration = false">Fermer</button>
                    <button class="btn btn-primary" type="submit" @click="showConfiguration = false">Exporter</button>
                </nav>
            </div>

        </section>
    </form>
</template>
<script>

    export default {
        props: {
            ids: { default: [] },
            fields: { default: [] },
            urlPost: { required: true },
        },
        data(){
            return {
                selectedFields: [],
                showConfiguration: false,
                switchSelect: true
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
            selectAll(){
                var selected = [];
                if( this.switchSelect === true ){
                    for( var p in this.fields ){
                        if( this.fields.hasOwnProperty(p) ){
                            this.fields[p].forEach((field) => {
                                selected.push(field);
                            })
                        }
                    }

                }
                this.switchSelect = !this.switchSelect;

                this.selectedFields = selected;
            },

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
            if( window.localStorage && window.localStorage.getItem('export_fields') ){
                this.selectedFields = JSON.parse(window.localStorage.getItem('export_fields'));
            } else {
                this.selectedFields = [];
            }
        },

        watch: {
            selectedFields(newVal){
                if( window.localStorage ){
                    window.localStorage.setItem('export_fields', JSON.stringify(this.selectedFields));
                }
            }
        }
    }
</script>