define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/editor/content.ejs'
],
    function (Backbone, Marionette, EJS, EditorContentTemplate) {
        return Marionette.ItemView.extend({
            template: new EJS({text: EditorContentTemplate}),

            events: {
                "click [data-action=cancel]": "cancel",
                "click [data-action=save]": "save"
            },

            ui: {
                string: "[data-name=content]"
            },

            initialize: function(options) {
                this.controller = options.controller;
                this.variableView = options.variableView;
            },

            render: function() {
                this.$el.html(this.template.render($.extend({}, {
                    rendered_value: this.variableView.renderValue(this.model)
                }, this.model.attributes)));
                this.bindUIElements();
                return this;
            },

            save: function() {
                var value = this.variableView.unrenderValue(this.ui.string.html());
                var root = this;
                this.model.save({
                    value: value
                }, {
                    success: function() {
                        root.cancel();
                    }
                });
            },

            cancel: function() {
                this.controller.app.editorRegion.$el.hide();
                this.close();
            }
        });
    }
);