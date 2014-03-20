define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/login/login.ejs'
], function (Backbone, Marionette, EJS, Template) {
    return Marionette.Layout.extend({
        initialize: function(options) {
        },

        template: new EJS({text: Template})


        /*events: {
        },*/

        //template: new EJS({text: Template}),

        /*initialize: function (options) {
        },

        render: function () {
            this.$el.html(this.template.render({}));
            return this;
        }*/
    });
});