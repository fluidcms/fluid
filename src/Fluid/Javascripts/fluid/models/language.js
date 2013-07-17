define(['backbone'], function (Backbone) {
    var Language = Backbone.Model.extend({
    });

    var Languages = Backbone.Collection.extend({
        url: 'language',

        model: Language,

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
        Language: Language,
        Languages: Languages
    };
});
