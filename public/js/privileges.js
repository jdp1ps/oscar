/**
 * Created by jacksay on 26/02/16.
 */
requirejs(['jquery', 'unicaen'], function($){
    requirejs(['jqueryui-widget'], function(w){
        requirejs(['domReady'], function (domReady){
            domReady(function(){
                require(['util', 'bootstrap'], function(){
                    console.log('Year');
                    $.widget("ose.droitsTbl", {

                        modifier: function (td, action)
                        {
                            var that = this;
                            td.html("<div class=\"loading\">&nbsp;</div>");
                            td.load(this.element.data('modifier-url'), {
                                role: td.data("role"),
                                statut: td.data("statut"),
                                privilege: td.data("privilege"),
                                action: action
                            }, function ()
                            {
                                that.initModifierClick(td); // pour reconnecter l'action du lien...
                            });

                        },


                        initModifierClick: function (td)
                        {
                            console.log('initModifierClick', td);
                            var that = this;
                            td.find("a").on("click", function ()
                            {
                                that.modifier(td, $(this).data("action"));
                            });
                        },

                        _create: function ()
                        {
                            var that = this;
                            this.element.find("td.modifier").each(function ()
                            {
                                that.initModifierClick($(this));
                            });
                        }
                    });
                    console.log(WidgetInitializer);
                    WidgetInitializer.add('droits-tbl', 'droitsTbl');

                });
            });
        })
    })
}, function(err){
    console.error(err);
});