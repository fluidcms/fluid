define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-layout',
    'text!templates/page/page.ejs'
],
    function (Backbone, Marionette, EJS, VariableLayoutView, Template) {
        return Marionette.Layout.extend({
            initialize: function(options) {
                this.controller = options.controller;
                this.model.fetch();
            },

            events: {
                "click a": "editPage"
            },

            modelEvents: {
                "change": "modelChanged"
            },

            template: new EJS({text: Template}),

            regions: {
                variablesRegion: '[data-name="variables"]'
            },

            modelChanged: function() {
                this.variablesRegion.show(new VariableLayoutView({collection: this.model.variables, controller: this.controller}));
            }
        });
    }
);