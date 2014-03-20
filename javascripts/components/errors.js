define([
    'backbone',
    'ejs',
    'text!components/templates/errors.ejs'
], function (Backbone, EJS, Template) {
    return Backbone.View.extend({
        initialize: function(data) {
            this.data = data;
        },

        template: new EJS({text: Template}),

        className: 'errors',

        render: function () {
            this.$el.html(this.template.render(this.data));
            return this;
        }
    });
});