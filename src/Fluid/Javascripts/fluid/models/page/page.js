define(['backbone', 'models/variables/variables'], function (Backbone, Variables) {
    return Backbone.Model.extend({
        url: 'page',

        socket: null,

        variables: null,

        initialize: function (attrs, options) {
            this.socket = options.socket;
            this.languages = options.languages;
            this.preview = options.preview;
            this.components = options.components;
            this.languages.on('change', this.changeLanguage, this);
        },

        changeLanguage: function() {
            this.set('language', this.languages.current.get('language'));
            this.fetch();
        },

        fetch: function() {
            var root = this;
            var url = this.url;

            if (typeof this.id !== 'undefined') {
                url = url + "/" + this.get('language') + '/' + this.id;
            } else {
                url = url + "/" + this.get('language') + "/global";
            }

            this.socket.send('GET', url, {}, function(response) {
                response = root.parse(response);
                root.set(response);
            });
        },

        save: function() {
            var root = this;
            var url = this.url;

            if (typeof this.id !== 'undefined') {
                url = url + "/" + this.languages.current.get('language') + "/" + this.id;
            } else {
                url = url + "/" + this.languages.current.get('language') + "/global";
            }

            var data = this.toJSON();

            if (typeof data['data'] !== 'object') {
                data = {};
            } else {
                data = data['data'];
            }

            // TODO: we need to add chaining for these so they don't overlap each others and bug the application
            this.socket.send('PUT', url, data, function(response) {
                response = root.parse(response);
                root.set(response);
                root.trigger('change');
                root.preview.reload();
            });
        },

        parse: function(response) {
            this.variables = new Variables(null, {
                components: this.components,
                data: response.data,
                definition: response.layoutDefinition // TODO: rename layoutDefinition to definition
            });
            response.render = this.variables.toHTML(); // TODO: rename to render to html
            return response;
        },

        destroy: function() {
            this.unbind();
            this.trigger('destroy', this);
        },

        saveData: function(group, item, data) {
            var variable = this.variables.toJSON(data, item, group);

            data = this.get('data');

            if (typeof data[group] === 'undefined') {
                data[group] = {};
            }

            data[group][item] = variable;

            this.set('data', data);
            this.variables.updateData(data);
            this.set('render', this.variables.toHTML());
            this.trigger('change');
            this.save();
        }
    });
});