<template>
    <div class="">
        <transition name="fade">
            <div v-if="showUI" class="overlay">
                <div class="overlay-content">
                    <div class="row">
                        <div class="col-md-6">
                            {{ persons }}
                            <ul>
                                <li>Personnes</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            Détails
                        </div>
                    </div>
                    <hr />
                    INFOS ICI
                    <button class="btn btn-danger" @click="showUI=false">Annuler</button>
                </div>
            </div>
        </transition>
        <div class="btn-group-sm btn-group">
            <a href="#" @click.prevent="manageReferent"class="btn btn-sm btn-primary"><i class="icon-cw-outline"></i>Gérer les référents</a>
        </div>
    </div>

</template>
<script>
    // nodejs ./node_modules/.bin/poi watch --format umd --moduleName  ReferentUI --filename.js ReferentUI.js --dist public/js/oscar/dist public/js/oscar/src/ReferentUI.vue

    import PersonAutoCompleter from "./PersonAutoCompleter";

    export default {

        components: {
            personselector: PersonAutoCompleter
        },

        props: {
            // Nom du référent
            persons: { require: true },
            url: { require: true }
        },

        data(){
            return {
                loading: false,
                datas: null,
                showUI: false
            }
        },

        methods:{
            manageReferent(){
                this.showUI = true;
                if( !this.datas ){
                    this.fetch();
                }
            },
            fetch(){
                this.$http.get(this.url).then(
                    ok => {

                    },
                    ko => {

                    }
                );
            }
        }
    }
</script>