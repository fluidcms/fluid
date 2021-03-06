define(['jquery-ui', 'views/editor/editor', 'views/helpers/contextmenu', 'views/variables/table'], function (jUI, Editor, ContextMenu, Table) {
    return $.extend({}, Table, {
        events: $.extend({}, Table.events, {
            "click a[data-action='close']": "close",
            "click [data-item]": "edit",
            "click [data-array-item]": "edit",
            "click nav a": "changeGroup",
            "click [data-action=addArrayItem]": "addArrayItem",
            'contextmenu div.array-item': 'arrayContextMenu',
            'change div.option select': 'changeOptionItem',
            'change div.bool input': 'changeBoolItem'
        }),

        rendered: false,

        current: null,

        editor: null,

        className: 'variables',

        initVariables: function() {
            this.initTables();
        },

        hide: function() {
            this.trigger('hide');
            this.$el.hide();
        },

        show: function() {
            this.trigger('show');
            this.$el.show();
        },

        close: function() {
            delete this.app.editors[this.cid];
            this.trigger('close');
            this.remove();
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

        droppable: function() {
            var root = this;

            // Images
            this.$el.find("[data-item] div.data.image img, [data-array-item] div.data.image img").droppable({
                hoverClass: "active",
                drop: function( event, ui ) {
                    var source = ui.draggable[0];
                    var target = $(event.target);

                    if (source.tagName === 'IMG') {
                        var id = $(source).parents('li').attr('data-id');
                        var file = root.files.get(id);

                        if (typeof file !== 'undefined') {
                            if (target.parents('[data-group]').length) {
                                var group = target.parents('[data-group]').attr('data-group');
                            }
                            var item;
                            if (target.parents('div[data-item]').length) {
                                item = target.parents('div[data-item]').attr('data-item');
                            } else if (target.parents('div[data-array]').length) {
                                item = target.parents('div[data-array]').attr('data-array');
                            }

                            var array = false;
                            if (target.parents('[data-array-item]').attr("data-array-item")) {
                                var key = target.parents("div.array-item").index() - 1;
                                array = target.parents('[data-array-item]').attr("data-array-item");
                            }

                            var data;
                            if (array) {
                                if (typeof group !== 'undefined') {
                                    data = root.data[group][item];
                                } else {
                                    data = root.data[item];
                                }
                                data[key][array] = file.id;
                            } else {
                                data = file.id;
                            }

                            target.css('opacity',.5);

                            if (typeof group !== 'undefined') {
                                root.save(data, item, group);
                            } else {
                                root.save(data, item);
                            }
                        }
                    }
                }
            });
        },

        arrayContextMenu: function(e) {
            e.preventDefault();
            new ContextMenu({url: ' javascripts/templates/variables/arraycm.ejs', parent: this, event: e}).render();
        },

        deleteArray: function(e) {
            var target = $(e);
            if (target.parents('div[data-group]').length) {
                var group = target.parents('div[data-group]').attr('data-group');
            }
            var item;
            if (target.parents('div[data-item]').length) {
                item = target.parents('div[data-item]').attr('data-item');
            } else if (target.parents('div[data-array]').length) {
                item = target.parents('div[data-array]').attr('data-array');
            }

            var index;
            if (target.hasClass('array-item')) {
                index = target.index() - 1;
            } else {
                index = target.parents(".array-item").index() - 1;
            }

            if (typeof group !== 'undefined') {
                this.data[group][item].splice(index, 1);
                this.save(this.data[group][item], item, group);
            } else {
                this.data[item].splice(index, 1);
                this.save(this.data[item], item);
            }
        },

        sortableArray: function() {
            var root = this;
            var startIndex;
            this.$el.find('div[data-array]').sortable({
                axis: "y",
                cancel: ".label,[data-array-item]",
                distance: 15,
                placeholder: 'ui-state-highlight',
                update: function (event, ui) {
                    var stopIndex = ui.item.index() - 1;

                    var target = ui.item;
                    if (target.parents('div[data-group]').length) {
                        var group = target.parents('div[data-group]').attr('data-group');
                    }
                    var item;
                    if (target.parents('div[data-item]').length) {
                        item = target.parents('div[data-item]').attr('data-item');
                    } else if (target.parents('div[data-array]').length) {
                        item = target.parents('div[data-array]').attr('data-array');
                    }

                    var array;
                    if (typeof group !== 'undefined') {
                        array = root.data[group][item][startIndex];
                        root.data[group][item].splice(startIndex, 1);
                        root.data[group][item].splice(stopIndex, 0, array);

                        root.save(root.data[group][item], item, group);
                    } else {
                        array = root.data[item][startIndex];
                        root.data[item].splice(startIndex, 1);
                        root.data[item].splice(stopIndex, 0, array);

                        root.save(root.data[item], item);
                    }
                },
                start: function(event, ui) {
                    $(event.currentTarget).find(".ui-state-highlight").height($(ui.item).height() - (2*19));
                    startIndex = ui.item.index() - 1;
                }
            });
            this.$el.find('div[data-array]').disableSelection();
        },

        addArrayItem: function(e) {
            var target = $(e.currentTarget);
            if (target.parents('div[data-group]').length) {
                var group = target.parents('div[data-group]').attr('data-group');
            }
            var item;
            if (target.parents('div[data-item]').length) {
                item = target.parents('div[data-item]').attr('data-item');
            } else if (target.parents('div[data-array]').length) {
                item = target.parents('div[data-array]').attr('data-array');
            }

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

        getItem: function(e) {
            var target = $(e.currentTarget);
            var group;
            if (target.parents('div[data-group]').length) {
                group = target.parents('div[data-group]').attr('data-group');
            }
            var item;
            if (target.parents('div[data-item]').length) {
                item = target.parents('div[data-item]').attr('data-item');
            } else if (target.parents('div[data-array]').length) {
                item = target.parents('div[data-array]').attr('data-array');
            }

            var array = false;
            var key;
            if (target.parents('div[data-array-item]')) {
                key = target.parents("div.array-item").index() - 1;
                array = target.parents('div[data-array-item]').attr("data-array-item");
            }

            return {
                target: target,
                group: group,
                item: item,
                array: array,
                key: key
            };
        },

        changeBoolItem: function(e) {
            e = this.getItem(e);
            var value = e.target.is(':checked');

            var dataArray;
            if (typeof e.group !== 'undefined' && e.group !== null) {
                if (e.array) {
                    dataArray = this.data[e.group][e.item];
                    dataArray[e.key][e.array] = value;
                    value = dataArray;
                }

                this.save(value, e.item, e.group);
            } else {
                if (e.array) {
                    dataArray = this.data[e.item];
                    dataArray[e.key][e.array] = value;
                    value = dataArray;
                }

                this.save(value, e.item);
            }
        },

        changeOptionItem: function(e) {
            var target = $(e.currentTarget);
            var value = target.val();
            if (target.parents('div[data-group]').length) {
                var group = target.parents('div[data-group]').attr('data-group');
            }
            var item;
            if (target.parents('div[data-item]').length) {
                item = target.parents('div[data-item]').attr('data-item');
            } else if (target.parents('div[data-array]').length) {
                item = target.parents('div[data-array]').attr('data-array');
            }

            var array = false;
            if (target.parents('div[data-array-item]')) {
                var key = target.parents("div.array-item").index() - 1;
                array = target.parents('div[data-array-item]').attr("data-array-item");
            }

            var dataArray;
            if (typeof group !== 'undefined') {
                if (array) {
                    dataArray = this.data[group][item];
                    dataArray[key][array] = value;
                    value = dataArray;
                }

                this.save(value, item, group);
            } else {
                if (array) {
                    dataArray = this.data[item];
                    dataArray[key][array] = value;
                    value = dataArray;
                }

                this.save(value, item);
            }
        },

        edit: function(e) {
            var target = $(e.currentTarget);
            if (target.parents('div[data-group]').length) {
                var group = target.parents('div[data-group]').attr('data-group');
            }
            var item = target.attr('data-item');
            if (typeof item === 'undefined') {
                if (target.parents('[data-item]').length) {
                    item = target.parents('[data-item]').attr('data-item');
                } else if (target.parents('[data-array]').length) {
                    item = target.parents('[data-array]').attr('data-array');
                }
            }

            var array = false;
            if (target.attr("data-array-item")) {
                var key = target.parents("div.array-item").index() - 1;
                array = target.attr("data-array-item");
            }

            var html = "";
            if (typeof this.html[item] !== 'undefined' || (typeof group !== 'undefined' && typeof this.html[group] !== 'undefined' && typeof this.html[group][item] !== 'undefined')) {
                if (typeof group !== 'undefined') {
                    html = this.html[group][item];
                } else {
                    html = this.html[item];
                }
            }

            if (array) {
                html = html[key][array];
            }

            var data = null;
            if (typeof this.data[item] !== 'undefined' || (typeof group !== 'undefined' && typeof this.data[group] !== 'undefined' && typeof this.data[group][item] !== 'undefined')) {
                if (typeof group !== 'undefined') {
                    data = this.data[group][item];
                } else {
                    data = this.data[item];
                }
            }

            if (array) {
                data = data[key][array];
            }

            var type;
            if (target.find('div.data').hasClass("string")) {
                type = 'string';
            } else if (target.find('div.data').hasClass("content")) {
                type = "content";
            }

            if (typeof type === 'undefined' || type === null) {
                return;
            }

            this.editData(data, html, type, array, key, item, group);
        },

        editContent: function(data, html, item, group) {
            this.editData(data, html, 'content', null, null, item, group);
        },

        editData: function(data, html, type, array, key, item, group) {
            var root = this;

            this.hide();

            this.editor = new Editor({
                type: type,
                html: html,
                data: data,
                app: this.app,
                components: this.components,
                files: this.files,
                tools: this.tools
            });

            if (type === 'content') {
                this.editor.on('close', this.toggleAppNav, this);
            }

            this.editor.on('close', function() {
                root.show();
            });

            this.editor.on('save', function() {
                root.show();
                var dataArray;
                if (typeof group !== 'undefined') {
                    if (array) {
                        dataArray = root.data[group][item];
                        dataArray[key][array] = this.data;
                        this.data = dataArray;
                    }

                    root.save(this.data, item, group);
                } else {
                    if (array) {
                        dataArray = root.data[item];
                        dataArray[key][array] = this.data;
                        this.data = dataArray;
                    }

                    root.save(this.data, item);
                }
            });

            this.trigger('editing');
            this.trigger('editing:'+type);

        }
    });
});
