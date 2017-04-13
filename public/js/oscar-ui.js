/**
 * Created by jacksay on 16-12-22.
 */
define('OscarUI', ["jquery", "bootbox"], function($, bootbox){
   "use strict";

    var domTooltip;

    var OscarUI = {
        showTooltip: function(htmlContent, mouseEvent){
            console.log(mouseEvent);
            if( !domTooltip ){
                domTooltip = $('<div class="oscar-tooltip">DEFAULT</div>');
                $('body').append(domTooltip);
                domTooltip.on('mouseleave', OscarUI.hideTooltip);
            }
            domTooltip.html(htmlContent).fadeIn(250).css({
                position: 'fixed',
                top: mouseEvent.clientY - 8,
                left: mouseEvent.clientX - 8
            });
        },
        hideTooltip: function(){
            if( domTooltip ){
                domTooltip.fadeOut(100);
            }
        }
    };

    return OscarUI;
});