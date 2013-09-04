define(['jquery-ui', 'views/editor/editor'], function (jUI, Editor) {
    return {
        events: {
            "click a[data-item]": "edit",
            "click nav a": "changeGroup",
            "click [data-action=addArrayItem]": "addArrayItem"
        },

        current: null,

        editor: null,

        className: 'variables',

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
                            if ($(target).parents('[data-group]').length) {
                                var group = $(target).parents('[data-group]').attr('data-group');
                            }
                            var item = $(target).parents('[data-item]').attr('data-item');
                            $(target).css('opacity',.5);

                            if (typeof group !== 'undefined') {
                                root.save(file.id, item, group);
                            } else {
                                root.save(file.id, item);
                            }
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

        addArrayItem: function(e) {
            var target = $(e.currentTarget);
            if (target.parents('div[data-group]').length) {
                var group = target.parents('div[data-group]').attr('data-group');
            }
            var item = target.parents('div[data-item]').attr('data-item');

            if (typeof group !== 'undefined') {
                if (typeof this.data[group] === 'undefined') {
                    this.data[group] = {};
                }
                if (typeof this.data[group][item] === 'undefined' || this.data[group][item] === null) {
                    this.data[group][item] = [];
                }
                this.data[group][item].push(null);
                this.save(this.data[group][item], item, group);
            } else {
                if (typeof this.data[item] === 'undefined' || this.data[item] === null) {
                    this.data[item] = [];
                }
                this.data[item].push(null);
                this.save(this.data[item], item);
            }
        },

        edit: function(e) {
            var root = this;
            var target = $(e.currentTarget);
            if (target.parents('div[data-group]').length) {
                var group = target.parents('div[data-group]').attr('data-group');
            }
            var item = target.attr('data-item');

            var html = "";
            if (typeof this.html[item] !== 'undefined' || (typeof group !== 'undefined' && typeof this.html[group] !== 'undefined' && typeof this.html[group][item] !== 'undefined')) {
                if (typeof group !== 'undefined') {
                    html = this.html[group][item];
                } else {
                    html = this.html[item];
                }
            }

            var data = null;
            if (typeof this.data[item] !== 'undefined' || (typeof group !== 'undefined' && typeof this.data[group] !== 'undefined' && typeof this.data[group][item] !== 'undefined')) {
                if (typeof group !== 'undefined') {
                    data = this.data[group][item];
                } else {
                    data = this.data[item];
                }
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

            if (type === 'content') {
                this.editor.on('close', this.toggleAppNav, this);
            }

            this.editor.on('save', function() {
                if (typeof group !== 'undefined') {
                    root.save(this.data, item, group);
                } else {
                    root.save(this.data, item);
                }
            });

            this.trigger('editing');
            this.trigger('editing:'+type);
        }
    };
});
