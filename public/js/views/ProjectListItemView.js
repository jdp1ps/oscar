/**
 * Created by jacksay on 28/09/15.
 */
define(['backbone'], function (Backbone) {
    var ProjectListItemView = Backbone.View.extend({
        events: {
            'click .handler': 'toggleDetails'
        },
        loaded: false,

        initialize: function () {
            this.projectId = this.$el.data('projectid');
            this.urlLoad = this.$el.data('lazy');
        },
        isOpen: function () {
            return this.$el.hasClass('open');
        },
        isLoaded: function () {
            return this.loaded;
        },
        hideLoader: function () {
            this.$el.find('.loader').fadeOut();
        },
        toggleDetails: function (e) {

            if (!this.isLoaded()) {
                e.stopImmediatePropagation();
                console.log(this.urlLoad);
                this.$el.addClass('loading');
                this.$el.find('.loader').html('<p class="caption">Chargement</p>');
                $.ajax({
                    'url': this.urlLoad
                })
                        .done(function (details) {
                        console.log('Datas loaded', this);
                            this.loaded = true;
                            this.$el.addClass('open');
                            this.$el.find('.content').html(details);
                        }.bind(this))
                        .always(function () {
                            this.hideLoader();
                        }.bind(this));
            } else {
                console.log('Toggle !');
                this.$el.toggleClass('open');
            }

//           console.log('Load details...', this.projectId);
        }
    });
    return ProjectListItemView;
});
