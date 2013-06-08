define(['backbone'], function (Backbone) {
    var Page = Backbone.Model.extend({
        parent: null,
        urlRoot: fluidBranch + '/structure',

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

            this.on('sync', function (e) {
                this.set('origin', this.get('id'));
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

        // TODO get ID from the server response would be a better way
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

            parse: function (response) {
                var parent = this;
                $.each(response, function () {
                    this.parent = parent;
                    this.id = this.page;
                });
                return response;
            },

            sort: function (item, receiver, position) {
                var root = this;
                if (receiver == 'undefined') {
                    receiver = '';
                }

                $.ajax({
                    url: this.url + "/sort/" + encodeURIComponent(item),
                    dataType   : 'json',
                    contentType: 'application/json',
                    type: "PUT",
                    data: JSON.stringify({
                        page: receiver,
                        index: position
                    })
                }).done(
                    function (response) {
                        if (response == true) {
                            item = root.findItem(item);
                            receiver = root.findItem(receiver);
                            if (typeof receiver == 'undefined') {
                                receiver = root;
                            } else {
                                receiver = receiver.get('pages');
                            }

                            var data = item.toJSON();
                            data.parent = receiver;

                            item.parent.remove(item);
                            receiver.add(data, {at: position});

                            receiver.models[position].set('id', receiver.models[position].getId());
                            receiver.models[position].attributes.id = receiver.models[position].get('id');
                        }
                        root.trigger('update');
                    }
                ).error(
                    function (XMLHttpRequest) {
                        root.trigger('update');
                        var error = XMLHttpRequest.getResponseHeader('X-Error-Message');
                        if (error !== null) {
                            alert('An error as occured: ' + error);
                        } else {
                            alert('The connection has timed out.');
                        }
                        location.reload();
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
            },

            resetOrigins: function () {
                this.each(function (item) {
                    item.resetOrigin();
                });
            }
        })
        ;

    return {
        Page: Page,
        Pages: Pages
    };
})
;