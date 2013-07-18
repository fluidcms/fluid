define(['backbone'], function (Backbone) {
    var Layout = Backbone.Model.extend({
    });

    var Layouts = Backbone.Collection.extend({
        url: 'layout',

        model: Layout,

        initialize: function (items, attrs) {
            this.socket = attrs.socket;
        },

        fetch: function () {
            var root = this;
            this.socket.send('GET', this.url, {}, function(response) {
                root.reset(response);
            });
        }
    });

    return {
        Layout: Layout,
        Layouts: Layouts
    };
});
