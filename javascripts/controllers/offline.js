define([
    'backbone',
    'marionette',
    'views/login/login',
    'views/create-account/create-account',
    'models/session/session',
    'models/user/user'
], function (Backbone, Marionette, LoginView, CreateAccountView, Session, User) {
    return Marionette.Controller.extend({
        initialize: function (options) {
            this.app = options.app;
            this.session = new Session;
        },

        login: function () {
            this.app.mainRegion.show(new LoginView({controller: this, session: this.session}));
        },

        createAccount: function() {
            this.app.mainRegion.currentView.remove();
            this.app.mainRegion.show(new CreateAccountView({controller: this, model: new User}));
        }
    });
});