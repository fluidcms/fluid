define(['backbone'], function (Backbone) {
    var Component = Backbone.Model.extend({
        initialize: function (attrs) {
        }
    });

    return Backbone.Collection.extend({
        socket: null,
        model: Component,

        url: 'component',

        initialize: function (items, attrs) {
            this.socket = attrs.socket;
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