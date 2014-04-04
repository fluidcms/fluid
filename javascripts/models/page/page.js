define([
    'backbone',
    'models/variable/variable-collection',
    'models/page/page-config',
    'models/template/template'
], function (Backbone, VariableCollection, PageConfig, Template) {
    return Backbone.Model.extend({
        urlRoot: '/page',
        socket: null,
        variables: null,
        config: null,
        template: null,

        saving: false,
        chain: false,

        initialize: function (attributes, options) {
            this.socket = this.collection.socket;
            this.variables = new VariableCollection(null, {page: this});
            this.config = new PageConfig;
            this.template = new Template;
            /*tthis.languages = options.languages;
            this.preview = options.preview;
            this.components = options.components;
            this.languages.on('change', this.changeLanguage, this);*/
        },

        changeLanguage: function() {
            //this.set('language', this.languages.current.get('language'));
            //this.fetch();
        },

        parse: function(response) {
            if (typeof response.variables !== 'undefined') {
                this.variables.reset(response.variables);
            }
            if (typeof response.config !== 'undefined') {
                this.config.set(response.config);
            }
            if (typeof response.template !== 'undefined') {
                this.template.set(response.template);
            }
            return [];
        },

        fetch: function() {
            var root = this;
            this.socket.send('GET', this.url(), {}, function(response) {
                root.parse(response);
                root.trigger('sync change reset');
            });


            /*var root = this;
            var url = this.url;

            if (typeof this.id !== 'undefined') {
                url = url + "/" + this.get('language') + '/' + this.id;
            } else {
                url = url + "/" + this.get('language') + "/global";
            }

            this.socket.send('GET', url, {}, function(response) {
                response = root.parse(response);
                root.set(response);
            });*/
        },

        save: function(attributes, options) {
            var root = this;
            this.socket.send('POST', this.url(), this.toJSON(), function(response) {
                root.trigger('sync');
                if (typeof options.success === 'function') {
                    options.success.call(response);
                }
            });

            /*var root = this;
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

            if (!this.saving) {
                this.saving = true;
                this.socket.send('PUT', url, data, function(response) {
                    if (!root.chain) {
                        response = root.parse(response);
                        root.set(response);
                        root.trigger('change');
                        root.preview.reload();
                        root.saving = false;
                    } else {
                        root.saving = false;
                        root.chain = false;
                        root.save();
                    }
                });
            } else {
                this.chain = true;
            }*/
        },

        toJSON: function(options) {
            var retval = Backbone.Model.prototype.toJSON.call(this, options);
            retval.variables = this.variables.toJSON();
            return retval;
        },

        /*parse: function(response) {
            this.variables = new Variables(null, {
                components: this.components,
                data: response.data,
                definition: response.layoutDefinition // TODO: rename layoutDefinition to definition
            });
            response.render = this.variables.toHTML(); // TODO: rename to render to html
            return response;
        },*/

        destroy: function() {
            this.unbind();
            this.trigger('destroy', this);
        },

        requestContent: function(group, item, lang, callback) {
            var root = this;
            var url = 'page_variable/' + lang + '/';

            if (typeof this.id !== 'undefined') {
                url = url  + this.id;
            } else {
                url = url + "global";
            }

            url = url + '/' + group + '/' + item;

            this.socket.send('GET', url, {}, function(response) {
                callback(response, root.variables.contentToHTML(response));
            });
        },

        saveData: function(group, item, data) {
            var variable = this.variables.toJSON(data, item, group);

            data = this.get('data');

            if ($.isArray(data) && data.length == 0) {
                data = {};
            }

            if (typeof data[group] === 'undefined') {
                data[group] = {};
            }

            data[group][item] = variable;

            this.variables.updateData(data);

            this.set({
                'data': data,
                'render': this.variables.toHTML()
            }, { silent: true });

            this.trigger('change');

            this.save();
        }
    });
});