<template>
    <div>
        <div class="overlay" v-if="showSelector">


            <div class="overlay-content">
                <p>Choississez un type de créneau : </p>

                <div class="row">
                    <div class="col-md-6">
                        <h3><i class="icon-cube"></i> Activités</h3>
                        <article class="timesheet-item" v-for="w in workpackages" @click.prevent="handlerSelectWP(w)" :class="{ 'selected' : selection && selection.id == w.id, 'disabled': !w.validation_up }">
                            <abbr :title="project" class="project-acronym"><i class="icon-cube"></i> {{ w.acronym }}</abbr>
                            <span class="activity-label">{{ w.activity }}</span>
                            <strong class="workpackage-infos">
                                <span class="code">{{ w.code }}</span>
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
<style scoped lang="scss">

    $baseColor: #0088cc;
    $offColor: desaturate($baseColor, 90%);
    $onColor: saturate($baseColor, 25%);

    .timesheet-item {
        background: lighten($offColor, 60%);
        cursor: pointer;
        border: thin $offColor solid;
        margin: .3em 0;
        font-size: 1em;
        display: flex;
        position: relative;
        transition: all .3s;
        >* { padding: .3em .5em; }
        .project-acronym {
            transition: all .3s;
            position: relative;
            display: inline-block;
            padding-right: .8em;
            padding-left: .8em;
            color: lighten($offColor, 30%);
            background: $offColor;
            text-shadow: -1px 1px 0 rgba(0,0,0,.3);
            font-weight: 700;
            &:before {
                transition: all .3s;
                content: " ";
                width: 1em;
                height: 1em;
                background: rgba(white, 0);
                transform: rotate(45deg);
                position: absolute;
                left: -.6em;
                top: .6em;
                z-index: 10000;
            }
        }
        .activity-label {
            transition: all .3s;
            position: relative;
            display: inline-block;
            padding-left: 1em;
            padding-right: 1em;
            background: lighten($offColor, 30%);
            color: darken($offColor, 30%);
            font-size: .8em;
            line-height: 2em;
            &:before {
                transition: all .3s;
                content: " ";
                width: 1.2em;
                height: 1.2em;
                background: $offColor;
                transform: rotate(45deg);
                position: absolute;
                left: -.6em;
                top: .6em;
                z-index: 10000;
            }
        }
        .workpackage-infos {
            transition: all .3s;
            position: relative;
            padding-left: 1em;
            &:before {
                transition: all .3s;
                content: " ";
                width: 1em;
                height: 1em;
                background: lighten($offColor, 30%);
                transform: rotate(45deg);
                position: absolute;
                left: -.6em;
                top: .5em;
                z-index: 10000;
            }
        }
        &.selected, &:hover {
            background: lighten($baseColor, 60%);
            border: thin $baseColor solid;
            color: $baseColor;
            .project-acronym {
                color: lighten($baseColor, 40%);
                background: $baseColor;
            }
            .activity-label {
                background: lighten($baseColor, 30%);
                color: darken($baseColor, 30%);
                &:before {
                    background: $baseColor;
                }
            }
            .workpackage-infos {
                &:before {
                    background: lighten($baseColor, 30%);
                }
            }
        }

        &.horslots-item {
            .workpackage-infos {
                flex: 1;
            }
            .activity-label:before {
                content: none;
            }
            .workpackage-infos:before {
                background: $offColor;
                top: .8em;
            }

            &.selected, &:hover {
                .workpackage-infos {
                    &:before {
                        background: $baseColor;
                    }
                }
            }
        }

        &.selected {
            box-shadow: .25em 0 .5em rgba($baseColor, .8);
            .project-acronym {
                &:before {
                    background: rgba(white, 1);
                }
            }
        }
    }
</style>

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