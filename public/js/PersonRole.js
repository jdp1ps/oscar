/**
 * Created by jacksay on 16-10-06.
 */
define('PersonRole', ['Backbone', 'hbs', 'text!templates/personrole.hbs'], function(Backbone, Handlebars, tpl){

    var PersonRole = {
        version: 1.0,
        author: "Stéphane Bouvry<stephane.bouvry@unicaen.fr>",
        template: Handlebars.compile(tpl)
    };

    PersonRole.ListView = Backbone.View.extend({

        template: null,

        initialize: function(){
            this.listenTo(this.collection, 'update', this.render);
        },

        render: function(){
            this.$el.html("<h1>Rôles</h1>");
            this.collection.forEach(function(item){
               this.$el.append(new PersonRole.View({
                   model: item
               }).render().$el);
            }.bind(this));
            return this;
        }

    });

    PersonRole.Model = Backbone.Model.extend({
        defaults: {
            start: null,
            end: null,
            role: "",
            organization: "",
            organization_id: null
        },

        initialize: function(opt){
            console.log(opt);
        }
    });

    PersonRole.View = Backbone.View.extend({
        model: PersonRole.Model,
        render: function(){
            this.$el.html(PersonRole.template(this.model.toJSON()));
            return this;
        }
    });

    PersonRole.Collection = Backbone.Collection.extend({
        model: PersonRole.Model
    });

    return PersonRole;
});