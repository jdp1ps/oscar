/**
 * Created by jacksay on 26/01/16.
 */
define(function(){
    var modalContainer, modalFooter, modalContent, modalTitle, ModalForm;
    ModalForm = function( $target){

        // Supprimer le comportement natif
        $target.on('click', '[data-modalform]', function( evt ){
            $.fn.modal.Constructor.prototype.enforceFocus = function () {};
            evt.preventDefault();
            ModalForm.modal();
            modalContent.empty().unbind();
            $.get($(evt.currentTarget).attr('href'), function(response, status, xhr){
                var view = $(response);
                if( view.find('h1') ){
                    modalTitle.html(view.find('h1').html());
                    view.find('h1').remove();
                }
                modalContent.html(view);

                modalContent.find('.button-back').on('click', function(e){
                    e.preventDefault();
                   modalContainer.modal('hide');
                });

                modalContainer.modal();
            });
        });
    };


    ModalForm.showForm = function( urlForm, onSuccess, onFail ){
        var modal = ModalForm.modal(),

            modalContent = modal.content;

        modalContent.empty().unbind();

        $.get(urlForm, function(response, status, xhr){
            var view = $(response);
            if( view.find('h1') ){
                modalTitle.html(view.find('h1').html());
                view.find('h1').remove();
            }
            modalContent.html(view);

            modalContent.find('.button-back').on('click', function(e){
                e.preventDefault();
                modalContainer.modal('hide');
            });

            if( onSuccess ){
                modalContent.on('click', '[type="submit"]', function(e){
                    e.preventDefault();
                    var form = $('form', modalContent)
                        , formMethod = (form.attr('method') || 'get')
                        , urlPost = (form.attr('action') || urlForm) ;

                    require(['jquery-serialize'], function(){
                        var datas = $('form', modalContent).serializeObject();
                        $.ajax({
                            'url': urlPost,
                            'method': formMethod,
                            'data': datas
                        }, datas).done(function(){
                            ModalForm.hide();
                            onSuccess();
                        }).fail(function(){
                            onFail();
                        });

                    });
                });
            }

            modalContainer.modal();
        });
    };

    ModalForm.modalify = function( link, onSuccess, onFail ){
        $(link).on('click', function( evt ){
            $.fn.modal.Constructor.prototype.enforceFocus = function () {};
            evt.preventDefault();
            var urlForm = $(evt.currentTarget).attr('href');

            ModalForm.showForm(urlForm, onSuccess, onFail);

        });
    };

    /**
     * Affiche dans une fenêtre modal le contenu de l'URL renseignée.
     *
     * @param url
     */
    ModalForm.ajaxModal = function(url){
        $.get(url, function(response, status, xhr){
            var view = $(response), modal = ModalForm.modal(), title, content;

            if( view.find('h1') ){
                title = view.find('h1').html();
                view.find('h1').remove();
            } else {
                title = '';
            }
            view.find('.button-back').on('click', function(e){
                e.preventDefault();
                modal.container.modal('hide');
            });
            content = view;
            ModalForm.show(title, content);
        });
    };

    /**
     * Alimente le contenu de la fenêtre modal et l'affiche.
     *
     * @param title Titre de la modal
     * @param content Contenu HTML de la modal
     * @returns {{title, content, container, footer}}
     */
    ModalForm.show = function(title, content){
        $.fn.modal.Constructor.prototype.enforceFocus = function () {};
        var modal = ModalForm.modal();
        modal.content.html(content);
        modal.title.html(title);
        modal.container.modal();
        return modal;
    };

    ModalForm.hide = function(){
        var modal = ModalForm.modal();
        modal.container.modal('hide');
        return modal;
    };

    /**
     * Création de la modal.
     *
     * @returns {{title: *, content: *, container: *, footer: *}}
     */
    ModalForm.modal = function(){
        if( !modalContainer ){
            modalContainer=$('<div class="modal" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel">' +
                '<div class="modal-dialog" role="document" style="min-width: 50%">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<h4 class="modal-title">TITLE</h4>' +
                '</div>' +
                '<div class="modal-body">CONTENT</div>' +
                    /*'<div class="modal-footer">FOOTER</div>' +*/
                '</div></div></div>');
            modalTitle = modalContainer.find('.modal-title');
            modalContent = modalContainer.find('.modal-body');
            modalFooter = modalContainer.find('.modal-footer');
            $('body').append(modalContainer);
        }
        return {
            title: modalTitle,
            content: modalContent,
            container: modalContainer,
            footer: modalFooter
        };
    }

    ModalForm.version = "1.0";

    return ModalForm;
});