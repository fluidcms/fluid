define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        conn: null,
        views: {},

        initialize: function (attrs) {
            this.views.version = attrs.version;

            this.connection();
        },

        connection: function() {
            var root = this;
            root.conn = new ab.Session(
                fluidWebSocketUrl,
                function() {
                    root.conn.subscribe(fluidBranch + "/" + fluidUserId, function(topic, message) {
                        root.parse(topic, message);
                    });
                },
                function() {
                    if (error !== null) {
                        alert('An error as occured: ' + error);
                    }
                    location.reload();
                },
                {'skipSubprotocolCheck': true}
            );
        },

        parse: function(topic, message) {
            var response = $.parseJSON(message);
            switch(response.target) {
                case 'version':
                    this.views.version.change(response.data);
                    break;
            }
        }
    });
});