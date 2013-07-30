define(['backbone', 'views/error'], function (Backbone, ErrorView) {
    return Backbone.Model.extend({
        conn: null,
        loader: null,
        topic: {},
        models: {},

        initialize: function (attrs) {
            this.topic = {
                session: fluidSession,
                branch: fluidBranch,
                user_id: fluidUserId,
                user_name: fluidUserName,
                user_email: fluidUserEmail
            };
            this.loader = attrs.loader;
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
                    // TODO: change the model and trigger a re-render of the view
                    break;
                case 'map':
                    this.models.map.changeCurrent(response.data['page']);
                    this.models.language.changeCurrent(response.data['language']);
                    break;
            }
        },

        send: function(method, url, data, callback) {
            if (typeof data !== 'object' || data === null) {
                data = {};
            }

            this.conn.call(JSON.stringify(this.topic), {
                method: method,
                url: url,
                data: data
            }).then(
                function (result) {
                    if (typeof callback === 'function') {
                        callback(result);
                    }
                }
            );
        }
    });
});