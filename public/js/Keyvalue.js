/**
 * Created by jacksay on 16-10-13.
 */
define('Keyvalue', ['Oscar', 'Backbone', 'hbs', 'jquery', 'bootbox'], function(Oscar, Backbone, Handlebars, $, Bootbox){

    var view = Backbone.View.extend({
        events: {
            'click .btn-add': 'handlerNew',
            'click .btn-delete': 'handlerRemove',
        },
        template: null,


        handlerNew: function(e){
            var bb = Bootbox.prompt('Intitulé ?', function(key){
                if(key){
                    this.$el.find('.keyvalue-lines').append(this.template({
                        'key': key,
                        'value': ''
                    }));
                }
            }.bind(this));
            bb.init(function(){
                console.log(bb.find('.bootbox-input').attr('list', 'keyvalue_list').attr('autocomplete', 'on'));
            });
            e.preventDefault();
        },

        handlerRemove: function(e){
            var btn = $(e.target);
            btn.parent().remove();
            e.preventDefault();
        },

        initialize: function(){
            this.template = Handlebars.compile(this.$el.data('template'));
        }
    });

    var $elements = $('.keyvalue-widget');

    if( $elements.length ){
        $elements.each(function(i, el){
            var v = new view({
                'el': el
            });
        });
    }

    return {
        'version': 1.0
    };
});
