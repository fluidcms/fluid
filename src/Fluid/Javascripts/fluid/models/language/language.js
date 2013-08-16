define(['backbone'], function (Backbone) {
    var Language = Backbone.Model.extend({
    });

    var Languages = Backbone.Collection.extend({
        url: 'language',

        model: Language,

        current: null,

        initialize: function (items, attrs) {
            this.socket = attrs.socket;
        },

        changeCurrent: function(item) {
            this.current = this.where({language: item})[0];
            this.trigger('change');
        },

        fetch: function () {
            var root = this;
            this.socket.send('GET', this.url, {}, function(response) {
                root.reset(response);
                root.current = root.at(0);
                root.trigger('change');
            });
        }
    });

    return {
        Language: Language,
        Languages: Languages
    };
});
