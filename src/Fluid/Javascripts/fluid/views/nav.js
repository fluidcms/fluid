define(['backbone', 'ejs', 'qtip'], function (Backbone, EJS, qTip) {
    return Backbone.View.extend({
        id: "nav",

        template: new EJS({url: 'javascripts/fluid/templates/nav.ejs'}),

        initialize: function (attrs) {
            this.items = [
                {name: 'Map', className: 'map'},
                {name: 'Components', className: 'components'},
                {name: 'Files', className: 'files'},
                {name: 'Tools', className: 'tools'},
                {name: 'History', className: 'history'}
            ];

            this.router = attrs.router;
            this.router.on("change", this.changeRoute, this);
        },

        render: function () {
            this.$el.html(this.template.render({items: this.items, current: this.router.current}));
            $("#main #nav").remove();
            $('#main').prepend(this.$el);

            $("#main #nav a").qtip({
                style: {
                    tip: false,
                },
                position: {
                    my: 'top center',
                    at: 'bottom center',
                    adjust: {
                        y: 10
                    }
                }
            });

            return this;
        },

        changeRoute: function () {
            $("#main #nav a").removeClass('current');
            $("#main #nav a." + this.router.current).addClass('current');
        }
    });
});