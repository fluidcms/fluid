define(['backbone', 'models/page/page'], function (Backbone, Page) {
    return Backbone.Collection.extend({
        url: '/pages',

        model: Page,

        initialize: function (models, options) {
            this.socket = options.socket;
        },

        fetch: function () {
            var root = this;
            this.socket.send('GET', this.url, {}, function(response) {
                root.reset(response);
            });
        }
    });
});