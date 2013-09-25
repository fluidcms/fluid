define(
    ['backbone', 'ejs', 'jquery-ui', 'views/helpers/modal', 'views/helpers/contextmenu', 'models/map/map'],
    function (Backbone, EJS, jUI, Modal, ContextMenu, Map) {
        var View = Backbone.View.extend({
            events: {
                'click a[data-action=addPage]': 'addPage',
                'contextmenu li a': 'contextmenu',
                'click a.global': 'editPage',
                'click ul.map a': 'editPage'
            },

            rendered: false,

            className: 'map',

            dropbox: {},

            template: new EJS({url: 'javascripts/fluid/templates/map/map.ejs?' + (new Date()).getTime()}),  // !! Remove for production

            initialize: function (attrs) {
                var root = this;
                this.render();
                this.collection.on('reset add remove update editing', this.render, this);
                this.languages = attrs.languages;
                this.layouts = attrs.layouts;
            },

            render: function () {
                var root = this;
                var current;
                if (typeof this.collection.editor.page !== 'undefined') {
                    current = this.collection.editor.page.get('id');
                    if (typeof current === 'undefined') {
                        current = 'global';
                    }
                } else if (typeof this.collection.current === 'undefined') {
                    current = "";
                } else {
                    current = this.collection.current;
                }

                if (this.rendered === true) {
                    var scroll = this.$el.scrollTop();
                }

                // TODO: live tracking of the scroll

                this.$el.html(this.template.render({
                    pages: this.collection,
                    current: current
                }));

                this.rendered = true;

                $("#main #content").append(this.$el);

                if (typeof scroll !== 'undefined') {
                    this.$el.scrollTop(scroll);
                    setTimeout(function() {
                        root.$el.scrollTop(scroll);
                    }, 10);
                }

                this.sortable();

                return this;
            },

            hide: function() {
                this.$el.hide();
            },

            show: function() {
                this.$el.show();
            },

            sortable: function () {
                var root = this;

                var cancelSort = function(e) {
                    if (e.keyCode == 27) {
                        root.$el.find('ul.map, ul.pages').sortable("cancel");
                    }
                };

                this.$el.find('ul.map, ul.pages').sortable({
                    distance: 25,
                    over: function(event, ui) {
                        $(event.target).parents('li:eq(0)').find('>span>a').addClass('dragover');
                    },
                    out: function(event, ui) {
                        $(event.target).parents('li:eq(0)').find('>span>a').removeClass('dragover');
                    },
                    start: function (event, ui) {
                        // Stop on escape
                        $(document).on('keydown', cancelSort);

                        ui.item.addClass("highlight").find('a').addClass("highlight");
                    },
                    stop: function (event, ui) {
                        $(document).off('keydown', cancelSort);

                        ui.item.removeClass("highlight").find('a').removeClass("highlight");
                    },
                    update: function (event, ui) {
                        var item = ui.item.attr('data-id');
                        var receiver = $(event.target).parents('li').attr('data-id');
                        if (typeof receiver == 'undefined') {
                            receiver = '';
                        }
                        root.dropbox.position = ui.item.index();
                        root.dropbox.item = item;
                        root.dropbox.receiver = receiver;
                        clearTimeout(root.dropbox.timeout);
                        root.dropbox.timeout = setTimeout(function () {
                            root.sort()
                        }, 10);
                    },
                    axis: "y",
                    connectWith: ".mapSortable",
                    placeholder: false
                });
            },

            sort: function () {
                this.collection.sort(this.dropbox.item, this.dropbox.receiver, this.dropbox.position);
            },

            contextmenu: function (e) {
                e.preventDefault();
                new ContextMenu({url: 'javascripts/fluid/templates/map/contextmenu.ejs', parent: this, event: e}).render();
            },

            addPage: function (e) {
                if ($(e).parents('li').length > 0) {
                    var parent = this.collection.findItem($(e).parents('li').attr('data-id')).get('pages');
                } else {
                    var parent = this.collection;
                }

                var page = new Map.Page({
                    base: this.collection,
                    parent: parent
                });
                var pageView = new PageView({
                    model: page,
                    languages: this.languages,
                    layouts: this.layouts,
                    newPage: true
                });
                pageView.render();
            },

            configPage: function (page) {
                new PageView({ model: this.collection.findItem($(page).parents('li').attr('data-id')), languages: this.languages, layouts: this.layouts }).render();
            },

            deletePage: function (page) {
                this.collection.removeItem($(page).parents('li').attr('data-id'));
            },

            editPage: function(page) {
                if (typeof page.target === 'object') {
                    page = page.target;
                }

                this.collection.startEditing($(page).attr('data-id'));
            }
        });

        // TODO: rename to page config
        var PageView = Backbone.View.extend($.extend({}, Modal, {
            template: new EJS({url: 'javascripts/fluid/templates/map/page.ejs?' + (new Date()).getTime()}),  // !! Remove for production

            initialize: function (attrs) {
                this.languages = attrs.languages;
                this.layouts = attrs.layouts;
                this.newPage = (typeof attrs.newPage !== 'undefined');
            },

            renderData: function () {
                return {
                    languages: this.languages,
                    layouts: this.layouts
                };
            },

            submit: function () {
                if (this.newPage) {
                    var parent = this.model.parent;
                    parent.add(this.model, {silent: true});

                    var data = this.model.toJSON();
                    data.base = this.model.base;
                    data.parent = parent;
                    data.index = parent.indexOf(this.model);

                    parent.remove(this.model);

                    parent.create(data);
                } else {
                    this.model.save();
                }
            }
        }));

        return View;
    }
);