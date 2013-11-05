define([
    'jquery-ui',
    'views/helpers/contextmenu',
    'views/editor/editor2',
    'models/variables/variables',
], function (
    jUI,
    ContextMenu,
    Editor,
    Variables
    ) {
    return {
        events: {
            'contextmenu div.table td': 'tableContextMenu',
            'input div.table td': 'tableSaveData'
        },

        initTables: function() {
            var root = this;
            var tables = $('[data-type=table]');
            if (tables.length) {
                $.each(tables, function(key, item) {
                    root.initTableEditors($(item));
                });
            }
        },

        initTableEditors: function(table) {
            var root = this;
            $.each(table.find('tbody td'), function(key, item) {
                new Editor({
                    el: item,
                    app: root.app,
                    components: root.components,
                    files: root.files,
                    tools: root.tools
                });
            });
        },

        tableContextMenu: function(e) {
            e.preventDefault();
            var body = false;
            if ($(e.currentTarget).parents('tbody').length) {
                body = true;
            }
            new ContextMenu({url: 'javascripts/fluid/templates/variables/tablecm.ejs', parent: this, event: e}).render({body: body});
        },

        tableInsertRowBefore: function(target) {
            var tr = $(target).parents('tr:first').clone();
            tr.find('td').html('');
            $(target).parents('tr:first').before(tr);
            this.tableSaveData(null, target);
        },

        tableInsertRowAfter: function(target) {
            var tr = $(target).parents('tr:first').clone();
            tr.find('td').html('');
            $(target).parents('tr:first').after(tr);
            this.tableSaveData(null, target);
        },

        tableInsertColumnBefore: function(target) {
            var index = $(target).index() + 1;
            var td = $(target).clone();
            td.html('');
            var table = $(target).parents("table:first");

            $.each(table.find('tr'), function(key, row) {
                $(row).find('td:nth-child('+index+')').before(td.clone());
            });
            this.tableSaveData(null, target);
        },

        tableInsertColumnAfter: function(target) {
            var index = $(target).index() + 1;
            var td = $(target).clone();
            td.html('');
            var table = $(target).parents("table:first");

            $.each(table.find('tr'), function(key, row) {
                $(row).find('td:nth-child('+index+')').after(td.clone());
            });
            this.tableSaveData(null, target);
        },

        tableDeleteRow: function(target) {
            var tr = $(target).parents('tr:first').remove();
            this.tableSaveData(null, target);
        },

        tableDeleteColumn: function(target) {
            var index = $(target).index() + 1;
            var table = $(target).parents("table:first");

            $.each(table.find('tr'), function(key, row) {
                $(row).find('td:nth-child('+index+')').remove();
            });

            this.tableSaveData(null, target);
        },

        tableSaveData: function(e, target) {
            if (typeof e !== 'undefined' && e !== null) {
                target = e.currentTarget;
            }

            e = this.getItem({currentTarget: target});
            var value = $(target).parents('table:first').html();

            var dataArray;
            if (typeof e.group !== 'undefined' && e.group !== null) {
                if (e.array) {
                    dataArray = this.data[e.group][e.item];
                    dataArray[e.key][e.array] = value;
                    value = dataArray;
                }

                this.save(value, e.item, e.group, {silent: true});
            } else {
                if (e.array) {
                    dataArray = this.data[e.item];
                    dataArray[e.key][e.array] = value;
                    value = dataArray;
                }

                this.save(value, e.item, {silent: true});
            }
        }
    };
});
