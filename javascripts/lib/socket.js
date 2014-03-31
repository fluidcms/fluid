define(['backbone', 'views/helpers/error'], function (Backbone, ErrorView) {
    return (function () {
        return $.extend(Backbone.Events, {
            pingTimeout: 30000,

            connection: null,
            models: {},

            initialize: function (options) {
                this.session = options.session;
                this.user = options.user;
                this.app = options.app;
                this.url = options.app.params.websocket;
                this.branch = options.app.params.branch;
            },

            topic: function () {
                return {
                    session: this.session.get('token'),
                    user_id: this.user.get('id'),
                    branch: this.branch
                };
            },

            connect: function () {
                var root = this;
                root.connection = new ab.Session(
                    this.url,
                    function () {
                        root.connection.subscribe(JSON.stringify(root.topic()), function (topic, message) {
                            root.parse(topic, message);
                            root.trigger('ready');
                            setTimeout(function () {
                                root.ping();
                            }, root.pingTimeout);
                        });
                    },
                    function () {
                        new ErrorView({msg: 'An unknown error occured, please contact the administrator.'});
                    },
                    {'skipSubprotocolCheck': true}
                );
            },

            parse: function (topic, message) {
                var response = JSON.parse(message);
                switch (response.target) {
                    case 'version':
                        //this.views.version.change(response.data);
                        // TODO: change the model and trigger a re-render of the view
                        break;
                    case 'data_request':
                        this.models.map.changeCurrent(response.data['page']);
                        this.models.language.changeCurrent(response.data['language']);
                        break;
                    case 'language_detected':
                        this.models.language.changeCurrent(response.data['language']);
                        break;
                    case 'map':
                        this.models.map.parse(response.data);
                        break;
                }
            },

            ping: function () {
                var root = this;

                this.connection.call('', {ping: 'ping'});

                setTimeout(function () {
                    root.ping();
                }, this.pingTimeout);
            },

            send: function (method, uri, params, callback) {
                if (typeof params !== 'object' || params === null) {
                    params = {};
                }

                this.connection.call(JSON.stringify(this.topic()), {
                    method: method,
                    uri: uri,
                    params: params
                }).then(
                    function (response) {
                        response = JSON.parse(response);
                        if (typeof callback === 'function') {
                            callback(response);
                        }
                    }
                );
            }
        })
    });
});