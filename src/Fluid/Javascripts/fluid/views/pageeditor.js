define(['backbone', 'ejs', 'jquery-ui', 'views/contextmenu', 'views/editor/content'], function (Backbone, EJS, jUI, ContextMenu, ContentEditor) {
    var View = Backbone.View.extend({
        events: {
//            'change form': 'change',
//            'submit form': 'submit',
//            'click [data-action="cancel"]': 'cancel',
//            'click nav a': 'tab',
//            'contextmenu label[data-action="array"]': "arrayContextmenu",
//            'contextmenu ul.array li': "arrayItemContextmenu"

            "click div.content": "editContent"
        },

        className: 'page-editor',

        template: new EJS({url: 'javascripts/fluid/templates/pageeditor/editor.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
//            this.model = attrs.page;
//            this.toolbar = attrs.toolbar;
//            this.tree = {
//                site: attrs.site,
//                page: attrs.page
//            };
//            this.current = 'page';
            this.model.on('change', this.render, this);

        },

        render: function () {
            this.$el.html(this.template.render({
                layoutDefinition: this.model.get('layoutDefinition'),
                data: this.model.get('data'),
                render: this.model.get('render')
            }));
            $("#website").after(this.$el);
            this.changeGroup();
//            this.sortable();
            return this;
        },

        editContent: function(e) {
            var group = $(e.target).parents('a').attr('data-group');
            var item = $(e.target).parents('a').attr('data-item');

            new ContentEditor({group: group, item: item, model: this.model});
        },













        changeGroup: function() {
            this.$el.find('nav li:first').addClass('current');
        },

        sortable: function () {
            var root = this;
            this.$el.find('ul.array').sortable({
                update: function (event, ui) {
                    root.updateIds();
                    root.$el.find("form").trigger("change");
                },
                axis: "y",
                placeholder: "sortable-placeholder"
            });
        },

        tab: function (e) {
            var target = $(e.target).attr('data-target');
            this.current = target;
            this.model = this.tree[target];
            this.render();
        },

        change: function (e) {
            var data = {};
            $.each($(e.currentTarget).serializeArray(), function (key, item) {
                if (item.name.match(/[\[\]]/)) {
                    var keys = item.name.split('[');
                    var itemKey = '';
                    for (var i = 0; i < keys.length; i++) {
                        keys[i] = keys[i].replace(/[\]'"]/g, "");
                        if (/^\d+$/.test(keys[i])) {
                            itemKey += "[" + keys[i] + "]";
                        } else {
                            itemKey += "['" + keys[i] + "']";
                        }

                        eval("if (typeof data" + itemKey + " == 'undefined') { data" + itemKey + " = {}; }");
                    }
                    eval("data" + itemKey + " = item.value;");
                } else {
                    data[item.name] = item.value;
                }
            });

            this.model.set('data', data);
        },

        submit: function (e) {
            e.preventDefault();
            var root = this;
            this.model.save(null, {success: function () {
                $("#website")[0].contentWindow.location.reload();
                root.toolbar.previewPage();
            }});
        },

        cancel: function (e) {
            $("#website")[0].contentWindow.location.reload();
            this.toolbar.previewPage();
        },

        arrayContextmenu: function (e) {
            e.preventDefault();
            new ContextMenu({url: 'javascripts/fluid/templates/pageeditor/arraycontextmenu.ejs', parent: this, event: e}).render();
        },

        arrayItemContextmenu: function (e) {
            e.preventDefault();
            new ContextMenu({url: 'javascripts/fluid/templates/pageeditor/itemcontextmenu.ejs', parent: this, event: e}).render();
        },

        addItem: function (target) {
            if ($(target).parents("ul.array").length == 0) {
                var list = $(target).next('ul');
            } else {
                var list = $(target).parents("ul.array");
            }

            var count = list.find('li').length - 1;
            var clone = list.find('li[data-role="clone"]').clone();
            clone.removeAttr("data-role").removeAttr("style");
            list.find('li[data-role="clone"]').before(clone);
            this.updateIds();
            this.$el.find("form").trigger("change");
        },

        deleteItem: function (target) {
            if (target.tagName !== 'LI') {
                target = $(target).parent('li');
            }

            $(target).remove();
            this.updateIds();
            this.$el.find("form").trigger("change");
        },

        updateIds: function () {
            $.each(this.$el.find("ul.array"), function (key, list) {
                var count = 0;
                $.each($(list).find('li:not([data-role="clone"])'), function (key, item) {
                    $.each($(item).find("*"), function (key, element) {
                        var attrs = ['for', 'name', 'id'];
                        for (var i = 0; i < attrs.length; i++) {
                            if ($(element).attr(attrs[i])) {
                                $(element).attr(attrs[i], $(element).attr(attrs[i]).replace('%d', count)).removeAttr('disabled');
                                if (attrs[i] == 'name') {
                                    $(element).attr(attrs[i], $(element).attr(attrs[i]).replace(/\[\d*\]/, '[' + count + ']'));
                                } else {
                                    $(element).attr(attrs[i], $(element).attr(attrs[i]).replace(/^pageEditorArray_\d*/, '[pageEditorArray_' + count));
                                }
                            }
                        }
                    });
                    count++;
                });
            });
        }
    });

    return View;
});
