define(['backbone'], function (Backbone) {
    var Page = Backbone.Model.extend({
        parent: null,

        initialize: function (attrs) {
            this.parent = attrs.parent;
            if (typeof attrs.pages != 'undefined') {
                this.set('pages', new Pages(attrs.pages, {parent: this}))
            } else {
                this.set('pages', new Pages([], {parent: this}))
            }

            if (typeof this.get('id') == 'undefined') {
                this.set('id', this.getId());
            }

            if (typeof this.get('origin') == 'undefined') {
                this.set('origin', this.get('id'));
            }

            this.on('change', function (e) {
                this.set('id', this.getId());
                this.parent.trigger('update');
            });
        },

        resetOrigin: function () {
            this.set('origin', this.getId());
            if (this.get('pages').length) {
                this.get('pages').each(function (item) {
                    item.resetOrigin();
                });
            }
        },

        getId: function () {
            var parent = this.parent;
            var id = this.get('page');

            while (parent != null) {
                if (parent instanceof Pages) {
                    parent = parent.parent;
                } else {
                    id = parent.get('page') + '/' + id;
                    parent = parent.parent;
                }
            }

            return id;
        },

        toJSON: function () {
            var output = _.clone(this.attributes);
            delete output.parent;
            output.id = output.origin;
            delete output.origin;
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

        url: fluidBranch + '/structure',

        parent: null,

        initialize: function (items, attrs) {
            if (this.parent == null && (typeof attrs == 'undefined' || typeof attrs.parent == 'undefined')) {
                this.fetch();
            } else {
                this.parent = attrs.parent;
                var parent = this;
                $.each(items, function () {
                    this.parent = parent;
                });
            }

            this.on('all', function (e) {
                if (this.parent != null && this.parent.parent != null) {
                    this.parent.parent.trigger(e);
                }
            });

            this.on('saved', function (e) {
                if (this.parent == null) {
                    this.resetOrigins();
                }
            });
        },

        save: function () {
            var obj = this;
            Backbone.sync('update', this, {success: function () {
                obj.trigger('saved');
            }});
        },

        parse: function (response) {
            var parent = this;
            $.each(response, function () {
                this.parent = parent;
                this.id = this.page;
            });
            return response;
        },

        sort: function (item, receiver, position) {
            var item = this.findItem(item);
            var receiver = this.findItem(receiver);
            if (typeof receiver == 'undefined') {
                receiver = this;
            } else {
                receiver = receiver.get('pages');
            }

            var data = item.toJSON();
            data.parent = receiver;

            item.parent.remove(item);
            receiver.add(data, {at: position});

            receiver.models[position].set('id', receiver.models[position].getId());
            receiver.models[position].attributes.id = receiver.models[position].get('id');

            this.trigger('change');
        },

        removeItem: function (item) {
            var item = this.findItem(item);
            item.parent.remove(item);

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
        },

        resetOrigins: function () {
            this.each(function (item) {
                item.resetOrigin();
            });
        }
    });

    return {
        Page: Page,
        Pages: Pages
    };
});