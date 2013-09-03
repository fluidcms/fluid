define(['backbone', 'ejs', 'jquery-ui', 'views/helpers/contextmenu', 'views/editor/editor'], function (Backbone, EJS, jUI, ContextMenu, Editor) {
    return Backbone.View.extend({
        events: {
            "click a[data-item]": "edit",
            "click nav a": "changeGroup"
        },

        current: null,

        previousAppNav: null,

        editor: null,

        className: 'page-editor',

        template: new EJS({url: 'javascripts/fluid/templates/page/page.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;

            this.app = attrs.app;

            this.app.editors[this.cid] = this;

            this.languages = attrs.languages;
            this.components = attrs.components;
            this.files = attrs.files;

            this.model.on('change', this.render, this);
            this.app.on('change', function() { root.previousAppNav = null; });
        },

        render: function () {
            var variables = new EJS({url: 'javascripts/fluid/templates/variables/variables.ejs?' + (new Date()).getTime()});  // !! Remove for production

            this.$el.html(this.template.render({
                definition: this.model.get('layoutDefinition'),
                data: this.model.get('data'),
                render: this.model.get('render'),
                variables: variables
            }));

            this.droppable();

            $("#target").append(this.$el);

            this.changeGroup(this.current);
            return this;
        },

        close: function() {
            delete this.app.editors[this.cid];
            this.remove();
        },

        droppable: function() {
            var root = this;

            // Images
            this.$el.find("a[data-item] div.data.image img").droppable({
                hoverClass: "active",
                drop: function( event, ui ) {
                    var source = ui.draggable[0];
                    var target = event.target;

                    if (source.tagName === 'IMG') {
                        var id = $(source).parents('li').attr('data-id');
                        var file = root.files.get(id);

                        if (typeof file !== 'undefined') {
                            var group = $(target).parents('[data-group]').attr('data-group');
                            var item = $(target).parents('[data-item]').attr('data-item');
                            $(target).css('opacity',.5);
                            root.model.saveData(group, item, file.id);
                        }
                    }
                }
            });
        },

        changeGroup: function(e) {
            this.$el.find('nav li').removeClass('current');
            this.$el.find('div.main>div').css("display", "none");

            if (typeof e === 'undefined' || e === null) {
                this.$el.find('nav li:first').addClass('current');
                this.$el.find('div.main div:first').css("display", "block");
            } else {
                if (typeof e === 'object') {
                    this.current = $(e.currentTarget).attr("data-group");
                } else {
                    this.current = e;
                }

                this.$el.find('nav li a[data-group="'+this.current+'"]').parents('li').addClass('current');
                this.$el.find('div.main>div[data-group="'+this.current+'"]').css("display", "block");
            }
        },

        edit: function(e) {
            var root = this;
            var target = $(e.currentTarget);
            var group = target.parents('div[data-group]').attr('data-group');
            var item = target.attr('data-item');
            var content = target.find('div.data');

            var html = this.model.get('render');
            if (typeof html[group] !== 'undefined' && typeof html[group][item] !== 'undefined') {
                html = html[group][item];
            } else {
                html = "";
            }

            var data = this.model.get('data');
            if (typeof data[group] !== 'undefined' && typeof data[group][item] !== 'undefined') {
                data = data[group][item];
            } else {
                data = null;
            }

            var type;
            if (target.find('div.data').hasClass("string")) {
                type = 'string';
            } else if (target.find('div.data').hasClass("content")) {
                type = "content";
                var previousAppNav = this.app.current;
                if (previousAppNav !== 'components' && previousAppNav !== 'files') {
                    this.app.make('tools');
                    this.previousAppNav = previousAppNav;
                }
            }

            this.editor = new Editor({
                type: type,
                html: html,
                data: data,
                app: this.app,
                components: this.components
            });

            if (type === 'content') {
                this.editor.on('close', this.toggleAppNav, this);
            }

            this.editor.on('save', function() {
                root.model.saveData(group, item, this.data);
            });

            this.trigger('editing');
        },

        toggleAppNav: function() {
            this.trigger('stopEditing');
            if (typeof this.previousAppNav !== 'undefined' && this.previousAppNav !== null) {
                this.app.make(this.previousAppNav);
            }
        }
    });
});
