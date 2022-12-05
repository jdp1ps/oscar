<template>
    <div>
        <div class="overlay" v-if="showSelector">


            <div class="overlay-content">
                <p>Choississez un type de créneau : </p>

                <div class="row">
                    <div class="col-md-6">
                        <h3><i class="icon-cube"></i> Activités</h3>
                        <article class="timesheet-item" v-for="w in workpackages"
                                 @click.prevent="handlerSelectWP(w)"
                                 :class="{ 'selected' : selection && selection.id == w.id, 'disabled': !w.validation_up }">
                            <abbr :title="project" class="project-acronym"><i class="icon-cube"></i> {{ w.acronym }}</abbr>
                            <span class="activity-label" :title="w.activity">{{ w.activity.substring(1,13) }}</span>
                            <strong class="workpackage-infos">
                                <span class="code" :title="w.label">{{ w.code }}</span>
                                <small class="workpackage-label">{{ w.label }}</small>
                            </strong>
                        </article>
                    </div>
                    <div class="col-md-6">
                        <h3>Hors activité (Hors-lot)</h3>
                        <article class="timesheet-item horslots-item" v-for="w in others"  @click.prevent="handlerSelectOther(w)" :class="{ 'selected' : selection == w, 'disabled': !w.validation_up }">
                            <span class="project-acronym" ><i :class="'icon-'+w.code"></i> {{ w.label }}</span>
                            <small class="workpackage-infos">{{ w.description }}</small>
                        </article>
                    </div>
                </div>
                <nav>
                    <button class="btn btn-default" @click="showSelector = false">Annuler</button>
                    <button class="btn btn-primary" v-if="usevalidation" :class="selection ? '' : 'disabled'" @click="handlerValidSelection()">Valider</button>
                </nav>
            </div>

        </div>
        <div class="dropdown">
            <button class="btn-lg btn btn-default dropdown-toggle" type="button" @click.prevent="showSelector = true">
                <span v-if="hasSelected" class="info">
                    <i :class=" selectedIcon ? 'icon-' +selectedIcon : 'icon-archive'"></i>
                    <strong>{{selectedCode}}</strong> <em>{{ selectedLabel }}</em><br/>
                    <small class="text-light">{{ selectedDescription }}</small>
                </span>
                <em v-else class="info">Lot de travail/Activité...</em>
                <span class="caret"></span>
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            workpackages: { default: [] },
            selection: { required:true },
            others: { required: true },
            usevalidation: { default: false }
        },

        data(){
            return {
                showSelector: false
            }
        },

        computed: {
            hasSelected(){
                return this.selection != null;
            },
            selectedCode(){
                return this.selection ? this.selection.code : '';
            },
            selectedLabel(){
                return this.selection ? this.selection.label : '';
            },
            selectedIcon(){
                return this.selection && this.selection.icon ? this.selection.code : '';
            },
            selectedDescription(){
                return this.selection ? this.selection.description : '';
            }
        },

        methods: {
            handlerSelectWP(wp){
                console.log(wp);
                this.selection = wp;
                if( !this.usevalidation ){
                    this.handlerValidSelection();
                }
            },

            handlerValidSelection(){
                if( this.selection ) {
                    this.showSelector = false;
                    this.$emit('select', this.selection);
                }
            },

            handlerSelectOther(wp){
                this.selection = wp;
                if( !this.usevalidation ){
                    this.handlerValidSelection();
                }
            }
        }
    }
</script>