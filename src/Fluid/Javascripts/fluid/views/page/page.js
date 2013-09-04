define(['backbone', 'ejs', 'jquery-ui', 'views/helpers/contextmenu', 'views/editor/editor', 'views/variables/variables'], function (Backbone, EJS, jUI, ContextMenu, Editor, VariablesView) {
    return Backbone.View.extend($.extend({}, VariablesView, {
        previousAppNav: null,

        template: new EJS({url: 'javascripts/fluid/templates/page/page.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;

            this.app = attrs.app;

            this.app.editors[this.cid] = this;

            this.languages = attrs.languages;
            this.components = attrs.components;
            this.files = attrs.files;

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
            var variables = new EJS({url: 'javascripts/fluid/templates/variables/variables.ejs?' + (new Date()).getTime()});  // !! Remove for production

            this.$el.html(this.template.render({
                definition: this.definition,
                data: this.data,
                render: this.html,
                variables: variables
            }));

            this.droppable();
            this.sortableArray();

            $("#target").append(this.$el);

            this.changeGroup(this.current);
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
        }
    }));
});
