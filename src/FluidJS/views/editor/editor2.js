define([
    'backbone',
    'ejs',
    'jquery-ui',
    'views/editor/helper',
    'views/helpers/contextmenu'
], function (
    Backbone,
    EJS,
    jUI,
    EditorHelper,
    ContextMenu
    ) {
    return Backbone.View.extend({
        events: {
            "click [data-action=cancel]": "close",
            "click [data-action=save]": "save",
            'contextmenu div[data-component]': 'componentContextMenu',
            'click div[data-component]': 'editComponent'
        },

        initialize: function (attrs) {
            var root = this;

            this.$el = $(attrs.el);

            this.app = attrs.app;
            this.files = attrs.files;
            this.tools = attrs.tools;
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
                        // TODO: fix this
                        //root.close();
                    }
                },
                // Enter key events
                'enter': function(e) {
                    if (root.type == 'string' && e.keyCode == 13) {
                        root.save();
                    }
                }
            };

            $(document).on('keydown', this.keyEvents.save);
            $(document).on('keydown', this.keyEvents.enter);
            $(document).on('keyup', this.keyEvents.escape);
        },

        render: function () {
            var root = this;

            EditorHelper(this.$el, 'content', this); // TODO: integrate into this view

            this.$el.on('focus', function() {
                setTimeout(function() {
                    root.trigger('focus');
                }, 0);
            });

            this.$el.on('blur', function() {
                setTimeout(function() {
                    root.trigger('blur');
                }, 0);
            });

            this.tools.register(this);

            this.droppable();

            return this;
        },

        componentContextMenu: function (e) {
            e.preventDefault();
            new ContextMenu({url: ' javascripts/templates/editor/componentcm.ejs', parent: this, event: e}).render();
        },

        save: function() {
            var content = this.$el.html();

            if (this.type === 'string') {
                this.data = content;
            } else if (this.type === 'content') {
                if (typeof this.data === 'undefined' || this.data === null) {
                    this.data = {};
                }
                this.data.source = content;
            }

            this.trigger('save');
            this.close();
        },

        close: function() {
            this.trigger('close');

            delete this.app.editors[this.cid];

            $(document).off('keydown', this.keyEvents.save);
            $(document).off('keydown', this.keyEvents.enter);
            $(document).off('keyup', this.keyEvents.escape);
            this.tools.disable();
            this.remove();
        },

        droppable: function() {
            var root = this;
            this.$el.sortable({
                cancel: "p,h1,h2,h3,h4,h5,h6,ul,li,b,u,i,a:not(.component)", // TODO include instead of exclude
                activeClass: "",
                receive: function( event, ui ) {
                    var item = $(this).find('>a.component');
                    root.addComponent(item);
                }
            });
        },

        addComponent: function(item) {
            var id = randomString(8);
            var componentName = item.attr('data-component');
            var component = $('<div id="' + id + '" data-component="' + componentName + '" contenteditable="false" class="component"></div>');
            component.html(item.html());
            item.before(component);
            item.remove();

            if (typeof this.data === 'undefined' || this.data === null) {
                this.data = {};
            }

            if (typeof this.data.components === 'undefined' || this.data.components === null) {
                this.data.components = {};
            }

            this.data.components[id] = {
                component: componentName,
                data: {}
            };
        },

        editComponent: function(e) {
            var root = this;
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

                var definition = this.components.findWhere({'component': element.attr('data-component')});

                var component = this.data['components'][id];

                require(['views/components/component'], function (ComponentView) {
                    root.hide();

                    var componentView = new ComponentView({
                        app: root.app,
                        components: root.components,
                        files: root.files,
                        definition: definition,
                        component: component,
                        tools: root.tools
                    });

                    componentView.on('save', function() {
                        root.show();
                        root.data['components'][id] = this.component;
                    });

                    componentView.on('close', function() {
                        root.show();
                    });
                });
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
                element.remove();

                delete this.data.components[id];
            }
        },

        fixIndentedList: function(item, previousItem, container, content) {
            if (typeof item.parentNode === 'undefined' || item.parentNode === null) {
                if (!previousItem.length) {
                    return false;
                }
                item = previousItem[0];
            }

            if (!$.contains(container, item)) {
                var ul = $(item).parents('ul:first');
                ul.prev('li').html(content);
                ul.remove();
            }

            return false;
        }
    });
});
