/**
 * Created by jacksay on 17-01-09.
 */
define(['vue', 'vue-resource', 'mm', 'text!templates/depenses-list.vue'], function(Vue, VueResource, moment, tpl){

    Vue.use(VueResource);

    // Formater
    var dateFull= function( date ){ return dateSince(date) +', ' +dateFrom(date) },
        dateSince= function( date ){ return !date ? "Pas de date" : moment(date).format('dddd Do MMMM YYYY')},
        dateFrom= function( date ){ return !date ? "" : moment(date).fromNow()}


    var OscarDepenses = Vue.extend({
        template: tpl,

        // ---------------------------------------------------------------------
        filters: {
            money: function( d ){
                var total = d.value.toFixed(2);
                return d ? total + ' ' + d.currency : "Non spécifié"
            },
            dateFull: dateFull,
            dateSince: dateSince,
            dateFrom: dateFrom
        },
        data: function(){
            return {
                loading: false,
                depenses: []
            }
        },
        computed: {
            total: function(){
                var total = 0.0;
                this.depenses.forEach(function(d){
                   total += (d.amount.value * d.amount.rate)
                }.bind(this));
                return total
            }
        },
        methods: {
            fetch: function(){
                this.loading = true;
                var self = this;
                this.$http.get(this.url).then(function(response){
                    self.depenses = response.body;
                }, function(error){
                    flashMessage('error', 'Impossible de charger les dépenses. ' + error.status +' : ' +error.statusText);
                }).then(function(){
                    self.loading = false;
                })
            }
        },
        created: function(){
            this.fetch();
        }
    });
    return OscarDepenses;
});
