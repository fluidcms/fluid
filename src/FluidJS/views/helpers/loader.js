define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        className: "loader",

        template: new EJS({url: 'javascripts/fluid/templates/helpers/loader.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.render();
        },

        render: function () {
            var loader = $("div.loader");

            if (loader.length) {
                this.$el = loader;
            } else {
                this.$el.html(this.template.render());
                $(document.body).append(this.$el);
            }
            return this;
        }
    });
});