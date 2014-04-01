define([
    'backbone',
    'marionette',
    'views/map/map-item'
],
    function (Backbone, Marionette, MapItemView) {
        return Marionette.CollectionView.extend({
            tagName: 'ul',
            className: 'map mapSortable',
            itemView: MapItemView,
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