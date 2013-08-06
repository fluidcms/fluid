define(['backbone'], function (Backbone) {
    var Page = Backbone.Model.extend({
        url: 'page',

        socket: null,

        initialize: function (attrs, options) {
            this.socket = options.socket;
            this.languages = options.languages;
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

            this.socket.send('PUT', url, data, function(response) {
                response = root.parse(response);
                root.set(response);
            });
        },

        parse: function(response) {
            response.render = this.render(response.layoutDefinition, response.data);
            return response;
        },

        destroy: function() {
            this.unbind();
            this.trigger('destroy', this);
        },

        render: function(layoutDefinition, data) {
            var output = {};

            $.each(layoutDefinition, function(key, item) {
                if (typeof data[key] !== 'undefined') {
                    output[key] = Render(item, data[key]);
                }
            });

            return output;
        },

        saveData: function(group, item, data) {
            data = UnRender(this.get('layoutDefinition')[group][item], data);

            var modelData = this.get('data');
            if (modelData.length === 0) {
                modelData = {};
            }

            if (typeof modelData[group] === 'undefined') {
                modelData[group] = {};
            }

            modelData[group][item] = data;

            this.set('data', modelData);
            this.set('render', this.render(this.get('layoutDefinition'), this.get('data')));
            this.trigger('change');
            this.save();
        }
    });

    var Render = function(definitions, data) {
        var root = this;
        var output = {};
        var image = '<img src="%src" alt="" id="%id">';

        this.renderContent = function(content) {
            var output = content.source;

            // Components
            $.each(content.components, function(key, item) {
                console.log(item);
            });
            // Images
            $.each(content.images, function(key, item) {
                var html = image.replace('%src', item.src).replace('%id', key);
                output = output.replace('{'+key+'}', html);
            });

            return output;
        };

        $.each(definitions, function(key, item) {
            if (typeof data[key] !== 'undefined') {
                switch(item.type) {
                    case 'string':
                        output[key] = data[key];
                        break;
                    case 'content':
                        output[key] = root.renderContent(data[key]);
                        break;
                }
            }
        });

        return output;
    };

    var UnRender = function(definition, data) {
        var root = this;
        var output;
        var image = '<img src="%src" alt="" id="%id">';

        this.unRenderContent = function(content) {
            var output = {
                source: '',
                components: {},
                images: {}
            };

            // Images
            var images = content.match(/<img .+>/gi);
            if (images !== null) {
                $.each(content.match(/<img .+>/gi), function(key, value) {
                    var id = value.match(/id="([^"]*)"/)[1];

                    output.images[id] = {
                        src: value.match(/src="([^"]*)"/)[1],
                        alt: "",
                        width: "",
                        height: ""
                    };

                    content = content.replace(value, "{"+id+"}");
                });
            }

            output.source = content;
            return output;
        };

        switch(definition.type) {
            case 'string':
                output = data;
                break;
            case 'content':
                output = root.unRenderContent(data);
                break;
        }

        return output;
    };

    return Page;
});