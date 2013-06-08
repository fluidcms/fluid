define(['backbone', 'ejs', 'jquery-ui', 'views/modal', 'views/contextmenu', 'models/structure'], function (Backbone, EJS, jUI, Modal, ContextMenu, Structure) {
    var View = Backbone.View.extend({
        events: {
            'click a[data-action=addPage]': 'addPage',
            'contextmenu li a': 'contextmenu'
        },

        className: 'structure',

        dropbox: {},

        template: new EJS({url: 'javascripts/fluid/templates/structure/structure.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;
            this.render();
            this.collection.on('reset add remove update', this.render, this);
            this.collection.on('saved', attrs.page.reload, this);
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
            var obj = this;
            this.$el.find('ul.structure, ul.pages').sortable({
                update: function (event, ui) {
                    var item = ui.item.attr('data-id');
                    var receiver = $(event.target).parents('li').attr('data-id');
                    if (typeof receiver == 'undefined') {
                        receiver = '';
                    }
                    obj.dropbox.position = ui.item.index();
                    obj.dropbox.item = item;
                    obj.dropbox.receiver = receiver;
                    clearTimeout(obj.dropbox.timeout);
                    obj.dropbox.timeout = setTimeout(function () {
                        obj.sort()
                    }, 10);
                },
                axis: "y",
                connectWith: ".structureSortable",
                placeholder: "sortable-placeholder"
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
            new ContextMenu({url: 'javascripts/fluid/templates/structure/contextmenu.ejs', parent: this, event: e}).render();
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
        template: new EJS({url: 'javascripts/fluid/templates/structure/page.ejs?' + (new Date()).getTime()}),  // !! Remove for production

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
                this.model.parent.add(this.model);
                this.model.set({
                    'path': this.model.id,
                    'index': this.model.parent.indexOf(this.model),
                    'id': null
                });
                this.model.id = null;
                this.model.save();
            } else {
                console.log(this.model);
                this.model.save();
            }
        }
    }));

    return View;
});