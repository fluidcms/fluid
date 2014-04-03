define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/editor/string.ejs'
],
    function (Backbone, Marionette, EJS, EditorStringTemplate) {
        return Marionette.ItemView.extend({
            template: new EJS({text: EditorStringTemplate}),

            events: {
                "click [data-action=cancel]": "cancel",
                "click [data-action=save]": "save"
            },

            ui: {
                string: "[data-name=string]"
            },

            initialize: function(options) {
                this.controller = options.controller;
            },

            save: function() {
                this.model.save({
                    value: this.ui.string.text().replace(/^\s+|\s+$/g, '')
                });
            },

            cancel: function() {
                this.controller.app.editorRegion.$el.hide();
                this.close();
            }
        });
    }
);