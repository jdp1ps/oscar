/**
 * Created by jacksay on 09/10/15.
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory); // Utiliser l'AMD
    } else {
        factory(jQuery); // pas d'AMD
    }
}(function ($) {
    // MODE LAZY
    $(function () {
        require(['bootbox'], function(bootbox) {
            $('body').on('click', '[data-confirm]', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var url = $(this).data('href');
                if( !url ){
                    url = $(this).attr('href');
                }
                bootbox.confirm($(this).data('confirm'), function(response){
                    if( response ){
                        document.location = url;
                    }
                });
            });
        });

        ////////////////////////////////////////////////////////////////////////
        var menu = $('#oscar-context'), menuVisible = false, $win = $(window);

        $("#user-current-info").popover({ html: true, container: '#navbar' });

        // Création du dropdown vide
        if( !menu.length ){
            menu = $('<ul class="dropdown-menu" style="display: none" id="oscar-context" role="menu"></ul>');
            $('body').append(menu);
            $('body').on('click', function(){
                menuVisible = false;
                menu.hide();
            });
            menu.on('click', '[data-action]', function(){
                var ids = [];
                $('[data-selectable].selected').each(function(i,item){
                    ids.push($(item).data('id'));
                });
                document.location = $(this).data('action') +'?ids=' +ids;
            });
        }


        $('body').on('contextmenu', '.member, .organization', function(e){

            var items = [], $el = $(this);

            if( $el.is('[data-move-to-project]') ){
                items.push({label: '<i class="icon-loop-outline"></i> Déplacer ce rôle dans le projet', action: $el.data('move-to-project')});
            }

            if( $el.is('[data-show]') ){
                var icon = $el.is('.member') ? 'icon-user' : 'icon-building-filled';
                items.push({label: '<i class="' +icon + '"></i> Voir la fiche', action: $el.data('show')});
            }

            menu.empty();

            if( items.length ){
                items.forEach(function(item){
                    menu.append('<li><a href="#" data-action="'+item.action+'">' + item.label + '</a></li>');
                });
                menu.stop()
                    //.show()
                    .css({
                        display: 'block',
                        position: "absolute",
                        left: e.clientX+$win.scrollLeft(),//getMenuPosition(e.clientX, 'width', 'scrollLeft'),
                        top: e.clientY + $win.scrollTop()//getMenuPosition(e.clientY, 'height', 'scrollTop')
                    });
                menuVisible = true;
                e.preventDefault();
                e.stopImmediatePropagation();
            }
            else {
                menuVisible = false;
                menu.hide();
            }
        });

        // Menu contextuel
        $('body').on('contextmenu', '[data-selectable].activity', function(e){

            var items = [];

            if( $('[data-selectable].selected').length ){
                items.push({label: 'Exporter en CSV', action: '/activites-de-recherche/csv'});
            }

            menu.empty();

            if( items.length ){
                items.forEach(function(item){
                    menu.append('<li><a href="#" data-action="'+item.action+'">' + item.label + '</a></li>');
                });
                menu.show()
                    .css({
                        position: "absolute",
                        left: e.clientX+$win.scrollLeft(),//getMenuPosition(e.clientX, 'width', 'scrollLeft'),
                        top: e.clientY + $win.scrollTop()//getMenuPosition(e.clientY, 'height', 'scrollTop')
                    });
                menuVisible = true;
                e.preventDefault();
            }
            else {
                menuVisible = false;
                menu.hide();
            }
        });


        var $lazyLoad = $('[data-lazy]');
        if ($lazyLoad.length) {
            require(['views/ProjectListItemView'], function (ProjectListItemView) {
                $lazyLoad.each(function (i, $el) {
                    var v = new ProjectListItemView({
                        el: $el
                    });
                });
            });
        }

        $('body').on('click', '[data-selectable]', function(e){
           $(this).toggleClass('selected');
        });

        var $openClosed = $('[data-openable]');
        if ($openClosed.length) {
            $openClosed.each(function(i, $el){
                var $section = $('>.content', $el);
                $($el).on('click', '>.handler [data-opener]', function(){
                    if( !$section.children().length ){
                        return;
                    }
                    $section.toggle();

                });
            });
        }


        var $dater = $('.input-date');
        if( $dater.length ){
            require(['datepicker'], function(){
                $dater.datepicker({
                    format: "yyyy-mm-dd",
                    todayBtn: "linked",
                    clearBtn: true,
                    language: "fr",
                    autoclose: true,
                    toggleActive: true
                });
            });
        }
    });
}));