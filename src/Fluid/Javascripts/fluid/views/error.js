define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        className: "error",

        template: new EJS({url: 'javascripts/fluid/templates/error.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.msg = attrs.msg;
            this.render();
        },

        render: function () {
            var error = $("div.error");

            if (error.length) {
                this.$el = error;
            } else {
                this.$el.html(this.template.render({error: this.msg}));
                $(document.body).append(this.$el);
            }
            return this;
        }
    });
});