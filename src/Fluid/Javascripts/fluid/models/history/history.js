define(['backbone'], function (Backbone) {
    var Step = Backbone.Model.extend({
        initialize: function (attrs) {
        },

        getReadableTime: function() {
            var time = Date.parse(this.get('date'));
            var since = Math.round(((new Date().getTime()) - time) / 1000);

            if (since < 60) {
                if (since === 1) {
                    return fluidLanguage['time']['second'].replace('%s', since);
                } else {
                    return fluidLanguage['time']['seconds'].replace('%s', since);
                }
            } else if (since < 3600) {
                since = Math.floor(since / 60);
                if (since === 1) {
                    return fluidLanguage['time']['minute'].replace('%s', since);
                } else {
                    return fluidLanguage['time']['minutes'].replace('%s', since);
                }
            } else if (since < 86400) {
                since = Math.floor(since / 60 / 60);
                if (since === 1) {
                    return fluidLanguage['time']['hour'].replace('%s', since);
                } else {
                    return fluidLanguage['time']['hours'].replace('%s', since);
                }
            } else {
                since = Math.floor(since / 60 / 60 / 24);
                if (since === 1) {
                    return fluidLanguage['time']['day'].replace('%s', since);
                } else {
                    return fluidLanguage['time']['days'].replace('%s', since);
                }
            }
        }
    });

    return Backbone.Collection.extend({
        socket: null,
        model: Step,

        url: 'history',

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
            $.each(response, function() {
                this.gravatar = md5(this.user_email);
            });
            this.reset(response);
        },

        rollBack: function(id) {
            var root = this;
            this.socket.send('PUT', this.url + "/" + id, {}, function(response) {
                root.parse(response);
            });
        }
    });
});