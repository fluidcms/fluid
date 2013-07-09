define(['backbone'], function (Backbone) {
    var Page = Backbone.Model.extend({
        base: null,
        parent: null,
        urlRoot: 'map',

        initialize: function (attrs) {
            this.base = attrs.base;
            this.parent = attrs.parent;
            if (typeof attrs.pages != 'undefined') {
                this.set('pages', new Pages(attrs.pages, {parent: this, base: this.base}))
            } else {
                this.set('pages', new Pages([], {parent: this, base: this.base}))
            }

            this.on('sync', function (e) {
                this.parent.trigger('update');
            });
        },

        destroy: function() {
            var root = this;
            this.base.socket.send(
                'DELETE',
                this.urlRoot + "/" + encodeURIComponent(this.id),
                null,
                function(response) {
                    root.base.parse(response);
                }
            );
        },

        toJSON: function () {
            var output = _.clone(this.attributes);
            delete output.base;
            delete output.parent;
            if (this.get('pages').length) {
                output.pages = this.get('pages').toJSON();
            } else {
                delete output.pages;
            }
            return output;
        },

        validate: function (attrs, options) {
            // Validate page
            if (attrs.page === '') {
                this.validationErrorAttr = 'page';
                return 'You must enter a page.';
            }
            if (!attrs.page.match(/^[a-z0-9_]*$/i)) {
                this.validationErrorAttr = 'page';
                return 'The page must contain only letters, numbers and underscores.';
            }
            return '';
        }
    });

    var Pages = Backbone.Collection.extend({
        model: Page,

        base : null,

        url: 'map',

        parent: null,

        initialize: function (items, attrs) {
            var root = this;
            this.socket = attrs.socket;
            this.base = attrs.base;

            if (this.parent == null && (typeof attrs == 'undefined' || typeof attrs.parent == 'undefined')) {
                this.fetch();
            } else {
                this.parent = attrs.parent;
                $.each(items, function () {
                    this.base = root.base;
                    this.parent = root;
                });
            }

            this.on('all', function (e) {
                if (typeof this.base !== 'undefined') {
                    this.base.trigger(e);
                }
            });
        },

        fetch: function () {
            var root = this;
            this.socket.send('GET', this.url, {}, function(response) {
                root.parse(response);
            });
        },

        parse: function (response) {
            var root = this;
            $.each(response, function () {
                this.base = root;
                this.parent = root;
            });
            this.reset(response);
            return response;
        },

        sort: function (item, receiver, position) {
            var root = this;
            if (receiver == 'undefined') {
                receiver = '';
            }

            this.socket.send(
                'PUT',
                this.url + "/sort/" + encodeURIComponent(item),
                {
                    page: receiver,
                    index: position
                },
                function(response) {
                   root.parse(response);
                }
            );
        },

        removeItem: function (item) {
            item = this.findItem(item);
            item.parent.remove(item);
            item.destroy();
            this.trigger('change');
        },

        findItem: function (item) {
            if (item == '') {
                return undefined;
            }
            var collection = this;
            var model;
            var items = item.split("/");
            $.each(items, function (index, value) {
                model = collection.find(function (model) {
                    return model.get('page') == value;
                });
                if (typeof model.get('pages') != 'undefined') {
                    collection = model.get('pages');
                }
            });

            return model;
        }
    });

    return {
        Page: Page,
        Pages: Pages
    };
})
;