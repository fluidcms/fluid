define(['backbone', 'models/file/file'], function (Backbone, File) {
    return Backbone.Collection.extend({
        model: File,

        url: '/files',

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
            var root = this;
            $.each(response, function() {
                this.socket = root.socket;
            });
            this.reset(response);
        },

        comparator: function (file) {
            return file.get('creation') * -1;
        },

        addFile: function (file) {
            var model = new File({
                id: randomString(8),
                name: file.name,
                size: file.size,
                type: file.type,
                creation: Math.round((new Date()).getTime() / 1000)
            }, {file: file});

            this.add(model);
        }
    });
});
