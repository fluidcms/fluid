define(['backbone', 'ejs', 'jquery-ui', 'views/modal', 'views/contextmenu'], function (Backbone, EJS, jUI, Modal, ContextMenu) {
    var View = Backbone.View.extend({
        events: {
            'click a[data-action=addPage]': 'addPage',
            'contextmenu li a': 'contextmenu'
        },

        className: 'map',

        dropbox: {},

        template: new EJS({url: 'javascripts/fluid/templates/map/map.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;
            this.render();
            this.collection.on('reset add remove update', this.render, this);
//            this.collection.on('saved', attrs.page.reload, this); // TODO: uncomment
            this.languages = attrs.languages;
            this.layouts = attrs.layouts;

            // Control + Z or Command + Z events
            setTimeout(function () {
                $(document).keydown(function (e) {
                    if ((e.ctrlKey || e.metaKey) && e.keyCode == 90) {
                        root.cancelChange();
                    }
                });
            }, 1);
        },

        render: function () {
            this.$el.html(this.template.render({pages: this.collection}));
            $("#main #content").append(this.$el);
            this.sortable();
            return this;
        },

        sortable: function () {
            var root = this;
            this.$el.find('ul.map, ul.pages').sortable({
                over: function(event, ui) {
                    $(event.target).parents('li:eq(0)').find('>span>a').addClass('dragover');
                },
                out: function(event, ui) {
                    $(event.target).parents('li:eq(0)').find('>span>a').removeClass('dragover');
                },
                start: function (event, ui) {
                    // Stop on escape
                    $(document).on('keydown', function (e) {
                        if (e.keyCode == 27) {
                            root.$el.find('ul.map, ul.pages').sortable("cancel");
                        }
                    });

                    ui.item.addClass("highlight").find('a').addClass("highlight");
                },
                stop: function (event, ui) {
                    $(document).off('keydown');

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
                        //root.sort()
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

        cancelChange: function() {
            console.log('cancel change');
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
            new Page({ model: new Structure.Page({ parent: parent }), languages: this.languages, layouts: this.layouts, newPage: true }).render();
        },

        editPage: function (page) {
            new Page({ model: this.collection.findItem($(page).parents('li').attr('data-id')), languages: this.languages, layouts: this.layouts }).render();
        },

        deletePage: function (page) {
            this.collection.removeItem($(page).parents('li').attr('data-id'));
        }
    });

    var Page = Backbone.View.extend($.extend({}, Modal, {
        template: new EJS({url: 'javascripts/fluid/templates/map/page.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.languages = attrs.languages;
            this.layouts = attrs.layouts;
            if (typeof attrs.newPage !== 'undefined') {
                this.newPage = true;
            } else {
                this.newPage = false;
            }
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

                data.id = null;
                data.parent = parent;
                data.path = this.model.getId();
                data.index = parent.indexOf(this.model);

                parent.remove(this.model);
                parent.create(data);
            } else {
                this.model.save();
            }
        }
    }));

    return View;
});