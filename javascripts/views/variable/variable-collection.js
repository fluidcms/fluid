define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-item'
],
    function (Backbone, Marionette, EJS, VariableItemView) {
        return Marionette.CollectionView.extend({
            itemView: VariableItemView,

            itemViewOptions: function() {
                return {
                    controller: this.controller
                };
            },

            initialize: function(options) {
                this.controller = options.controller;
            }
        });
    }
);