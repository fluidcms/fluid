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
            var root = this;
            e.preventDefault();
            var attributes = this.getAttributes();
            var validation = this.model.validate(attributes);

            if (typeof validation !== 'undefined') {
                this.showErrors(validation);
                return false;
            }

            this.model.save(attributes, {
                success: function() {
                    window.location.reload();
                },
                error: function(response) {
                    root.showErrors(response.errors);
                }
            });
            return true;
        },

        showErrors: function(errors) {
            var text = [];
            for (var prop in errors) {
                if (errors.hasOwnProperty(prop)) {
                    if (prop === 'email') {
                        text.push('You need to enter an email address');
                    } else if (prop === 'email_exists') {
                        text.push('The email you entered is already in use');
                    } else if (prop === 'name') {
                        text.push('You need to enter a name');
                    } else if (prop === 'password') {
                        text.push('You need to enter a password');
                    }
                }
            }
            if (!text.length) {
                text.push('An unknown error occured');
            }
            this.errors.show(new ErrorsComponent({text: text}));
        },

        getAttributes: function() {
            return {
                name: this.ui.name.val(),
                email: this.ui.email.val(),
                password: this.ui.password.val()
            };
        }
    });
});