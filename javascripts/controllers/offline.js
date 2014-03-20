define(['backbone', 'marionette', 'views/login/login'], function (Backbone, Marionette, LoginView) {
    return Marionette.Controller.extend({
        initialize: function (options) {
            this.app = options.app;
        },

        login: function () {
            this.app.mainRegion.show(new LoginView);
        }
    });
});
