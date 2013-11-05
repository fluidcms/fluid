define(['backbone', 'ejs'], function (Backbone, EJS) {
    var ContextMenu = Backbone.View.extend({
        tagName: 'div',

        className: "context-menu",

        events: {
            'click [data-action]': 'click'
        },

        initialize: function (config) {
            this.event = config.event;
            this.parent = config.parent;
            this.template = new EJS({
                url: config.url + '?' + (new Date()).getTime() // !! Remove for production
            });
        },

        render: function (data) {
            $(this.event.target).addClass('active');
            this.$el.html(this.template.render(data));
            this.$el.css({left: this.event.pageX, top: this.event.pageY});
            $(document.body).append(this.$el);

            var root = this;
            setTimeout(function () {
                $(document.body).on('click contextmenu', function () {
                    root.close();
                });
                $(document).keyup(function (e) {
                    if (e.keyCode == 27) root.remove();
                });

            }, 1);
            return this;
        },

        click: function (e) {
            this.close();
            var action = $(e.currentTarget).attr('data-action');
            this.parent[action](this.event.target, e);
        },

        close: function () {
            $(document.body).off('click contextmenu');
            $(this.event.target).removeClass('active');
            this.remove();
        }
    });

    return ContextMenu;
});