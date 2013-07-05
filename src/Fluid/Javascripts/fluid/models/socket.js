define(['backbone', 'views/error'], function (Backbone, ErrorView) {
    return Backbone.Model.extend({
        conn: null,
        loader: null,
        views: {},

        initialize: function (attrs) {
            this.loader = attrs.loader;
            //this.views.version = attrs.version;
        },

        connection: function() {
            var root = this;
            root.conn = new ab.Session(
                fluidWebSocketUrl,
                function() {
                    root.trigger('ready');
                    root.conn.subscribe(fluidBranch + "/" + fluidUserId, function(topic, message) {
                        root.parse(topic, message);
                    });
                },
                function() {
                    new ErrorView({msg: 'An unknown error occured, please contact the administrator.'});
                },
                {'skipSubprotocolCheck': true}
            );
        },

        parse: function(topic, message) {
            var response = $.parseJSON(message);
            switch(response.target) {
                case 'version':
                    //this.views.version.change(response.data);
                    break;
            }
        },

        fetch: function(method, url, data, callback) {
            this.conn.call(fluidBranch + "/" + fluidUserId, {
                method: method,
                url: url,
                data: data
            }).then(
                function (result) {
                    callback(result);
                }
            );
        }
    });
});