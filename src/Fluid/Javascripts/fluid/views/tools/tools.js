define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        events: {
        },

        className: 'tools',

        dropbox: {},

        template: new EJS({url: 'javascripts/fluid/templates/tools/tools.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.map = attrs.map;
            this.render();
            //this.collection.on('reset', this.render, this);
        },

        render: function () {
            this.$el.html(this.template.render({

            }));
            $("#main #content").append(this.$el);
            return this;
        },

        hide: function() {
            this.$el.hide();
        },

        show: function() {
            this.$el.show();
        }
    });
});