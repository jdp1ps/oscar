define('Payments', ['Oscar', 'Backbone', 'hbs', 'text!templates/payment-item.hbs'], function(Oscar, Backbone, Handlebars, itemTpl){
    var Payments = {};

    ////////////////////////////////////////////////////////////////////////////
    //
    // MODEL
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Décrit une échéance.
     */
    Payments.Model = Backbone.Model.extend({
        defaults: function(){
            return {
                'date': null,
                'type': 'Type inconnu'
            };
        },
    });

    /**
     * Collection d'échéance
     */
    Payments.Collection = Backbone.Collection.extend({
        model: Payments.Model
    });

    ////////////////////////////////////////////////////////////////////////////
    //
    // VIEWS
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Vue d'une échéance (de la liste).
     */
    Payments.ViewItem = Oscar.GenericItemView.extend({
        template: Handlebars.compile(itemTpl)
    });

    /**
     * Vue pour la liste des échéance
     */
    Payments.View = Oscar.GenericCollectionView.extend({
        itemClass: Payments.ViewItem,

        render: function(){
            this.loadingStop();
            this.$content.html('');
            var status = null;
            this.model.each( function(item){
                if( item.get('status') !== status ){
                    status = item.get('status');
                    this.$content.append('<h4>' +Payments.getStatus(status) +'(s)<h4>');
                }
                var itemView = new this.itemClass({
                    model: item
                });

                this.$content.append(itemView.render().$el);
            }.bind(this));
            return this;
        }
    });

    Payments.getStatus = function( id ){
        if( !Payments.status ){
            Payments.status = {
                1: "Prévisionnel",
                2: "Réalisé",
                3: "Écart"
            }
        }
        return Payments.status[id];
    }

    return Payments;
});

