define([
    'backbone',
    'marionette',
    'jquery-ui',
    'views/component/component-item'
],
    function (Backbone, Marionette, jUI, ComponentItemView) {
        return Marionette.CollectionView.extend({
            tagName: 'ul',

            className: 'components',

            itemView: ComponentItemView,

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