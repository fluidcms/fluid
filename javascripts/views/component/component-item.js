define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/component/component-item.ejs'
],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.ItemView.extend({
            events: {
                //"click a": "editPage"
            },

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
            }
        });
    }
);