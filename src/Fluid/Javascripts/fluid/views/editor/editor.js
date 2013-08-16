define(['backbone', 'ejs', 'jquery-ui', 'views/editor/helper', 'views/helpers/contextmenu', 'views/components/component'], function (Backbone, EJS, jUI, Editor, ContextMenu, ComponentView) {
    return Backbone.View.extend({
        events: {
            "click [data-action=cancel]": "close",
            "click [data-action=save]": "save",
            'contextmenu div[data-component]': 'componentContextMenu',
            'click div[data-component]': 'editComponent'
        },

        type: null,

        className: 'editor',

        template: new EJS({url: 'javascripts/fluid/templates/editor/editor.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;

            this.type = attrs.type;
            this.model = attrs.model;
            this.group = attrs.group;
            this.item = attrs.item;
            this.components = attrs.components;

            this.render();

            this.keyEvents = {
                // Control + S or Command + S events
                'save': function (e) {
                    if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
                        root.save();
                    }
                },
                // Escape key event
                'escape': function (e) {
                    if (e.keyCode == 27) {
                        root.close();
                    }
                }
            };

            $(document).on('keydown', this.keyEvents.save);
            $(document).on('keyup', this.keyEvents.escape);
        },

        render: function () {
            var render = this.model.get('render');
            var content;

            if (typeof render[this.group] === 'undefined' || typeof render[this.group][this.item] === 'undefined') {
                content = "";
            } else {
                content = render[this.group][this.item];
            }

            this.$el.html(this.template.render({
                type: this.type,
                content: content
            }));
            $(".page-editor").after(this.$el);

            Editor(this.$el.find('div[contenteditable]'), this.type);

            this.droppable();

            this.$el.find("div[contenteditable]").focus();

            return this;
        },

        componentContextMenu: function (e) {
            e.preventDefault();
            new ContextMenu({url: 'javascripts/fluid/templates/editor/componentcm.ejs', parent: this, event: e}).render();
        },

        save: function() {
            this.trigger('save');
            this.model.saveData(this.group, this.item, this.$el.find('div[contenteditable]').html());
            this.close();
        },

        close: function() {
            this.trigger('close');
            $(document).off('keydown', this.keyEvents.save);
            $(document).off('keyup', this.keyEvents.escape);
            this.remove();
        },

        droppable: function() {
            var root = this;
            this.$el.find('div[contenteditable]').sortable({
                cancel: "p,h1,h2,h3,h4,h5,h6,ul,li,a:not(.component)",
                activeClass: "",
                receive: function( event, ui ) {
                    var item = $(this).find('>a.component');
                    root.addComponent(item);
                }
            });
        },

        addComponent: function(item) {
            var id = item.attr('data-component');
            item.removeAttr('data-component');
            item.attr('contenteditable', 'false');
            item.wrap('<div id="'+randomString(8)+'" data-component="'+id+'"></div>');
            this.editComponent($(item).parents('div[data-component]')[0]);
        },

        editComponent: function(e) {
            var element;
            if (typeof e.tagName === 'string') {
                if ($(e).attr('data-component')) {
                    element = $(e);
                } else {
                    element = $(e).parents('div[data-component]');
                }
            } else if (typeof e.currentTarget !== 'undefined') {
                element = $(e.currentTarget);
            }

            if (typeof element !== 'undefined' && element !== null) {
                var id = element.attr('id');
                var model = this.components.findWhere({'component': element.attr('data-component')});
                var data = this.model.get('data');

                if (
                    typeof data[this.group] !== 'undefined' &&
                    typeof data[this.group][this.item] !== 'undefined' &&
                    typeof data[this.group][this.item]['components'] !== 'undefined' &&
                    typeof data[this.group][this.item]['components'][id] !== 'undefined'
                ) {
                    data = data[this.group][this.item]['components'][id];
                } else {
                    data = {};
                }

                new ComponentView({
                    definition: model,
                    data: data
                });
//                console.log(id, model);
//                console.log(this.model, this.group, this.item);
            }
        },

        deleteComponent: function(e) {
            var element;
            if (typeof e.tagName === 'string') {
                if ($(e).attr('data-component')) {
                    element = $(e);
                } else {
                    element = $(e).parents('div[data-component]');
                }
            }

            if (typeof element !== 'undefined' && element !== null) {
                var id = element.attr('id');
                // TODO: delete component from model
                element.remove();
            }
        }
    });
});
