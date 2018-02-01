<template>
    <div class="unicaen autocompleter">
        <i :class="icon"></i>
        <input type="text" v-model="content">
        <ul v-show="proposal.length > 0" class="proposals">
            <li v-for="p in proposal" class="proposal">
                <i :class="icon"></i>
                {{ p.label }}
                <small v-if="p.closed">Cette organisation n'existe plus / est fermée</small>
            </li>
        </ul>
        <small class="pending-msg" v-show="pending">Recherche...</small>

    </div>
</template>

<script>
    export default {

        props: {
          'icon': {
              default: 'icon-pencil'
          },
          'url': String
        },

        data() {
            return {
                pending: false,
                content: "",
                proposal: []
            }
        },

        watch: {
            content(newValue, oldValue){
                console.log(oldValue, newValue);
                if( newValue.length >= 3 ){
                    this.search(newValue.toLowerCase());
                } else {
                    this.proposal = [];
                }
            }
        },

        methods:{
            search(){
                this.content, this.$http.get(this.url + this.content).then(
                    (resolve) => {
                        this.proposal = resolve.body.datas;
                        this.pending = false;
                    },
                    (fail) => {
                        console.log('ERROR', fail);
                        this.error = fail;
                        this.pending = false;
                    }
                );
            }
        }
    }
</script>