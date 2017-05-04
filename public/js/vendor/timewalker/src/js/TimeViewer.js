define(['Backbone'], function (Backbone) {

    "use strict";

    var TimeViewer, numericDate, unitDateData, showTooltip, tooltip, hideTooltip;

    // --------------------------------------------------------------- NAMESPACE
    TimeViewer = {
        Model: {},
        View: {},
        Templates: {}
    };

    /**
     * Get date as number
     * @param Date date
     */
    numericDate = function (date) {
        return date.getTime();
    };

    /**
     * Display tooltip
     * @param html
     */
    showTooltip = function(html, x, y) {
        if( !tooltip ){
            tooltip = $('<div class="tv-tooltip">THE TOOLTIP</div>')
                .css({
                    'position': 'fixed',
                    'z-index': 1000
                }).hide();
            $('body').append(tooltip);

        }

        var positionLeft = x + 20,
            positionTop = y,
            width,
            height,
            $window = $(window),
            windowWidth = $window.width(),
            windowHeight = $window.height();

        tooltip.html(html);

        // Get tootlip's size
        width = tooltip.width();
        height = tooltip.height();

        // Replace if overflow
        if(positionLeft + width + 20 > windowWidth ){
            positionLeft = windowWidth - width;
        }
        // Replace if overflow
        if(positionTop + height > windowHeight ){
            positionTop = windowHeight - height;
        }


        tooltip.css({
            'top': positionTop,
            'left': positionLeft
        });

        tooltip.fadeIn(300);
    };

    hideTooltip = function() {
        if (tooltip) {
            tooltip.stop().hide();
        }
    };

    /**
     * @deprecated
     * @param date
     * @param unit
     * @returns {*}
     */
    unitDateData = function (date, unit) {
        if (!date) {
            return null;
        }
        if (!(date instanceof Date))
            date = new Date(date);

        switch (unit) {
            case 'year':
                return date.getUTCFullYear();

            // TODO month view

            // TODO Week view

            // TODO day view
        }
        throw new Error('Invalid time unit');
    };

    // --------------------------------------------------------------- TEMPLATES
    // Use Mustache's style
    _.templateSettings = {
        interpolate: /\{\{(.+?)\}\}/g
    };

    TimeViewer.Templates.renderSerie = _.template('<h2 class="tv-serie-label">{{label}}</h2><section class="tv-serie-datas"><div class="tv-serie-datas-view tv-view"></div></section>');
    TimeViewer.Templates.renderData = _.template('<strong class="tv-data-label">{{label}}</strong>'); //<span class="period">du <time class="from">{{start}}</time> au <time class="to">{{end}}</time></span>


    //////////////////////////////////////////////////////////////////////////////
    //
    // MODEL
    //
    //////////////////////////////////////////////////////////////////////////////

    /**
     * Data.
     */
    TimeViewer.Model.Data = Backbone.Model.extend({
        defaults: function () {
            return {
                start: null,
                end: null,
                key: 'Unamed data',
                label: 'Unamed data'
            };
        },

        getMinDate: function () {
            return this.get('start');
        },

        getMaxDate: function () {
            return this.get('end');
        }
    });

    /**
     * Collection of data
     */
    TimeViewer.Model.DataCollection = Backbone.Collection.extend({
        model: TimeViewer.Model.Data
    });

    /**
     * Serie of data
     */
    TimeViewer.Model.Serie = Backbone.Model.extend({
        defaults: function () {
            return {
                label: "Unamed serie",
                datas: new TimeViewer.Model.DataCollection()
            };
        },

        add: function (datas) {
            this.get('datas').add(datas);
        },

        getData: function () {
            return this.get('datas');
        },

        size: function(){
            return this.getData().size();
        },

        initialize: function(){
            this.getData().on('all', function(type, data){
               this.trigger(type, data);
            }.bind(this));
        }
    });

    /**
     * Model for main view, an aggregate of serie.
     */
    TimeViewer.Model.TimeViewer = Backbone.Collection.extend({
        model: TimeViewer.Model.Serie,
        getData: function () {
            return this;
        }
    });

    /**
     * Common method for get earliest date
     *
     * @type {Function}
     */
    TimeViewer.Model.Serie.prototype.getMinDate = TimeViewer.Model.TimeViewer.prototype.getMinDate = function () {
        var min = '9999';
        this.getData().each(function (item) {
            if (item.getMinDate() !== null && item.getMinDate() < min) {
                min = item.getMinDate();
            }
        });
        return min == '9999' ? null : min;
    };

    /**
     * Common method for get the most recent date
     *
     * @type {Function}
     */
    TimeViewer.Model.Serie.prototype.getMaxDate = TimeViewer.Model.TimeViewer.prototype.getMaxDate = function () {
        var max = '0000';
        this.getData().each(function (item) {
            if (item.getMaxDate() !== null && item.getMaxDate() > max) {
                max = item.getMaxDate();
            }
        });
        return max == '0000' ? null : max;
    };


    //////////////////////////////////////////////////////////////////////////////
    //
    // VIEWS
    //
    //////////////////////////////////////////////////////////////////////////////

    /**
     * View for data
     */
    TimeViewer.View.Data = Backbone.View.extend({
        className: "tv-serie-data",
        options: {},

        events: {
            'mouseenter': 'onMouseover',
            'mouseleave': 'onMouseout',
            'click': 'onClick',
            'click [data-action]': 'handlerAction'
        },

        handlerAction: function( e ){
            var eventType = $(e.currentTarget).data('action');
            this.serieView.mainView.trigger(eventType, this);
            e.preventDefault();
        },

        onMouseover: function( evt ){
            if(this.model.get('description')){
                showTooltip(this.model.get('description'), evt.clientX, evt.clientY);
            }
        },

        onClick: function( evt ){
            if( this.options.dataSelectable === true ){
                if (this.$el.hasClass('tv-selected')){
                    this.watchOff();
                } else {
                    this.watchStart();
                }
                evt.stopPropagation();
            }
        },

        watchStart: function(){
            if( TimeViewer.View.Data.watched ){
                TimeViewer.View.Data.watched.watchOff();
            }
            this.$el.addClass('tv-selected');
            $(window).on('keydown click', this.watchDel.bind(this));
            TimeViewer.View.Data.watched = this;
        },

        watchOff: function(){
            this.$el.removeClass('tv-selected');
            $(window).off("keydown click"); //, this.watchDel);
            TimeViewer.View.Data.watched = null;
        },

        watchDel: function(e){
            if(e.type === 'click'){
                this.watchOff();
                e.preventDefault();
                return;
            }

            if(e.type === 'keydown' && (e.keyCode === KeyEvent.DOM_VK_DELETE || e.keyCode === KeyEvent.DOM_VK_BACK_SPACE) ){
                this.watchOff();
                this.serieView.mainView.trigger('delete-data', this);
                e.preventDefault();
                return;
            }
        },

        onMouseout: function( evt ){
            hideTooltip();
        },

        /**
         * Constructor.
         *
         * @param options
         */
        initialize: function (options) {
            this.decalage = options.decalage;
            this.periodDisplay = options.periodDisplay;
            this.options = options.options;
            this.serieView = options.serieView;
            this.serieView.mainView.on('updateview', function(){
                this.interval = setInterval(function(){
                    this.adjustLabelPosition();
                }.bind(this), 500);
            }.bind(this));
        },

        adjustLabelPosition: function(){

            var itemSize = {
                left: this.$el.offset().left,
                right: this.$el.width() + this.$el.offset().left
            };
            var maskSize = {
                left: this.serieView.getMask().offset().left,
                right: this.serieView.getMask().width() + this.serieView.getMask().offset().left
            };

            if( !(itemSize.right < maskSize.left || itemSize.left > maskSize.right) ){
                var padding = 0;
                if( itemSize.left < maskSize.left ){
                    padding = 100 / (itemSize.right - itemSize.left) * (maskSize.left - itemSize.left);
                }
                this.$el.find('.tv-data-label').css('padding-left', padding+'%');

            }

            clearInterval(this.interval);
        },

        /**
         * Render.
         *
         * @returns {TimeViewer.View.Data}
         */
        render: function () {
            var
            // begining of period
                numericBegin = this.periodDisplay.numericStart,
                numericEnd = this.periodDisplay.numericEnd,
            // begining of current item
                itemStart = this.model.get('start') ? numericDate(new Date(this.model.get('start'))) : numericBegin,

            // ending of current item
                itemEnd = this.model.get('end') ? numericDate(new Date(this.model.get('end'))) : numericEnd,

            // ratio
                ratio = this.periodDisplay.ratio,

            // Calculate item width/position
                itemleft = (itemStart - numericBegin) * ratio,
                itemWidth = ((itemEnd - itemStart)) * ratio
                ;


            // Add cosmetic CSS classes
            if (!this.model.get('end')) {
                this.$el.addClass('noend');
            }
            if (!this.model.get('start')) {
                this.$el.addClass('nostart');
            }

            if (this.options.labelClass[this.model.get('key')]) {
                this.$el.addClass(this.options.labelClass[this.model.get('key')]);
            }

            this.$el.html(TimeViewer.Templates.renderData(this.model.toJSON())).css({
                width: itemWidth + '%',
                'margin-top': ((this.decalage*2) + 0.25) + 'em',
                position: 'absolute',
                left: itemleft + '%'
            });

           /* if( this.periodDisplay.dataDeletable ){
                this.$el.append('<a href="#" class="" data-action="delete-data"><i class="icon-trash"></i></a>')
            }*/

            return this;
        }
    });

    /**
     * View for Serie of data.
     */
    TimeViewer.View.Serie = Backbone.View.extend({
        className: "tv-serie",
        options: {},
        events: {
            'click [data-action]': 'actions'
        },

        actions: function(e){
            this.model.trigger($(e.target).data('action'), this.model);
        },

        initialize: function (options) {
            this.renderers = options.renderers || {};
            this.periodDisplay = options.periodDisplay;
            this.options = _.extend(this.options, options.options);
            this.mainView = options.mainView;
        },

        getViewport: function(){
            return this.$el.find('.tv-serie-datas .tv-serie-datas-view') || this.$el;
        },

        /**
         * @returns jquery
         */
        getMask: function(){
            return this.$el.find('.tv-serie-datas');
        },

        getMaskBounds: function(){
            if( this.maskBounds === undefined ){
                var offset = this.getMask().offset();
                this.maskBounds = {
                    left: offset.left,
                    right: offset.left + this.getMask().width()
                };
            }
            return this.maskBounds;
        },

        render: function () {
            this.$el.html(TimeViewer.Templates.renderSerie(this.model.toJSON()));
            //this.$el.find('.tv-serie-label').append('<a data-action="toAdd">Ajouter</a>');
            var datas = this.getViewport();
            var decalage = {}, decalageCounter = 0;

            // Display "left maker", the effective begining
            if( this.periodDisplay.forceBegining ) {
                var startMarker = $('<div class="tv-marker tv-start">&nbsp;</div>').css({
                    position: 'absolute',
                    top: 0,
                    bottom: 0,
                    left: '-2000%',
                    width: ((numericDate(new Date(this.periodDisplay.startUse)) - this.periodDisplay.numericStart) * this.periodDisplay.ratio) + 2000 + '%',
                    'z-index': 20
                });
                datas.append(startMarker);
            }

            // Display "right maker", the effective ending
            if( this.periodDisplay.forceEnding ) {
                var endMarker = $('<div class="tv-marker tv-end">&nbsp;</div>').css({
                    position: 'absolute',
                    top: 0,
                    bottom: 0,
                    left: ((numericDate(new Date(this.periodDisplay.endUse)) - this.periodDisplay.numericStart) * this.periodDisplay.ratio) + '%',
                    right: 0,
                    'z-index': 20
                });

                datas.append(endMarker);
            }

            // Other visual help
            for( var j=0; j<this.periodDisplay.segments; j++ ){
                var weft = $('<div class="tv-weft">&nbsp;</div>').css({
                    position: 'absolute',
                    'z-index': 0,
                    width: (100/this.periodDisplay.segments)+'%',
                    bottom: 0,
                    top: 0,
                    left: (j*(100/this.periodDisplay.segments))+'%'
                });
                datas.append(weft);
            }


            this.model.get('datas').each(function (data) {
                decalage[data.get('label')] = decalageCounter++;
                /*
                if (!decalage[data.get('label')]) {
                    decalage[data.get('label')] = decalageCounter++;
                }*/
                var view = new TimeViewer.View.Data({
                    model: data,
                    decalage: decalage[data.get('label')],
                    periodDisplay: this.periodDisplay,
                    options: this.options,
                    serieView: this
                });
                datas.append(view.render().$el);
            }.bind(this));
            this.$el.css('min-height', (1.25 + (decalageCounter*1.65)) + 'em');
            return this;
        }
    });

    /**
     * Vue globale
     * @author Stéphane Bouvry<jacksay@jacksay.com>
     */
    TimeViewer.View.Main = Backbone.View.extend({

        events: {
            'click .next': 'handlerScrollRight',
            'click .previous': 'handlerScrollLeft',
            'click .zoomin': 'handlerZoomIn',
            'click .zoomout': 'handlerZoomOut',
            'click [data-action]' : 'handlerAction'
        },

        handlerAction: function(evt) {
            this.trigger($(evt.target).data('action'));
            evt.preventDefault();
        },

        className: "tv",

        // Rendering options, can be overridden at initialization
        defaultOptions: function () {
            return {
                forceBegining: null, // Force the begining
                dataSelectable: false,
                forceEnding: null, // Same for ending
                title: 'Component title',
                renderStrategy: {
                    layout: 'rendeStrategyLayout'
                },
                labelClass: {}
            };
        },

        period: {
            displayMode: 'oneyear', // Mode d'affichage : oneyear | fill | month
            startDisplay: 'auto',   // Début de la plage d'affichage
            endDisplay: 'auto',     // Fin de la plage d'affichage
            start: 'auto',          // Plus petite date (peut être null)
            end: 'auto',             // Plus grande date, peut être null
            currentYear: 'auto',
            currentMonth: null
        },

        // Les modes d'affichage
        displayModes: ['oneyear', 'fill'],

        //
        rightPos: null,
        currentSizeStep: null,

        //
        moveStep: 1,

        // current zoom
        zoom: 100,
        zoomGap: 100,

        /**
         * Get saved state (localstorage)
         * @returns {*}
         */
        getSaveState: function(){
            var store;
            if( this.options.saveState && window && window.localStorage ){
                store = window.localStorage.getItem(this.options.saveState);
                if( store ){
                    store = JSON.parse(store);
                    return store;
                }
            }
            return {
                rightPos: 0,
                currentSizeStep: 1
            };
        },

        /**
         * Save current state in localStorage.
         */
        saveState: function(){
          var data = {
            rightPos: this.rightPos,
              currentSizeStep: this.currentSizeStep
          };
            if( this.options.saveState && window && window.localStorage ){
                window.localStorage.setItem(this.options.saveState, JSON.stringify(data));
            }
        },


        rendeStrategyLayout: function () {
            return '<h1 class="tv-title">' + this.options.title + '</h1>' +
                '<header class="tv-series-header">' +
                '<nav class="tv-header-nav">' +
                '<a href="#" class="previous">&larr;</a>' +
                '<a href="#" class="next">&rarr;</a>' +
                '<a href="#" class="zoomin">-</a>' +
                '<a href="#" class="zoomout">+</a>' +
                '</nav>' +
                '<div class="tv-header-labels"><div class="tv-header-labels-view tv-view"></div></div>' +
                '</header>' +
                '<div class="tv-series">' +
                '</div>' +
                '<footer class="tv-footer"></footer>';
        },

        getPeriodDisplayed: function () {
            // Données
            var startAbsolute,   // Début absolue réél
                endAbsolute,     // Fin absolue réél

            // Les données 'use' sont égales aux données 'abs' ci-dessus, sauf
            // si ces dernières sont null, dans ce cas on utilise une plage d'un
            // an avant/après l'autre date. Si aucune date on prend l'année en
            // cour.
                numericStart,
                numericEnd,
                ratio,
                endUse,     // Fin utilisée
                startUse,    // Fin utilisée
                startDisplay,
                endDisplay,
                segments,
                segmentLabels = [],
                segmentDivisions = [],
                segmentLabelStrategy,
                display;

            startUse = startAbsolute = this.options.forceBegining ? this.options.forceBegining : this.model.getMinDate();
            endUse = endAbsolute = this.options.forceEnding ? this.options.forceEnding : this.model.getMaxDate();

            if (!startUse && !endUse) {
                startUse = (new Date()).toISOString().substring(0, 4) + '-01-01';
                endUse = (new Date()).toISOString().substring(0, 4) + '-12-31';
            }
            else if (!startUse) {
                startUse = endUse.substring(0, 4) + '-01-01';
            }
            else if (!endUse) {
                endUse = startUse.substring(0, 4) + '-12-31';
            }

            if (this.period.startDisplay == 'auto') {
                startDisplay = startUse.substring(0, 4) + '-01-01';
            }
            if (this.period.endDisplay == 'auto') {
                endDisplay = endUse.substring(0, 4) + '-12-31';
            }

            // begining of period
            numericStart = numericDate(new Date(startDisplay));
            numericEnd = numericDate(new Date(endDisplay));
            ratio = 100 / (numericEnd - numericStart);

            switch (this.period.displayMode) {
                case 'oneyear':
                    segments = (endUse.substring(0, 4) - startUse.substring(0, 4)) + 1;
                    segmentDivisions = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];
                    segmentLabelStrategy = function (value) {
                        return "Année " + value;
                    };
                    break;
                default :
                    throw new Error("displayMode not implemented");
            }

            for (var i = parseInt(startUse.substring(0, 4)); i <= endUse.substring(0, 4); i++) {
                segmentLabels.push(segmentLabelStrategy(i));
            }
            // Nombre de segment
            display = {
                segments: segments,
                segmentLabels: segmentLabels,
                segmentDivisions: segmentDivisions,
                dataDeletable: this.options.dataDeletable,
                dataSelectable: this.options.dataSelectable,
                mode: this.period.displayMode,
                startAbsolute: startAbsolute,
                endAbsolute: endAbsolute,
                startUse: startUse,
                endUse: endUse,
                startDisplay: startDisplay,
                endDisplay: endDisplay,
                numericStart: numericStart,
                numericEnd: numericEnd,
                ratio: ratio
            };

            return display;
        },



        getSize: function () {
            return this.sizeStep * 100 / (this.sizeStep - this.currentSizeStep + 1);
        },

        getRight: function(){
            return -(this.getSize() / this.sizeStep) * this.rightPos;
        },

        /**
         * Fix the view size.
         */
        sizing: function (trigger) {
            this.$el.find('.tv-view').css('width', (this.getSize()) + '%');
            this.placing(trigger);
        },

        /**
         * Fix the view location
         */
        placing: function (trigger) {
            var right = this.getSize();
            this.$el.find('.tv-view').css('right', this.getRight() + '%');
            if( trigger && trigger === true ){
                this.trigger('updateview');
            }
        },

        ////////////////////////////////////////////////////////////////////////
        // HANDLERS
        handlerZoomOut: function ( evt ) {
            if (this.currentSizeStep < this.sizeStep) {
                this.currentSizeStep += 1;
            }
            this.sizing(true);
            this.saveState();
            evt.preventDefault();
        },

        handlerZoomIn: function ( evt ) {
            if (this.currentSizeStep > 1) {
                this.currentSizeStep -= 1;
            }
            this.sizing(true);
            this.saveState();
            evt.preventDefault();
        },

        handlerScrollRight: function ( evt ) {
            if (this.rightPos > 0) {
                this.rightPos--;
            }
            this.placing(true);
            this.saveState();
            evt.preventDefault();
        },

        handlerScrollLeft: function ( evt ) {
            if (this.rightPos < this.sizeStep - 1) {
                this.rightPos++;
            }
            this.placing(true);
            this.saveState();
            evt.preventDefault();
        },

        ////////////////////////////////////////////////////////////////////////
        // CORE METHODS
        initialize: function (attributes, options) {
            this.options = _.extend(this.defaultOptions(), options);
            if (!this.model) {
                this.model = new TimeViewer.Model.TimeViewer();
            }
        },

        render: function () {
            var periodDisplay = this.getPeriodDisplayed(),
                tvDatas,
                headerTime,
                i,
                timeDivisionModel;


            this.$el.html(this[this.options.renderStrategy.layout]());

            headerTime = this.$el.find('.tv-header-labels-view');
            tvDatas = this.$el.find('.tv-series');
            timeDivisionModel = $('<div class="tv-header-labels-divisions"></div>');


            for (i = 0; i < periodDisplay.segmentDivisions.length; i++) {
                timeDivisionModel.append('<span class="division" style="display: inline-block; width: ' + (100 / periodDisplay.segmentDivisions.length) + '%">' +
                    periodDisplay.segmentDivisions[i] +
                    '</span>');
            }

            for (i = 0; i < periodDisplay.segmentLabels.length; i++) {

                headerTime.append($('<div class="tv-segment-header" style="width: ' + (100 / periodDisplay.segmentLabels.length) + '%">' +
                    '<h4>' + periodDisplay.segmentLabels[i] + '</h4>' +
                    timeDivisionModel.clone().html() +
                    '</div>'));
            }

            this.model.each(function (serie) {
                if( serie.size() ) {
                    var view = new TimeViewer.View.Serie({
                        model: serie,
                        periodDisplay: periodDisplay,
                        mainView: this,
                        options: this.options
                    });
                    tvDatas.append(view.render().$el);
                }
            }.bind(this));

            if( this.options.renderFooter )
                tvDatas.append(this.options.renderFooter());

            this.sizeStep = periodDisplay.segmentLabels.length;
            this.rightPos = this.getSaveState().rightPos;
            this.currentSizeStep = this.getSaveState().currentSizeStep;

            this.sizing(false);

            var interval = setInterval(function(){
                this.triggerChange();
                clearInterval(interval);
            }.bind(this), 50);

            return this;
        },
        triggerChange: function(){
          this.trigger('updateview');
        },
        getCurrentSizeStep: function(){

        },

        // WORK
        /**
         * Add a serie (timeline) to timeviewer with optional data (array).
         * @var name string Unique name of the serie (use in label).
         * @var datas array|null An array of data for the serie.
         * @var silent boolean
         */
        addSerie: function (name, datas, silent) {
            var serie;
            if (!this.getSerie(name)) {
                // Création de la série
                serie = new TimeViewer.Model.Serie(datas, {name: name});

                // Ajout au modèle
                this.series.push(serie);
            }
            return serie;
        },

        /**
         * Return serie by name, null if not.
         */
        getSerie: function (name) {
            var serie = null;
            _.each(this.series, function (s) {
                if (s.name == name) {
                    serie = s;
                }
            });
            return serie;
        },
    });
    return TimeViewer;
});
