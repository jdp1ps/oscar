/**
 * unicaen.js
 *
 * Javascript commun à toutes les applis.
 */
$(function ()
{
    /**
     * Détection de réponse "403 Unauthorized" aux requêtes AJAX pour rediriger vers
     * la page de connexion.
     */
    $(document).ajaxComplete(function (event, xhr, settings)
    {
        if (xhr.status === 403) {
            if (confirm("Votre session a expiré, vous devez vous reconnecter.\n\nCliquez sur OK pour être redirigé(e) vers la page de connexion...")) {
                var pne = window.location.pathname.split('/');
                var url = "/" + (pne[0] ? pne[0] : pne[1]) + "/auth/connexion?redirect=" + $(location).attr('href');
                $(location).attr('href', url);
            }
        }
    });

    /**
     * Installation d'un lien permettant de remonter en haut de la page.
     * Ce lien apparaît lorsque c'est nécessaire.
     */
    if ($(window).scrollTop() > 100) {
        $('.scrollup').fadeIn();
    }
    $(window).scroll(function ()
    {
        if ($(this).scrollTop() > 100) {
            $('.scrollup').fadeIn();
        }
        else {
            $('.scrollup').fadeOut();
        }
    });
    $('.scrollup').click(function ()
    {
        $("html, body").animate({scrollTop: 0}, 300);
        return false;
    });

    ajaxPopoverInit();
    AjaxModalListener.install();

    /* Utilisation du WidgetInitializer et de l'intranavigator */
    WidgetInitializer.install();
    IntraNavigator.install();
});



/**
 * Système d'initialisation automatique de widgets
 *
 */
WidgetInitializer = {
    /**
     * Liste des widgets déclarés (format [className => widgetName])
     * className = Nom de la classe CSS qui déclenche l'association
     * widgetName = Nom du widget (sans le namespace)
     */
    widgets: {},

    _initializeWidget: function (className, widgetName)
    {
        eval('$(".' + className + '").' + widgetName + '();');
    },

    /**
     * Ajoute un nouveau Widget à l'initializer
     *
     * @param string className
     * @param string widgetName
     */
    add: function (className, widgetName)
    {
        WidgetInitializer.widgets[className] = widgetName;
        this._initializeWidget(className, widgetName);
    },

    /**
     * Lance automatiquement l'association de tous les widgets déclarés avec les éléments HTMl de classe correspondante
     */
    run: function ()
    {
        for (className in this.widgets) {
            this._initializeWidget(className, this.widgets[className]);
        }
    },

    /**
     * Installe le WidgetInitializer pour qu'il se lance au chargement de la page ET après chaque requête AJAX
     */
    install: function ()
    {
        var that = this;

        this.run();
        $(document).ajaxSuccess(function ()
        {
            that.run();
        });
    }

};



IntraNavigator = {
    getElementToRefresh: function (element)
    {
        return $($(element).parents('.intranavigator').get(0));
    },

    embeds: function(element)
    {
        return $(element).parents('.intranavigator').length > 0;
    },

    add: function(element)
    {
        if (!$(element).hasClass('intranavigator')) {
            $(element).addClass('intranavigator');
            //IntraNavigator.run();
        }
    },

    waiting: function(element, message)
    {
        var msg = message ? message : 'Chargement';
        msg += ' <span class="loading">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        msg = '<div class="alert alert-success intramessage" role="alert">' + msg + '</div>';
        $(element).append(msg);
    },

    formSubmitListener: function (e)
    {
        var form = $(e.target);
        var postData = form.serializeArray(); // paramètre "modal" indispensable
        var url = form.attr('action');
        var elementToRefresh = IntraNavigator.getElementToRefresh(form);

        if (elementToRefresh) {
            // requête AJAX de soumission du formulaire
            IntraNavigator.waiting(elementToRefresh, 'Enregistrement en cours');
            $.post(url, postData, $.proxy(function (data)
            {
                elementToRefresh.html(data);
            }, this));
        }

        e.preventDefault();
    },

    innerAnchorClickListener: function (e)
    {
        var anchor = $(e.currentTarget);
        var url = anchor.attr('href');
        var elementToRefresh = IntraNavigator.getElementToRefresh(anchor);

        if (elementToRefresh && url && url !== "#") {
            // requête AJAX pour obtenir le nouveau contenu de la fenêtre modale
            IntraNavigator.waiting(elementToRefresh, 'Chargement');
            $.get(url, {}, $.proxy(function (data)
            {
                elementToRefresh.html(data);
            }, this));
        }

        e.preventDefault();
    },

    btnPrimaryClickListener: function (e)
    {
        var form = IntraNavigator.getElementToRefresh(e.target).find('form');
        if (form.length) {
            form.submit();
            e.preventDefault();
        }
    },

    /**
     * Lance automatiquement l'association de tous les widgets déclarés avec les éléments HTMl de classe correspondante
     */
    run: function ()
    {
        $('body').off("submit", ".intranavigator form", IntraNavigator.formSubmitListener);
        $('body').off("click", ".intranavigator a", IntraNavigator.innerAnchorClickListener);
        $('body').off("click", ".intranavigator .btn-primary", IntraNavigator.btnPrimaryClickListener);

        $('body').one("submit", ".intranavigator form", IntraNavigator.formSubmitListener);
        $('body').one("click", ".intranavigator a", IntraNavigator.innerAnchorClickListener);
        $('body').one("click", ".intranavigator .btn-primary", IntraNavigator.btnPrimaryClickListener);
    },

    /**
     * Installe le WidgetInitializer pour qu'il se lance au chargement de la page ET après chaque requête AJAX
     */
    install: function ()
    {
        var that = this;

        this.run();
        $(document).ajaxSuccess(function ()
        {
            that.run();
        });
    }
};



/**
 * Autocomplete jQuery amélioré :
 * - format de données attendu pour chaque item { id: "", value: "", label: "", extra: "" }
 * - un item non sléctionnable s'affiche lorsqu'il n'y a aucun résultat
 *
 * @param Array options Options de l'autocomplete jQuery +
 *                      {
 *                          elementDomId: "Id DOM de l'élément caché contenant l'id de l'item sélectionné (obligatoire)",
 *                          noResultItemLabel: "Label de l'item affiché lorsque la recherche ne renvoit rien (optionnel)"
 *                      }
 * @returns description self
 */
$.fn.autocompleteUnicaen = function(options)
{
    var defaults = {
        elementDomId: null,
        noResultItemLabel: "Aucun résultat trouvé.",
    };
    var opts = $.extend(defaults, options);
    if (!opts.elementDomId) {
        alert("Id DOM de l'élément invisible non spécifié.");
    }
    var select = function(event, ui) {
        // un item sans attribut "id" ne peut pas être sélectionné (c'est le cas de l'item "Aucun résultat")
        if (ui.item.id) {
            $(event.target).val(ui.item.label);
            $('#' + opts.elementDomId).val(ui.item.id);
            $('#' + opts.elementDomId).trigger("change",[ui.item]);
        }
        return false;
    };
    var response = function(event, ui) {
        if(!ui.content.length) {
            ui.content.push({ label: opts.noResultItemLabel });
        }
    };
    var element = this;
    element.autocomplete($.extend({ select: select, response: response }, opts))
        // on doit vider le champ caché lorsque l'utilisateur tape le moindre caractère (touches spéciales du clavier exclues)
        .keypress(function(event) {
            if (event.which === 8 || event.which >= 32) { // 8=backspace, 32=space
                var lastVal = $('#' + opts.elementDomId).val();
                $('#' + opts.elementDomId).val(null);
                if (null === lastVal) $('#' + opts.elementDomId).trigger("change");
            }
        })
        // on doit vider le champ caché lorsque l'utilisateur vide l'autocomplete (aucune sélection)
        // (nécessaire pour Chromium par exemple)
        .keyup(function() {
            if (!$(this).val().trim().length) {
                var lastVal = $('#' + opts.elementDomId).val();
                $('#' + opts.elementDomId).val(null);
                $('#' + opts.elementDomId).trigger("change");
                if (null === lastVal) $('#' + opts.elementDomId).trigger("change");
            }
        })
        // ajoute de quoi faire afficher plus d'infos dans la liste de résultat de la recherche
        .data("ui-autocomplete")._renderItem = function(ul, item) {
        var template = item.template ? item.template : '<span id=\"{id}\">{label} <span class=\"extra\">{extra}</span></span>';
        var markup   = template
            .replace('{id}', item.id ? item.id : '')
            .replace('{label}', item.label ? item.label : '')
            .replace('{extra}', item.extra ? item.extra : '');
        markup = '<a id="autocomplete-item-'+item.id+'">' + markup + "</a>";
        var li = $("<li></li>").data("item.autocomplete", item).append(markup).appendTo(ul);
        // mise en évidence du motif dans chaque résultat de recherche
        highlight(element.val(), li, 'sas-highlight');
        // si l'item ne possède pas d'id, on fait en sorte qu'il ne soit pas sélectionnable
        if (!item.id) {
            li.click(function() { return false; });
        }
        return li;
    };
    return this;
};



/**
 * Installation d'un mécanisme d'ouverture de fenêtre modale Bootstrap 3 lorsqu'un lien
 * ayant la classe CSS 'modal-action' est cliqué.
 * Et de gestion de la soumission du formulaire éventuel se trouvant dans la fenêtre modale.
 *
 * @param dialogDivId Id DOM éventuel de la div correspondant à la fenêtre modale
 */
function AjaxModalListener(dialogDivId)
{
    this.eventListener = $("body");
    this.modalContainerId = dialogDivId ? dialogDivId : "modal-div-gjksdgfkdjsgffsd";
    this.modalEventName = undefined;

    this.getModalDialog = function ()
    {
        var modal = $("#" + this.modalContainerId);
        if (!modal.length) {
            var modal =
                $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" />').append(
                    $('<div class="modal-dialog" />').append(
                        $('<div class="modal-content" />').append(
                            $('<div class="modal-body">Patientez, svp...<div>')
                        )
                    )
                );
            modal.attr('id', this.modalContainerId).appendTo("body").modal({show: false});
        }
        return modal;
    };
    this.extractNewModalContent = function (data)
    {
        var selector = '.modal-header, .modal-body, .modal-footer';
        // seuls les header, body et footer nous intéressent
        var newModalContent = $(data).filter(selector);
        if (!newModalContent.length) {
            newModalContent = $('<div class="modal-body" />');
        }
        // les var_dump, notice, warning, error PHP s'affichent n'importe où, on remet tout ça dans le body
        $(data).filter(':not(' + selector + ')').prependTo(newModalContent.filter(".modal-body"));
        // suppression de l'éventuel titre identique présent dans le body
        if (title = $(".modal-title", newModalContent).html()) {
            $(":header", newModalContent.filter(".modal-body")).filter(function () { return $(this).html() === title; }).remove();
        }
        return newModalContent;
    }
    this.getDialogBody = function ()
    {
        return $("div.modal-body", this.getModalDialog());
    };
    this.getDialogFooter = function ()
    {
        return $("div.modal-footer", this.getModalDialog());
    };
    this.getForm = function ()
    {
        return $("form", this.getDialogBody());
    };
    this.getSubmitButton = function ()
    {
        return $("#" + this.modalContainerId + " .btn-primary");
    };

    /**
     * Fonction lancée à l'ouverture de la fenêtre modale
     */
    this.modalShownListener = function (e)
    {
        // déplacement du bouton submit dans le footer
//        this.getSubmitButton().prependTo(this.getDialogFooter());
    };

    /**
     * Interception des clics sur les liens adéquats pour affichage de la fenêtre modale
     */
    this.anchorClickListener = function (e)
    {
        var anchor = $(e.currentTarget);
        var url = anchor.attr('href');
        var modalDialog = this.getModalDialog();

        if (url && url !== "#") {
            // transmet à la DIV le lien cliqué (car fournit l'événement à déclencher à la soumission du formulaire)
            modalDialog.data('a', anchor);
            this.modalEventName = anchor.data('event');

            // requête AJAX pour obtenir le nouveau contenu de la fenêtre modale
            $.get(url, {modal: 1}, $.proxy(function (data)
            {
                // remplacement du contenu de la fenêtre modale
                $(".modal-content", modalDialog.modal('show')).html(this.extractNewModalContent(data));

            }, this));
        }

        e.preventDefault();
    };

    /**
     * Interception des clics sur les liens inclus dans les modales pour rafraichir la modale au lieu de la page
     */
    this.innerAnchorClickListener = function (e)
    {
        if (IntraNavigator.embeds(e.currentTarget)) {
            return; // L'IntraNavigator se charge de tout, il n'y a rien à faire
        }

        var anchor = $(e.currentTarget);
        var url = anchor.attr('href');
        var modalDialog = this.getModalDialog();

        if (url && url !== "#") {
            this.modalEventName = anchor.data('event');

            // requête AJAX pour obtenir le nouveau contenu de la fenêtre modale
            $.get(url, {modal: 1}, $.proxy(function (data)
            {
                // remplacement du contenu de la fenêtre modale
                $(".modal-content", modalDialog.modal('show')).html(this.extractNewModalContent(data));

            }, this));
        }

        e.preventDefault();
    };

    this.btnPrimaryClickListener = function (e)
    {
        var form = this.getForm();

        if (IntraNavigator.embeds(form)) {
            return; // L'IntraNavigator se charge de tout, il n'y a rien à faire
        }

        if (form.length) {
            form.submit();
            e.preventDefault();
        }
    };

    this.formSubmitListener = function (e)
    {
        if (IntraNavigator.embeds(e.target)) {
            return; // L'IntraNavigator se charge de tout, il n'y a rien à faire
        }

        var that = this;
        var modalDialog = this.getModalDialog();
        var dialogBody = this.getDialogBody().css('opacity', '0.5');
        var form = $(e.target);
        var postData = $.merge([{name: 'modal', value: 1}], form.serializeArray()); // paramètre "modal" indispensable
        var url = form.attr('action');
        var isRedirect = url.indexOf("redirect=") > -1 || $("input[name=redirect]").val();

        // requête AJAX de soumission du formulaire
        $.post(url, postData, $.proxy(function (data)
        {
            // mise à jour du "content" de la fenêtre modale seulement
            $(".modal-content", modalDialog).html(this.extractNewModalContent(data));

            // tente de déterminer si le formulaire éventuel contient des erreurs de validation
            var terminated = !isRedirect && ($(".input-error, .has-error, .has-errors, .alert.alert-danger", modalDialog).length ? false : true);
            if (terminated) {
                // recherche de l'id de l'événement à déclencher parmi les data du lien cliqué
                //var modalEventName = modalDialog.data('a').data('event');
                if (that.modalEventName) {
                    var args = this.getForm().serializeArray();
                    var event = jQuery.Event(that.modalEventName, {div: modalDialog, a: modalDialog.data('a')});
//                        console.log("Triggering '" + event.type + "' event...");
//                        console.log("Event object : ", event);
//                        console.log("Trigger args : ", args);
                    this.eventListener.trigger(event, [args]);
                }
            }
            dialogBody.css('opacity', '1.0');
        }, this));

        e.preventDefault();
    };
}
/**
 * Instance unique.
 */
AjaxModalListener.singleton = null;
/**
 * Installation du mécanisme d'ouverture de fenêtre modale.
 */
AjaxModalListener.install = function (dialogDivId)
{
    if (null === AjaxModalListener.singleton) {
        AjaxModalListener.singleton = new AjaxModalListener(dialogDivId);
        AjaxModalListener.singleton.start();
    }

    return AjaxModalListener.singleton;
};
/**
 * Désinstallation du mécanisme d'ouverture de fenêtre modale.
 */
AjaxModalListener.uninstall = function ()
{
    if (null !== AjaxModalListener.singleton) {
        AjaxModalListener.singleton.stop();
    }

    return AjaxModalListener.singleton;
};
/**
 * Démarrage du mécanisme d'ouverture de fenêtre modale.
 */
AjaxModalListener.prototype.start = function ()
{
    // interception des clics sur les liens adéquats pour affichage de la fenêtre modale
    this.eventListener.on("click", "a.ajax-modal", $.proxy(this.anchorClickListener, this));

    // interception des clics sur les liens adéquats pour affichage de la fenêtre modale
    this.eventListener.on("click", "#" + this.modalContainerId + " a", $.proxy(this.innerAnchorClickListener, this));

    // le formulaire éventuel est soumis lorsque le bouton principal de la fenêtre modale est cliqué
    this.eventListener.on("click", this.getSubmitButton().selector, $.proxy(this.btnPrimaryClickListener, this));

    // interception la soumission classique du formulaire pour le faire à la sauce AJAX
    this.eventListener.on("submit", "#" + this.modalContainerId + " form", $.proxy(this.formSubmitListener, this));

    // force le contenu de la fenêtre modale à être "recalculé" à chaque ouverture
    this.eventListener.on('hidden.bs.modal', "#" + this.modalContainerId, function (e)
    {
        $(e.target).removeData('bs.modal');
    });

    this.eventListener.on('shown.bs.modal', "#" + this.modalContainerId, $.proxy(this.modalShownListener, this));

    return this;
};
/**
 * Arrêt du mécanisme d'ouverture de fenêtre modale.
 */
AjaxModalListener.prototype.stop = function ()
{
    this.eventListener
        .off("click", "a.ajax-modal", $.proxy(this.anchorClickListener, this))
        .off("click", this.getSubmitButton().selector, $.proxy(this.btnPrimaryClickListener, this))
        .off("submit", "#" + this.modalContainerId + " form", $.proxy(this.formSubmitListener, this))
        .off('hidden.bs.modal', "#" + this.modalContainerId);

    return this;
};





/***************************************************************************************************************************************************
 Popover
 /***************************************************************************************************************************************************/

function ajaxPopoverInit()
{
    jQuery.fn.popover.Constructor.prototype.replace = function ()
    {
        var $tip = this.tip()

        var placement = typeof this.options.placement == 'function' ?
            this.options.placement.call(this, $tip[0], this.$element[0]) :
            this.options.placement

        var autoToken = /\s?auto?\s?/i
        placement = placement.replace(autoToken, '') || 'top'

        this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)

        var pos = this.getPosition()
        var actualWidth = $tip[0].offsetWidth
        var actualHeight = $tip[0].offsetHeight

        var $parent = this.$element.parent()

        var orgPlacement = placement
        var docScroll = document.documentElement.scrollTop || document.body.scrollTop
        var parentWidth = this.options.container == 'body' ? window.innerWidth : $parent.outerWidth()
        var parentHeight = this.options.container == 'body' ? window.innerHeight : $parent.outerHeight()
        var parentLeft = this.options.container == 'body' ? 0 : $parent.offset().left

        placement = placement == 'bottom' && pos.top + pos.height + actualHeight - docScroll > parentHeight ? 'top' :
            placement == 'top' && pos.top - docScroll - actualHeight < 0 ? 'bottom' :
                placement == 'right' && pos.right + actualWidth > parentWidth ? 'left' :
                    placement == 'left' && pos.left - actualWidth < parentLeft ? 'right' :
                        placement

        $tip
            .removeClass(orgPlacement)
            .addClass(placement)

        var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

        this.applyPlacement(calculatedOffset, placement)
    }

    $("body").popover({
        selector: 'a.ajax-popover',
        html: true,
        trigger: 'click',
        content: 'Chargement...',
    }).on('shown.bs.popover', ".ajax-popover", function (e)
    {
        var target = $(e.target);

        var content = $.ajax({
            url: target.attr('href'),
            async: false
        }).responseText;

        div = $("div.popover").last(); // Recherche la dernière division créée, qui est le conteneur du popover
        div.data('a', target); // On lui assigne le lien d'origine
        div.html(content);
        target.popover('replace'); // repositionne le popover en fonction de son redimentionnement
        div.find("form:not(.filter) :input:first").focus(); // donne le focus automatiquement au premier élément de formulaire trouvé qui n'est pas un filtre
    });

    $("body").on("click", "a.ajax-popover", function ()
    { // Désactive le changement de page lors du click
        return false;
    });

    $("body").on("click", "div.popover .fermer", function (e)
    { // Tout élément cliqué qui contient la classe .fermer ferme le popover
        div = $(e.target).parents('div.popover');
        div.data('a').popover('hide');
    });

    $("body").on("submit", "div.popover div.popover-content form", function (e)
    {
        var form = $(e.target);
        var div = $(e.target).parents('div.popover');
        $.post(
            form.attr('action'),
            form.serialize(),
            function (data)
            {
                div.html(data);

                var terminated = $(".input-error, .has-error, .has-errors, .alert", $(data)).length ? false : true;
                if (terminated) {
                    // recherche de l'id de l'événement à déclencher parmi les data de la DIV
                    var modalEventName = div.data('a').data('event');
                    var args = form.serializeArray();
                    var event = jQuery.Event(modalEventName, {a: div.data('a'), div: div});
                    $("body").trigger(event, [args]);
                }
            }
        );
        e.preventDefault();
    });
}




$.widget("unicaen.formAdvancedMultiCheckbox", {

    height: function (height)
    {
        if (height === undefined) {
            return this.getItemsDiv().css('max-height');
        } else {
            this.getItemsDiv().css('max-height', height);
        }
    },

    overflow: function (overflow)
    {
        if (overflow === undefined) {
            return this.getItemsDiv().css('overflow');
        } else {
            this.getItemsDiv().css('overflow', overflow);
        }
    },

    selectAll: function ()
    {
        this.getItems().prop("checked", true);
    },

    selectNone: function ()
    {
        this.getItems().prop("checked", false);
    },

    _create: function ()
    {
        var that = this;
        this.getSelectAllBtn().on('click', function () { that.selectAll(); });
        this.getSelectNoneBtn().on('click', function () { that.selectNone(); });
    },

    //@formatter:off
    getItemsDiv     : function() { return this.element.find('div#items');           },
    getItems        : function() { return this.element.find("input[type=checkbox]");},
    getSelectAllBtn : function() { return this.element.find("a.btn.select-all");    },
    getSelectNoneBtn: function() { return this.element.find("a.btn.select-none");   }
    //@formatter:on

});

$(function ()
{
    WidgetInitializer.add('form-advanced-multi-checkbox', 'formAdvancedMultiCheckbox');
});




/**
 * TabAjax
 */
$.widget("unicaen.tabAjax", {

    /**
     * Permet de retourner un onglet, y compris à partir de son ID
     *
     * @param string|a tab
     * @returns {*}
     */
    getTab: function (tab)
    {
        if (typeof tab === 'string') {
            return this.element.find('.nav-tabs a[aria-controls="' + tab + '"]');
        } else {
            return tab; // par défaut on présuppose que le lien "a" a été transmis!!
        }
    },

    getIsLoaded: function (tab)
    {
        tab = this.getTab(tab);
        return tab.data('is-loaded') == '1';
    },

    setIsLoaded: function (tab, isLoaded)
    {
        tab = this.getTab(tab);
        tab.data('is-loaded', isLoaded ? '1' : '0');
        return this;
    },

    getForceRefresh: function(tab)
    {
        return this.getTab(tab).data('force-refresh') ? true : false;
    },

    setForceRefresh: function(tab, forceRefresh)
    {
        this.getTab(tab).data('force-refresh', forceRefresh);
        return this;
    },

    select: function (tab)
    {
        var that = this;

        tab = this.getTab(tab);
        if (tab.attr('href')[0] !== '#' && (!this.getIsLoaded(tab) || this.getForceRefresh(tab))) {
            var loadurl = tab.attr('href'),
                tid = tab.attr('data-target');

            that.element.find(".tab-pane" + tid).html("<div class=\"loading\">&nbsp;</div>");
            IntraNavigator.add(that.element.find(".tab-pane" + tid));
            $.get(loadurl, function (data)
            {
                that.element.find(".tab-pane" + tid).html(data);
                that.setIsLoaded(tab, true);
            });
        }
        tab.tab('show');
        this._trigger("change");
        return this;
    },

    _create: function ()
    {
        var that = this;

        this.element.find('.nav-tabs a').on('click', function (e)
        {
            e.preventDefault();
            that.select($(this));
            return false;
        });
    },

});

$(function ()
{
    WidgetInitializer.add('tab-ajax', 'tabAjax');
});