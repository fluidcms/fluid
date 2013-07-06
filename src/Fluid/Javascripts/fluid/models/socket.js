define(['backbone', 'views/error'], function (Backbone, ErrorView) {
    return Backbone.Model.extend({
        conn: null,
        loader: null,
        topic: {},
        views: {},

        initialize: function (attrs) {
            this.topic = {
                branch: fluidBranch,
                user_id: fluidUserId,
                user_name: fluidUserName,
                user_email: fluidUserEmail
            };
            this.loader = attrs.loader;
            //this.views.version = attrs.version;
        },

        connection: function() {
            var root = this;
            root.conn = new ab.Session(
                fluidWebSocketUrl,
                function() {
                    root.trigger('ready');
                    root.conn.subscribe(JSON.stringify(root.topic), function(topic, message) {
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
            var response = JSON.parse(message);
            switch(response.target) {
                case 'version':
                    //this.views.version.change(response.data);
                    break;
            }
        },

        send: function(method, url, data, callback) {
            this.conn.call(JSON.stringify(this.topic), {
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