define([
    'backbone',
    'marionette',
    'ejs'
], function (Backbone, Marionette, EJS, Template, ErrorsComponent) {
    return Marionette.ItemView.extend({
        initialize: function(options) {
            this.$el = $("#website-iframe");
            //this.controller = options.controller;
            //this.session = options.session;
        },

        //template: new EJS({text: Template}),

        events: {
        },

        regions: {
        },

        ui: {
        },

        render: function() {
            this.$el[0].contentWindow.location = '/';

            //console.log(this.$el);
            //this.model.getUrl();
        },

        reload: function() {
            window.location = '/admin/';
        }
    });
});