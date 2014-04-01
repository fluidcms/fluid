define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-collection',
    'text!templates/page/page.ejs'
],
    function (Backbone, Marionette, EJS, VariableCollectionView, Template) {
        return Marionette.Layout.extend({
            initialize: function() {
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
                this.variablesRegion.show(new VariableCollectionView({collection: this.model.variables}));
            }
        });
    }
);