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
            "submit form": "submitForm",
            "click button[data-action=createAccount]": "createAccount"
        },

        ui: {
            email: "[name=email]",
            password: "[name=password]"
        },

        submitForm: function(e) {
            var root = this;
            e.preventDefault();
            this.session.testCredentials(
                    this.ui.email.val(),
                    this.ui.password.val()
                ).done(function(response) {
                    console.log(response);
                }).fail(function() {
                    root.showError();
                })
        },

        showError: function() {
            alert('Wrong email or password');
        },

        createAccount: function() {
            this.controller.createAccount()
        }
    });
});