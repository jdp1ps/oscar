<template>
    <div class="oscar-growl">
        <section v-show="show">
            <transition-group name="list" tag="article">
                <article v-for="err in unreadErrors" class="alert alert-danger" :key="err.key">
                    <i class="icon-attention-1"></i>
                    {{ err.msg }}
                    <i class="icon-cancel-outline" @click="err.read = true"></i>
                    <pre>{{ err }}</pre>
                </article>
            </transition-group>
        </section>
    </div>
</template>
<script>
    import OscarBus from './OscarBus.js';

    export default {
        props: {

        },

        data(){
            return {
                view: ["error"],
                show: true,
                bus: OscarBus
            }
        },

        computed: {
            errors(){
                console.log("ERRORS");
                let errors = [];
                for( let i=0; i<this.bus.messages.length; i++ ){
                    if( this.bus.messages[i].type == 'error')
                        errors.push(this.bus.messages[i]);
                }
                return errors;
            },

            unreadErrors(){
                console.log("ERRORS UNREAD", this.bus.messages);
                let errors = [];
                for( let i=0; i<this.errors.length; i++ ){
                    if( this.errors[i].read != true)
                        errors.push(this.errors[i]);
                }
                return errors;
            }
        },

        mounted(){
            console.log(this.bus.id);
        }
    }
</script>
<style>
    .oscar-growl {
        position: fixed;
        top: 50px;
        right: 0;
        z-index: 10;
    }
</style>
