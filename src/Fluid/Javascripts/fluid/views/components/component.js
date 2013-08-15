define(['backbone', 'ejs', 'jquery-ui', 'views/contextmenu', 'views/editor/editor'], function (Backbone, EJS, jUI, ContextMenu, Editor) {
    return Backbone.View.extend({
        events: {
            "click a[data-action='close']": "close",
            "click a[data-item]": "edit"
        },

        current: null,

        previousAppNav: null,

        contentEditor: null,

        className: 'variables',

        template: new EJS({url: 'javascripts/fluid/templates/components/component.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            /*var root = this;
            this.app = attrs.app;
            this.languages = attrs.languages;
            this.components = attrs.components;
            this.model.on('change', this.render, this);
            this.app.on('change', function() { root.previousAppNav = null; });*/

            this.definition = attrs.definition;
            this.data = attrs.data;
            this.render();
        },

        render: function () {
            this.$el.html(this.template.render({
                definition: this.definition.get('variables'),
                data: this.data
            }));

            $("#target").append(this.$el);
            return this;
        },

        close: function() {
            this.remove();
        },

        edit: function(e) {
            var target = $(e.currentTarget);
            var group = target.attr('data-group');
            var item = target.attr('data-item');
            var data = target.find('div.data');

            if (data.hasClass("string")) {
                this.contentEditor = new Editor({type: 'string', group: group, item: item, model: this.model, components: this.components});
            } else if (data.hasClass("content")) {
                var previousAppNav = this.app.current;
                if (previousAppNav !== 'components' && previousAppNav !== 'files') {
                    this.app.make('tools');
                    this.previousAppNav = previousAppNav;
                }
                this.contentEditor = new Editor({type: 'content', group: group, item: item, model: this.model, components: this.components});
                this.contentEditor.on('close', this.toggleAppNav, this);
            } else {
                this.contentEditor = null;
            }

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
