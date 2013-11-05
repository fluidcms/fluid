define(
    ['backbone', 'ejs', 'jquery-ui', 'views/helpers/contextmenu', 'views/editor/editor', 'views/variables/variables'],
    function (Backbone, EJS, jUI, ContextMenu, Editor, VariablesView) {
        return Backbone.View.extend($.extend({}, VariablesView, {
            events: $.extend({}, VariablesView.events, {
                "contextmenu [data-type=content]": "contentCM"
            }),

            previousAppNav: null,

            template: new EJS({url: 'javascripts/fluid/templates/page/page.ejs?' + (new Date()).getTime()}),  // !! Remove for production

            initialize: function (attrs) {
                var root = this;

                this.app = attrs.app;

                this.app.editors[this.cid] = this;

                this.languages = attrs.languages;
                this.components = attrs.components;
                this.files = attrs.files;
                this.tools = attrs.tools;

                this.definition = this.model.get('layoutDefinition');
                this.data = this.model.get('data');
                this.html = this.model.get('render');

                this.on('editing:content', function() {
                    var previousAppNav = root.app.current;
                    if (previousAppNav !== 'components' && previousAppNav !== 'files') {
                        root.app.make('tools');
                        root.previousAppNav = previousAppNav;
                    }
                });

                this.model.on('change', function() {
                    root.definition = root.model.get('layoutDefinition');
                    root.data = root.model.get('data');
                    root.html = root.model.get('render');
                    root.render();
                });
                this.app.on('change', function() { root.previousAppNav = null; });
            },

            render: function () {
                var root = this;
                var variables = new EJS({url: 'javascripts/fluid/templates/variables/variables.ejs?' + (new Date()).getTime()});  // !! Remove for production

                if (this.rendered === true) {
                    var scroll = this.$el.find("div.main").scrollTop();
                }

                // TODO: live tracking of the scroll

                this.$el.html(this.template.render({
                    definition: this.definition,
                    data: this.data,
                    render: this.html,
                    variables: variables
                }));

                this.rendered = true;

                this.droppable();
                this.sortableArray();

                $("#target").append(this.$el);

                this.changeGroup(this.current);

                if (typeof scroll !== 'undefined') {
                    this.$el.find("div.main").scrollTop(scroll);
                    setTimeout(function() {
                        root.$el.find("div.main").scrollTop(scroll);
                    }, 10);
                }

                this.initVariables();

                return this;
            },

            save: function(data, item, group) {
                this.model.saveData(group, item, data);
            },

            toggleAppNav: function() {
                this.trigger('stopEditing');
                if (typeof this.previousAppNav !== 'undefined' && this.previousAppNav !== null) {
                    this.app.make(this.previousAppNav);
                }
            },

            contentCM: function(e) {
                e.preventDefault();
                new ContextMenu({url: 'javascripts/fluid/templates/variables/contentcm.ejs', parent: this, event: e}).render({languages: this.languages});
            },

            copyLang: function(target, e) {
                var root = this;
                var lang = $(e.target).attr('data-lang');
                var group;
                var item;

                if ($(target).parents('[data-group]').length) {
                    group = $(target).parents('[data-group]').attr('data-group');
                } else if ($(target).attr('data-group')) {
                    group = $(target).attr('data-group');
                } else if ($(target).children('[data-group]').length) {
                    group = $(target).children('[data-group]').attr('data-group');
                }

                if ($(target).parents('[data-item]').length) {
                    item = $(target).parents('[data-item]').attr('data-item');
                } else if ($(target).attr('data-item')) {
                    item = $(target).attr('data-item');
                } else if ($(target).children('[data-item]').length) {
                    item = $(target).children('[data-item]').attr('data-item');
                }

                this.model.requestContent(group, item, lang, function(response, html) {
                    root.editContent(response, html, item, group);
                });
            }
        }));
    }
);
