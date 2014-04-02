define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-item'
],
    function (Backbone, Marionette, EJS, VariableItemView) {
        return Marionette.CollectionView.extend({
            itemView: VariableItemView
        });
    }
);