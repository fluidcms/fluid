define([
    'backbone',
    'ejs',
    'views/map/map-collection',
    'views/map/map-item',
    'text!templates/map/map-item.ejs'
],
    function (Backbone, EJS, MapCollectionView, MapItemView, Template) {
        return Backbone.Marionette.CompositeView.extend({
            initialize: function() {
                this.collection = this.model.pages;
            },

            itemView: MapItemView,

            itemViewContainer: '[data-id="pages"]',

            tagName: 'li',

            attributes: function() {
                return {
                    'data-name': this.model.get('name')
                };
            },

            template: new EJS({text: Template})
        });
    }
);