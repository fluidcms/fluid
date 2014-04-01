define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-item',
    'text!templates/variable/variable-collection.ejs'
],
    function (Backbone, Marionette, EJS, VariableItemView, Template) {
        return Marionette.CollectionView.extend({
            itemView: VariableItemView,
            hasVariables: false,
            hasGroups: false,
            template: new EJS({text: Template}),

            initialize: function() {
                var root = this;
            },

            render: function() {
                this.$el.html(this.template.render({
                    hasVariables: this.collection.hasVariables,
                    hasGroups: this.collection.hasGroups,
                    groups: this.collection.getGroups()
                }));
                return this;
            }
        });
    }
);