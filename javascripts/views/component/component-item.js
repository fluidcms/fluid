define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/component/component-item.ejs'
],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.ItemView.extend({
            tagName: 'li',

            template: new EJS({text: Template}),

            initialize: function(options) {
                this.controller = options.controller;
            },

            render: function() {
                this.$el.html(this.template.render($.extend({}, {
                    baseUrl: this.controller.baseUrl,
                    iconsPath: 'components-icons/'
                }, this.model.attributes)));
                this.onRender();
                return this;
            },

            onRender: function() {
                this.draggable();
            },

            draggable: function() {
                this.$el.find('a').draggable({
                    connectToSortable: "div[contenteditable]",
                    helper: "clone",
                    containment: "document",
                    revert: "invalid",
                    revertDuration: 100,
                    iframeFix: true
                });
            }
        });
    }
);