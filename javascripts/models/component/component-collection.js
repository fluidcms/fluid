define(['backbone', 'models/component/component'], function (Backbone, Component) {
    return Backbone.Collection.extend({
        model: Component,

        socket: null,

        url: 'component',

        initialize: function (models, options) {
            this.socket = options.socket;
        },

        fetch: function () {
            var root = this;
            this.socket.send('GET', this.url, {}, function(response) {
                root.parse(response);
            });
        },

        parse: function(response) {
            this.reset(response);
        }
    });
});