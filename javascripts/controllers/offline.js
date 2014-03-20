define([
    'backbone',
    'marionette',
    'views/login/login',
    'models/session/session'
], function (Backbone, Marionette, LoginView, Session) {
    return Marionette.Controller.extend({
        initialize: function (options) {
            this.app = options.app;
            this.session = new Session;
        },

        login: function () {
            this.app.mainRegion.show(new LoginView({controller: this, session: this.session}));
        }
    });
});
