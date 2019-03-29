<template>
    <div class="container">
        <transition name="fade">
            <div class="overlay" v-if="displayed">
                <div class="overlay-content">

                    <div class="row" style="width: 80%">
                        <h1 class="col-md-6 col-md-offset-3">
                            <small>Options de copie pour</small> <br>

                            <strong>{{ activity.label }}</strong></h1>

                        <div class="col-md-6 col-md-offset-3">
                            <div class="list-group-item separator-bottom">
                                <strong>
                                    <i class="icon-group"></i>
                                    Reprendre les personnes
                                </strong>
                                <div class="material-switch pull-right">
                                    <input id="keeppersons" name="keeppersons" type="checkbox" v-model="keepPersons"/>
                                    <label for="keeppersons" class="label-primary"></label>
                                </div>
                            </div>

                            <div class="list-group-item separator-bottom">
                                <strong>
                                    <i class="icon-calendar"></i>
                                    Reprendre les jalons
                                </strong>
                                <div class="material-switch pull-right">
                                    <input id="keepmilestones" name="keepmilestones" type="checkbox" v-model="keepMilestones"/>
                                    <label for="keepmilestones" class="label-primary"></label>
                                </div>
                            </div>

                            <div class="list-group-item separator-bottom">
                                <strong>
                                    <i class="icon-building-filled"></i>
                                    Reprendre les organisations
                                </strong>
                                <div class="material-switch pull-right">
                                    <input id="keeporganizations" name="keeporganizations" type="checkbox" v-model="keepOrganizations"/>
                                    <label for="keeporganizations" class="label-primary"></label>
                                </div>
                            </div>

                            <div class="list-group-item separator-bottom">
                                <strong>
                                    <i class="icon-archive"></i>
                                    Reprendre les lots de travail
                                </strong>

                                <div class="material-switch pull-right">
                                    <input id="keepworkpackages" name="keepworkpackages" type="checkbox" v-model="keepWorkpackages"/>
                                    <label for="keepworkpackages" class="label-primary"></label>
                                </div>
                            </div>

                            <div class="list-group-item separator-bottom">
                                <strong>
                                    <i class="icon-archive"></i>
                                    Reprendre les données administrative
                                </strong>

                                <div class="material-switch pull-right">
                                    <input id="keepadmdata" name="keepadmdata" type="checkbox" v-model="keepAdmData"/>
                                    <label for="keepadmdata" class="label-primary"></label>
                                </div>
                            </div>

                            <nav class="text-center">
                                <a href="#" class="btn btn-primary" @click="displayed = false">Annuler</a>
                                <a href="#" class="btn btn-default" @click="performSend()">Dupliquer l'activité</a>
                            </nav>
                        </div>
                    </div>


                </div>
            </div>
        </transition>
    </div>
</template>
<script>

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  Activityclone --filename.js Activityclone.js --dist public/js/oscar/dist public/js/oscar/src/Activityclone.vue

    export default {
        props: {
            activity: { required: true },
            urlPost: { required: true }
        },

        data() {
            return {
                displayed: false,
                keepPersons: true,
                keepOrganizations: true,
                keepMilestones: true,
                keepWorkpackages: false,
                keepAdmData: false
            }
        },

        methods: {
            performSend(){
                document.location = this.urlPost +"?"
                        + (this.keepPersons ? '&keeppersons=on': '')
                        + (this.keepOrganizations ? '&keeporganizations=on': '')
                        + (this.keepMilestones ? '&keepmilestones=on': '')
                        + (this.keepWorkpackages ? '&keepworkpackage=on': '')
                        + (this.keepAdmData ? '&keepadmdata=on': '');

            }
        }
    }
</script>