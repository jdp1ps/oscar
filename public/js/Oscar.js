/**
 * Created by jacksay on 02/02/16.
 */
define('Oscar', ['Backbone', 'hbs', 'bootbox'], function(Backbone, Handlebars, bootbox){

    'use strict';

    var Oscar = {};

    Oscar.messenger = $('#oscar-messenger');
    if( !Oscar.messenger.length ){
        console.log('Create global messenger');
        Oscar.messenger = $('<div id="oscar-messenger" style="display: none">'+
            '<div class="container">' +
            '<a href="#" class="close"><i class="icon-cancel-outline"></i></a>' +
            '<div class="content">>Message oscar</div>' +
            '</div></div>');
        $('body').append(Oscar.messenger);
        Oscar.messenger.on('click', '.close', function(e){
            Oscar.waitScreen(null);
            e.preventDefault();
        });
    }

    Oscar.waitScreen = function( content, type ){
        var type = (type || 'info');
        console.log(type);
        Oscar.messenger.find('.container').removeClass('error info warning success').addClass(type);

        if( content === null ){
            Oscar.messenger.stop().fadeOut();
        } else {
            Oscar.messenger.stop().fadeIn().find('.content').html(content);
        }
    };




    // -------------------------------------------------------------------------
    // Views
    Oscar.GenericItemView = Backbone.View.extend({
        events: {
            'click .btn-delete': 'processDelete',
            'click .btn-edit': 'processEdit'
        },

        options: {

        },

        initialize: function( args ){
            if( args.options ){
                this.options = _.extend(this.options, args.options);
            }
        },

        /**
         * Suppression d'une donnée.
         * @param e
         */
        processDelete: function( e ){
            bootbox.confirm('Supprimer ?', function(response){
                if( response ){
                    this.model.destroy({ wait: true });
                }
            }.bind(this));
            e.preventDefault();
        },

        processEdit: function( e ){
            console.log('Process edit');
            var model = this.model;


            if( this.model.collection.urlEdit ){
                require(['modalform'], function(ModalForm){

                    // Données
                    var url = this.model.collection.urlEdit +this.model.get('id')
                        , modal = ModalForm.modal()
                        , modalContainer = modal.container
                        , modalTitle = modal.title
                        , modalContent = modal.content;

                    // On supprime l'ancien contenu de la modale
                    modalContent.empty().unbind();

                    var jqxhr = $.ajax({
                        'type': 'GET',
                        'url': url
                    }).done(function(content){
                        var title = "Modification"
                            , $title
                            , modalContent = $(content);

                        if(modalContent && ($title = modalContent.find('h1')) ){
                            title = $title.html();
                            //modalContent.find('h1').remove();
                        }

                        ModalForm.show(title, modalContent);

                        modalContent.on('click', '.button-back', function(e){
                            e.preventDefault();
                            ModalForm.hide();
                        });

                        modalContent.on('click', '[type="submit"]', function(e){
                            e.preventDefault();
                            var form = $('form', modalContent)
                                , formMethod = (form.attr('method') || 'get')
                                , urlPost = (form.attr('action') || url) ;

                            require(['jquery-serialize'], function(){
                                var datas = $('form', modalContent).serializeObject();
                                console.log('Send', datas, 'to', urlPost);
                                $.ajax({
                                    'url': urlPost,
                                    'method': formMethod,
                                    'data': datas
                                }, datas).done(function(content){
                                    modalContent.html(content);
                                    model.collection.fetch();
                                }).fail(function(){
                                    Oscar.waitScreen('Erreur lors du traitement des données', 'error');
                                });
                            });
                        });
                    }).fail(function( xhr, status, response){
                        var title = 'Erreur Oscar',
                            content = 'Le serveur à retourné une erreur non-identifiée';
                        if( xhr.status === 400 ){
                            title = 'Erreur de saisie';
                            content = "Votre requète n'a pas été traitée !";
                        }
                        if( xhr.responseJSON && xhr.responseJSON.error ){ content = xhr.responseJSON.error;}
                        Oscar.waitScreen('<h1><i class="icon-attention-1"></i>' + title + '</h1>' + content, 'error');
                    });
                    jqxhr.always(function(){console.log('always()', arguments)});
                }.bind(this));
            }
            e.preventDefault();
        },

        /**
         * Rendu
         * @returns {Oscar.GenericItemView}
         */
        render: function(){
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });

    Oscar.GenericCollectionView = Backbone.View.extend({
        events: {
            'click .btn-delete': 'processDelete'
        },

        create: function(){
            console.log("Procédure d'ajout");
        },

        formNew: function(){
            if( this.model.urlNew ){
                this.modalUrl(this.model.urlNew, 'Nouveau');
            }
        },

        modalUrl: function(url, title){
            var model = this.model;
            require(['modalform'], function(ModalForm){
                var modal = ModalForm.modal()
                    , modalContainer = modal.container
                    , modalTitle = modal.title
                    , modalContent = modal.content;

                // On supprime l'ancien contenu de la modale
                modalContent.empty().unbind();

                var jqxhr = $.ajax({
                    'type': 'GET',
                    'url': url
                }).done(function(content){
                    var title = (title || "")
                        , $title
                        , modalContent = $(content);

                    if(modalContent && ($title = modalContent.find('h1')) ){
                        title = $title.html();
                        //modalContent.find('h1').remove();
                    }

                    ModalForm.show(title, modalContent);

                    modalContent.on('click', '.button-back', function(e){
                        e.preventDefault();
                        ModalForm.hide();
                    });

                    modalContent.on('click', '[type="submit"]', function(e){
                        e.preventDefault();
                        var form = $('form', modalContent)
                            , formMethod = (form.attr('method') || 'get')
                            , urlPost = (form.attr('action') || url) ;

                        require(['jquery-serialize'], function(){
                            var datas = $('form', modalContent).serializeObject();
                            $.ajax({
                                'url': urlPost,
                                'method': formMethod,
                                'data': datas
                            }, datas).done(function(content){
                                modalContent.html(content);
                                model.fetch();
                            }).fail(function(){
                                Oscar.waitScreen('Erreur lors du traitement des données', 'error');
                            });
                        });
                    });
                }).fail(function( xhr, status, response){
                    var title = 'Erreur Oscar',
                        content = 'Le serveur à retourné une erreur non-identifiée';
                    if( xhr.status === 400 ){
                        title = 'Erreur de saisie';
                        content = "Votre requète n'a pas été traitée !";
                    }
                    if( xhr.responseJSON && xhr.responseJSON.error ){ content = xhr.responseJSON.error;}
                    Oscar.waitScreen('<h1><i class="icon-attention-1"></i>' + title + '</h1>' + content, 'error');
                });

            }.bind(this));
        },

        form: function(model){
            console.log('Formulaire generic');


            /****
            require(['modalform'], function(ModalForm){

                // Données
                var url = this.model.collection.urlEdit +this.model.get('id')
                    , modal = ModalForm.modal()
                    , modalContainer = modal.container
                    , modalTitle = modal.title
                    , modalContent = modal.content;

                // On supprime l'ancien contenu de la modale
                modalContent.empty().unbind();

                var jqxhr = $.ajax({
                    'type': 'GET',
                    'url': url
                }).done(function(content){
                    var title = "Modification"
                        , $title
                        , modalContent = $(content);

                    if(modalContent && ($title = modalContent.find('h1')) ){
                        title = $title.html();
                        //modalContent.find('h1').remove();
                    }

                    ModalForm.show(title, modalContent);

                    modalContent.on('click', '.button-back', function(e){
                        e.preventDefault();
                        ModalForm.hide();
                    });

                    modalContent.on('click', '[type="submit"]', function(e){
                        e.preventDefault();
                        var form = $('form', modalContent)
                            , formMethod = (form.attr('method') || 'get')
                            , urlPost = (form.attr('action') || url) ;

                        require(['jquery-serialize'], function(){
                            var datas = $('form', modalContent).serializeObject();
                            console.log('Send', datas, 'to', urlPost);
                            $.ajax({
                                'url': urlPost,
                                'method': formMethod,
                                'data': datas
                            }, datas).done(function(content){
                                modalContent.html(content);
                                model.collection.fetch();
                            }).fail(function(){
                                Oscar.waitScreen('Erreur lors du traitement des données', 'error');
                            });
                        });
                    });
                }).fail(function( xhr, status, response){
                    var title = 'Erreur Oscar',
                        content = 'Le serveur à retourné une erreur non-identifiée';
                    if( xhr.status === 400 ){
                        title = 'Erreur de saisie';
                        content = "Votre requète n'a pas été traitée !";
                    }
                    if( xhr.responseJSON && xhr.responseJSON.error ){ content = xhr.responseJSON.error;}
                    Oscar.waitScreen('<h1><i class="icon-attention-1"></i>' + title + '</h1>' + content, 'error');
                });
                jqxhr.always(function(){console.log('always()', arguments)});
            }.bind(this));  /****/
        },

        initialize: function(){
            //this.listenTo(this.model, 'all', function(){
            //    console.log("GenericCollectionView event", arguments[0]);
            //}.bind(this));

            // Fin de la requête
            this.listenTo(this.model, 'sync update', function(){
                this.render();
            }.bind(this));

            // Début de la requête
            this.listenTo(this.model, 'error', function(data, response){
                // Message d'erreur
                var errorMsg = "Erreur";
                if( response && response.responseJSON && response.responseJSON.error ){
                    errorMsg = response.responseJSON.error;
                }
                this.errorShow(errorMsg);
            }.bind(this));

            // Début de la requête
            this.listenTo(this.model, 'request', function(){
                this.loadingStart();
            }.bind(this));

            this.$content = this.$el.find('.lc-content');
        },

        loadingStop: function(){
            this.$el.removeClass('pending');
        },

        loadingStart: function(){
            this.$el.addClass('pending');
        },

        errorShow: function( msg ){
            this.loadingStop();
            var text = (msg || 'Error !');
            this.$el.addClass('error');
            this.$el.find('.lc-alert').html(text);
        },

        render: function(){
            this.loadingStop();
            this.$content.html('');
            this.model.each( function(item){
                var itemView = new this.itemClass({
                    model: item
                });

                this.$content.append(itemView.render().$el);
            }.bind(this));
            return this;
        }
    });
    return Oscar;
});


