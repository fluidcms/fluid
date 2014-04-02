define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-item'
],
    function (Backbone, Marionette, EJS, VariableItemView) {
        return Marionette.ItemView.extend({
            //itemView: VariableItemView
            render: function() {
                this.$el.html('tooo');
                return this;
            }
        });
    }
);