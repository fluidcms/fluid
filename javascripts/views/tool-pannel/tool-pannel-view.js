define([
    'backbone',
    'ejs',
    'text!templates/tool-pannel/tool-pannel.ejs'
], function (Backbone, EJS, Template) {
    return Backbone.View.extend({
        events: {
        },

        className: 'tools',

        template: new EJS({text: Template}),

        initialize: function (options) {
        },

        render: function () {
            this.$el.html(this.template.render({
                textEnabled: false
            }));
            return this;
        }
    });
});