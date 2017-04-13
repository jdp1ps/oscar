/**
 * Created by jacksay on 08/12/16.
 */
define(['Backbone', 'hbs'], function (Backbone, Handlebars) {
    var Enroll = {
        version: "0.0.1"
    };

    ///////////////////////////////////
    // MODEL
    //

    Enroll.Model = Backbone.Model.extend({
        defaults: {

            // Identifiant de la jointure
            id: 0,

            // Intitulé de l'enrollé (par exemple la personne)
            enrolledLabel: "",

            // Identifiant de l'enrollé (par exemple, l'ID de la personne)
            enrolled: 0,

            // Rôle (ID)
            role: "",

            // Rôle (intitulé)
            roleLabel: "",

            // Contexte (par exemple, l'activité)
            enrollerLabel: "",

            // Context ID (par exemple ID de l'activité)
            enroller: 0,

            // Début
            start: null,

            // Fin
            end: null
        }
    });

    Enroll.Collection = Backbone.Collection.extend({
        model: Enroll.Model
    });

    /////////////////////////////////////////////////////
    // VIEW

    Enroll.View = Backbone.View.extend({
        render: function(){
            this.$el.html('M:' +
                this.model.get('enrolledLabel') +
                " - " +
                this.model.get('roleLabel')
            );
            return this;
        }
    });

    Enroll.CollectionView = Backbone.View.extend({

        aggregator: 'role',
        aggregateTo: 'enrolled',


        initialize: function(opt){
            this.tpl = opt.tpl;
            this.listenTo(this.collection, 'update', this.render);
        },

        changeStack: function (aggregator, aggregateTo){
            this.aggregator = aggregator;
            this.aggregateTo = aggregateTo;
            this.render();
        },

        render: function(){
            this.$el.empty();
            var aggregat = {};
            var objIdUsed = [];

            this.collection.forEach(function(e){

                var idAggregat = e.get(this.aggregator);
                if( !aggregat[idAggregat] ){
                    objIdUsed[idAggregat] = [];
                    aggregat[idAggregat] = {
                        label: e.get(this.aggregator+"Label"),
                        roles: []
                    };
                }
                if( objIdUsed[idAggregat].indexOf(e.get(this.aggregateTo)) < 0 ) {
                    aggregat[idAggregat].roles.push({
                        label: e.get(this.aggregateTo + 'Label'),
                        id: e.get('id'),
                        urlRemove: "todo?",
                        objId: e.get(this.aggregateTo),
                        objLabel: e.get(this.aggregateTo + "Label")
                    });
                    objIdUsed[idAggregat].push(e.get(this.aggregateTo));
                }
            }.bind(this));

            _.each(aggregat, function(stack){
                this.$el.append(this.tpl(stack));
            }.bind(this));

            return this;
        }
    });


    return Enroll;
});
