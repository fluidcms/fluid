define(['backbone', 'ejs', 'jquery-ui', 'editor/editor'], function (Backbone, EJS, jUI, Editor) {
    return Backbone.View.extend({
        events: {
            "click [data-action=cancel]": "close",
            "click [data-action=save]": "save"
        },

        type: null,

        className: 'editor',

        template: new EJS({url: 'javascripts/fluid/templates/pageeditor/content.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;

            this.type = attrs.type;
            this.model = attrs.model;
            this.group = attrs.group;
            this.item = attrs.item;

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
            this.$el.find('div[contenteditable]').sortable({
                cancel: "p,h1,h2,h3,h4,h5,h6,ul,li,a:not(.component)",
                activeClass: "",
                receive: function( event, ui ) {
                    var item = $(this).find('>a.component');
                    item.attr('contenteditable', 'false');
                    item.wrap('<div id="'+randomString(8)+'"></div>');
                }
            });
        }
    });
});
