<template>
    <div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span v-if="hasSelected" class="info">
                    <i :class=" selectedIcon ? 'icon-' +selectedIcon : 'icon-archive'"></i>
                    <strong>{{selectedCode}}</strong> <em>{{ selectedLabel }}</em><br/>
                    <small class="text-light">{{ selectedDescription }}</small>
                </span>
                <em v-else class="info">Lot de travail/Activité...</em>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li v-for="wp in workpackages"
                    :class="{ 'selected' : selection == wp, 'disabled': !wp.validation_up }"
                    :title="wp.validation_up ? '': 'Vous ne pouvez pas ajouter de créneau pour ce lot'">

                    <a href="#" @click.prevent="handlerSelectWP(wp)">
                        <i class="icon-archive"></i>
                        <abbr :title="wp.project">[{{wp.acronym}}]</abbr>
                        <strong>{{wp.code}}</strong> <em>{{ wp.label }}</em><br/>
                        <small class="text-light">{{ wp.description }}</small>
                    </a>
                </li>
                <li role="separator" class="divider"></li>
                <li v-for="o in others" :class="{ 'selected' : selection == o, 'disabled': !o.validation_up }">
                    <a href="#" @click.prevent="handlerSelectOther(o)">
                        <i :class="'icon-' +o.code"></i>
                        {{ o.label }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped lang="scss">
    .dropdown {
        color: black;
        font-size: 1em;
        .selected a {
            background-color: #5c9ccc;
            text-shadow: -1px 1px 0 rgba(255,255,255,.25);
        }
        li {
            a {
                padding: 2px 4px;
                background-color: white;
                transition: background-color ease-out .3s;
            }
            &:hover a {
                background-color: lighten(#5c9ccc, 20%);
            }
        }

    }
    button {
        display: flex;
        text-align: left;
        padding: 2px 4px;
        line-height: 1em;
        align-items: center;
        align-items: center;
        .info {
            padding-right: 4px;
        }
        small {
            font-size: .8em;
        }
    }
</style>

<script>
    export default {
        props: {
            workpackages: { default: [] },
            selection: { required:true },
            others: { required: true }
        },

        data(){
            return {

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
                console.log('Selection WP');
                this.selection = wp;
                this.$emit('select', this.selection);
            },
            handlerSelectOther(wp){
                console.log('Selection OTHER');
                this.selection = wp;
                this.$emit('select', this.selection);
            }
        }
    }
</script>