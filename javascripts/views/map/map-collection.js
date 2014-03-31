define([
    'backbone',
    'views/map/map-item'
],
    function (Backbone, MapItemView) {
        return Backbone.Marionette.CollectionView.extend({
            tagName: 'ul',
            className: 'map mapSortable',
            itemView: MapItemView
        });
    }
);