define(['backbone', 'ejs', 'jquery-ui', 'views/helpers/contextmenu', 'models/variables/variables', 'views/editor/editor'], function (Backbone, EJS, jUI, ContextMenu, Variables, Editor) {
    return Backbone.View.extend({
        events: {
            "click a[data-action='close']": "close",
            "click a[data-item]": "edit"
        },

        current: null,

        editor: null,

        className: 'variables',

        changed: false,

        template: new EJS({url: 'javascripts/fluid/templates/components/component.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.app = attrs.app;
            this.components = attrs.components;
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

            this.data = this.component.data;
            this.html = this.variables.toHTML();

            this.render();
        },

        render: function () {
            var variables = new EJS({url: 'javascripts/fluid/templates/variables/variables.ejs?' + (new Date()).getTime()});  // !! Remove for production

            this.$el.html(this.template.render({
                definition: this.definition.get('variables'),
                data: this.data,
                render: this.html,
                variables: variables
            }));

            $("#target").append(this.$el);
            return this;
        },

        save: function() {
            this.trigger('save');
        },

        close: function() {
            if (this.changed) {
                this.save();
            }
            delete this.app.editors[this.cid];
            this.remove();
        },

        edit: function(e) {
            var root = this;
            var target = $(e.currentTarget);
            var item = target.attr('data-item');

            var html = "";
            if (typeof this.html[item] !== 'undefined') {
                html = this.html[item];
            }

            var data = null;
            if (typeof this.data[item] !== 'undefined') {
                data = this.data[item];
            }

            var type;
            if (target.find('div.data').hasClass("string")) {
                type = 'string';
            } else if (target.find('div.data').hasClass("content")) {
                type = "content";
            }

            this.editor = new Editor({
                type: type,
                html: html,
                data: data,
                app: this.app,
                components: this.components
            });

            this.editor.on('save', function() {
                root.data[item] = this.data;
                root.component.data = root.data;
                root.variables.updateData(root.data);
                root.html = root.variables.toHTML();
                root.changed = true;
                root.render();
            });

            this.trigger('editing');
        }
    });
});
