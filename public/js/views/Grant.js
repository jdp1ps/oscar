/**
 * Created by jacksay on 14/09/15.
 */
/**
 * Permet de gérer une source de financement (Contrat)
 * Created by jacksay on 14/09/15.
 */
(function(root, $, _, Backbone, Handlebars){
    'use strict';

    console.log(root);

    root.Oscar = root.Oscar || {};
    root.Oscar.View = root.Oscar.View || {};


    var GrantModelView, GrantCollectionView, GrantModalView;

    GrantModalView = Backbone.View.extend({
        className: 'modal',
        template: Handlebars.compile($('#tplGrantModal').html()),
        events: {
            'click .btnSave': 'save'
        },
        save: function(){


            var grant = this.collection.add({
                amount: $('.amount', this.$el).val(),
                idsource: $('.idsource', this.$el).val(),
                idtype: $('.idtype', this.$el).val()
            });
            grant.save();

        },
        render: function(){
            this.$el.html(this.template({
                label: this.label,
                grant: this.model.toJSON()
            }));
            return this;
        },
        setData: function(label, collection, model){
            this.label = label;
            this.model = model;
            this.collection = collection;
            return this;
        },
        show: function(){
            this.$el.modal('show');
            return this;
        }
    });

    /**
     * Vue pour UN contrat.
     */
    GrantModelView = Backbone.View.extend({
        tagName: 'article',
        className: 'card',
        template: Handlebars.compile($('#tplGrant').html()),
        events: {
            'click .btnDelete': 'handleDelete',
            'click .btnEdit': 'handleEdit'
        },

        initialize: function()
        {
            this.$el.addClass('status-' + this.model.get('status'));
        },

        handleDelete: function(e)
        {
            console.info('DELETE Contract', this.model.get('id'));
            this.model.destroy();
            //this.model.collection.remove(this.model.get('id'));
        },

        handleEdit: function(e)
        {
            console.log('EDIT');
        },

        render: function(){
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });

    GrantCollectionView = Backbone.View.extend({

        $content: null,
        events: {
            'click .btnAdd' : 'create'
        },

        getModal: function(){
            if( !this.modal ){
                this.modal = new GrantModalView();
                $('body').append(this.modal.el);
            }
            return this.modal;
        },

        create: function()
        {
            this.getModal().setData("Nouveau contrat", this.model, new Oscar.Model.GrantModel()).render().show();
        },

        initialize: function(){
            console.log('Create instance of Oscar.View.GrantCollectionView', this);
            this.listenTo(this.model, 'sync', this.render);
            this.$content = this.$('.content', this.el);
        },

        render: function(){
            this.$content.html(this.model.length +' contrat(s)');
            this.model.forEach(function(grant){
                this.$content.append(new GrantModelView({model: grant}).render().el);
            }.bind(this));
            return this;
        }
    });

    root.Oscar.View.GrantModelView = GrantModelView;
    root.Oscar.View.GrantCollectionView = GrantCollectionView;

})(this, jQuery, _, Backbone, Handlebars);
