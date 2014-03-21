define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/login/login.ejs',
    'components/errors'
], function (Backbone, Marionette, EJS, Template, ErrorsComponent) {
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

        regions: {
            errors: "errors"
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
                    alert('Login');
                }).fail(function() {
                    root.showError();
                })
        },

        showError: function() {
            this.errors.show(new ErrorsComponent({text: ['Wrong email or password']}));
        },

        createAccount: function() {
            this.controller.createAccount()
        }
    });
});