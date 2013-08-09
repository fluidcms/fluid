define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        className: 'tools',

        dropbox: {},

        rendered: false,

        textEnabled: false,

        contentEditor: null,

        template: new EJS({url: 'javascripts/fluid/templates/tools/tools.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.map = attrs.map;
            this.map.on('editing', this.trackEditing, this);
        },

        render: function () {
            this.$el.html(this.template.render({
                textEnabled: this.textEnabled
            }));
            $("#main #content").append(this.$el);
            this.rendered = true;
            return this;
        },

        hide: function() {
            this.$el.hide();
        },

        show: function() {
            if (!this.rendered) {
                this.render();
            }
            this.$el.show();
        },

        trackEditing: function() {
            this.map.editor.view.on('editing', this.enable, this);
            this.map.editor.view.on('stopEditing', this.disable, this);
        },

        enable: function() {
            var root = this;

            if (this.map.editor.view.contentEditor.type === 'content') {
                this.textEnabled = true;
                this.contentEditor = this.map.editor.view.contentEditor.$el.find('[contenteditable]');
                $(document).on('keypress keyup click mouseup', null, {root: this}, this.analyzeText);

                $(this.$el).find('div.text a[data-role]').on('mousedown', this.formatText).on('mousedown', function() { root.analyzeText({data: {root: root}}); });

                this.analyzeText({data: {root: this}});
            }

            if (this.rendered) {
                if (this.textEnabled) {
                    this.$el.find('div.text select').removeAttr('disabled');
                    this.$el.find('div.text a').removeAttr('data-disabled');
                }
            }
        },

        disable: function() {
            var root = this;
            this.textEnabled = false;

            if (this.rendered) {
                this.$el.find('div.text select').attr('disabled', 'true');
                this.$el.find('div.text a').attr('data-disabled', "true");
            }

            $(this.$el).find('div.text a[data-role]').off('mousedown');
            $(document).off('keypress keyup click mouseup', this.analyzeText);
        },

        analyzeText: function(e) {
            var root = e.data.root;

            var fontStyles = ['bold', 'italic', 'underline', 'strikeThrough'];

            if (root.contentEditor.is(':focus')) {
                $.each(fontStyles, function(key, value) {
                    if (document.queryCommandValue(value) === 'true') {
                        root.$el.find('[data-role="'+value+'"]').addClass('active');
                    } else {
                        root.$el.find('[data-role="'+value+'"]').removeClass('active');
                    }
                });
            }
        },

        formatText: function(e) {
            e.preventDefault();

            var role = $(e.currentTarget).attr('data-role');

            switch(role) {
                case 'bold':
                case 'italic':
                case 'underline':
                case 'strikeThrough':
                    document.execCommand(role, false, null);
                    break;
            }

            return false;
        }
    });
});