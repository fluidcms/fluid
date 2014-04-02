define([
    'backbone',
    'marionette',
    'ejs'
],
    function (Backbone, Marionette, EJS) {
        return Marionette.ItemView.extend({
            render: function() {
                this.$el.html('tooo');
                return this;
            }
        });
    }
);