define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        url: window.location.rootpath + 'session',

        testCredentials: function(email, password) {
            $.ajax({
                url: this.url,
                type: "POST",
                data: {
                    email: email,
                    password: password
                }
            }).done(function(response) {
                console.log(response);
            });
        }
    });
});