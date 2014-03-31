define(['backbone', 'models/page/page-collection'], function (Backbone, PageCollection) {
    return Backbone.Model.extend({
        url: '/map',

        pages: null,

        initialize: function (attributes, options) {
            this.socket = options.socket;
            this.pages = new PageCollection(null, {socket: options.socket});
        }
    });
});