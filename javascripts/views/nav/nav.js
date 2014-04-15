define(['backbone', 'ejs', 'qtip'], function (Backbone, EJS, qTip) {
    return Backbone.View.extend({
        events: {
            "click a": "navigate"
        },

        id: "nav",

        template: new EJS({url: ' javascripts/templates/nav/nav.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (options) {
            this.controller = options.controller;

            this.items = [
                {name: 'Map', className: 'map', pannel: 'mapPannel'},
                {name: 'Components', className: 'components', pannel: 'componentsPannel'},
                {name: 'Files', className: 'files', pannel: 'filesPannel'},
                {name: 'Tools', className: 'tools', pannel: 'toolsPanel'},
                {name: 'History', className: 'history', pannel: 'historyPannel'}
            ];

            this.$el = $("#nav");

            //this.router = attrs.router;
            //this.router.on("change", this.changeRoute, this);
        },

        render: function () {
            this.$el.html(this.template.render({
                items: this.items,
                current: 'map'
                //current: this.router.current
            }));

            this.$el.find("a").qtip({
                style: {
                    tip: false
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

        navigate: function (e) {
            var pannel = $(e.currentTarget).attr('data-pannel');
            this.controller[pannel]();
            this.changeRoute(pannel);
        },

        changeRoute: function (pannel) {
            var nav = $("#nav");
            nav.find("a").removeClass('current');
            nav.find("a[data-pannel=" + pannel + "]").addClass('current');
        }
    });
});