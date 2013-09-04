define(['backbone', 'ejs', 'jquery-ui', 'views/editor/helper', 'views/helpers/contextmenu'], function (Backbone, EJS, jUI, EditorHelper, ContextMenu) {
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

        data: null,
        html: null,

        initialize: function (attrs) {
            var root = this;

            this.type = attrs.type;
            this.html = attrs.html;
            this.data = attrs.data;
            this.components = attrs.components;
            this.app = attrs.app;

            this.app.editors[this.cid] = this;

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
            this.$el.html(this.template.render({
                type: this.type,
                html: this.html
            }));
            $("#target").append(this.$el);

            EditorHelper(this.$el.find('div[contenteditable]'), this.type); // TODO: integrate into this view

            this.droppable();

            this.$el.find("div[contenteditable]").focus();

            return this;
        },

        componentContextMenu: function (e) {
            e.preventDefault();
            new ContextMenu({url: 'javascripts/fluid/templates/editor/componentcm.ejs', parent: this, event: e}).render();
        },

        save: function() {
            var content = this.$el.find('div[contenteditable]').html();

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
                    var componentView = new ComponentView({
                        app: root.app,
                        components: root.components,
                        definition: definition,
                        component: component
                    });

                    componentView.on('save', function() {
                        root.data['components'][id] = this.component;
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
        }
    });
});
