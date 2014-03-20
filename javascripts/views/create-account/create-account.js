define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/create-account/create-account.ejs',
    'components/errors'
], function (Backbone, Marionette, EJS, Template, ErrorsComponent) {
    return Marionette.Layout.extend({
        initialize: function(options) {
            this.controller = options.controller;
            this.session = options.session;
        },

        template: new EJS({text: Template}),

        className: 'create-account box',

        events: {
            "submit form": "submitForm"
        },

        regions: {
            errors: "errors"
        },

        ui: {
            name: "[name=name]",
            email: "[name=email]",
            password: "[name=password]"
        },

        submitForm: function(e) {
            e.preventDefault();
            var errors = this.model.validate({
                name: this.ui.name.val(),
                email: this.ui.email.val(),
                password: this.ui.password.val()
            });
            if (errors !== true) {
                var text = [];
                for (var prop in errors) {
                    if (errors.hasOwnProperty(prop)) {
                        if (prop === 'email') {
                            text.push('You need to enter an email address');
                        } else if (prop === 'name') {
                            text.push('You need to enter a name');
                        } else if (prop === 'password') {
                            text.push('You need to enter a password');
                        }
                    }
                }
                this.errors.show(new ErrorsComponent({text: text}));
            } else {
                alert('good');
            }
        }
    });
});