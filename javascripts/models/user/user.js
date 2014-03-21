define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        url: window.location.rootpath + 'user',

        validate: function(attributes) {
            var errors = {};
            if (typeof attributes.name === 'undefined' || !attributes.name) {
                errors['name'] = 'name';
            }
            if (typeof attributes.email === 'undefined' || !attributes.email) {
                errors['email'] = 'email';
            }
            if (typeof attributes.password === 'undefined' || !attributes.password) {
                errors['password'] = 'password';
            }
            for (var prop in errors) {
                if (errors.hasOwnProperty(prop)) {
                    return errors;
                }
            }
        }
    });
});
