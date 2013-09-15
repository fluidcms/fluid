define(
    ['backbone', 'ejs', 'jquery-ui', 'views/helpers/contextmenu', 'models/variables/variables', 'views/editor/editor', 'views/variables/variables'],
    function (Backbone, EJS, jUI, ContextMenu, Variables, Editor, VariablesView) {
        return Backbone.View.extend($.extend({}, VariablesView, {
            changed: false,

            template: new EJS({url: 'javascripts/fluid/templates/components/component.ejs?' + (new Date()).getTime()}),  // !! Remove for production

            initialize: function (attrs) {
                this.app = attrs.app;
                this.components = attrs.components;
                this.files = attrs.files;

                this.definition = attrs.definition;
                this.component = attrs.component;

                this.app.editors[this.cid] = this;

                this.variables = new Variables(null, {
                    components: this.components,
                    data: this.component.data,
                    definition: this.definition.get('variables')
                });

                if ($.isArray(this.component.data) && this.component.data.length === 0) {
                    this.component.data = {};
                }

                this.definition = this.definition.get('variables');
                this.data = this.component.data;
                this.html = this.variables.toHTML();

                this.render();
            },

            render: function () {
                var root = this;
                var variables = new EJS({url: 'javascripts/fluid/templates/variables/variables.ejs?' + (new Date()).getTime()});  // !! Remove for production

                if (this.rendered === true) {
                    var scroll = this.$el.find("div.main").scrollTop();
                }

                this.$el.html(this.template.render({
                    component: this.components.findWhere({component: this.component.component}),
                    definition: this.definition,
                    data: this.data,
                    render: this.html,
                    variables: variables
                }));

                this.droppable();
                this.sortableArray();

                $("#target").append(this.$el);

                if (typeof scroll !== 'undefined') {
                    this.$el.find("div.main").scrollTop(scroll);
                    setTimeout(function() {
                        root.$el.find("div.main").scrollTop(scroll);
                    }, 10);
                }

                return this;
            },

            save: function(data, item) {
                this.data[item] = this.variables.toJSON(data, item);
                this.variables.updateData(this.data);

                this.component.data = this.data;
                this.html = this.variables.toHTML();
                this.changed = true;
                this.render();
            },

            close: function() {
                this.trigger('close');

                if (this.changed) {
                    this.trigger('save');
                }
                delete this.app.editors[this.cid];
                this.remove();
            }
        }));
    }
);
