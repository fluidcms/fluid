define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        events: {
            "click a[data-id]": "rollBack"
        },

        className: 'history',

        dropbox: {},

        template: new EJS({url: 'javascripts/fluid/templates/history/history.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.collection.on('reset', this.render, this);
        },

        render: function () {
            this.$el.html(this.template.render({steps: this.collection.models}));
            $("#main #content").append(this.$el);
            return this;
        },

        rollBack: function(e) {
            var id = $(e.target).attr('data-id');
            this.collection.rollBack(id);
        }
    });
});