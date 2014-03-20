define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/login/login.ejs'
], function (Backbone, Marionette, EJS, Template) {
    return Marionette.Layout.extend({
        initialize: function(options) {
            this.controller = options.controller;
            this.session = options.session;
        },

        template: new EJS({text: Template}),

        className: 'login box',

        events: {
            "submit form": "submitForm"
        },

        ui: {
            email: "[name=email]",
            password: "[name=password]"
        },

        submitForm: function(e) {
            e.preventDefault();
            this.session.testCredentials(
                    this.ui.email.val(),
                    this.ui.password.val()
                ).done(function(response) {
                    console.log(response);
                }).fail(function() {
                    alert( "error" );
                })
        }


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