define([
    'backbone',
    'marionette',
    'ejs',
    'views/map/map-collection',
    'views/map/map-item',
    'text!templates/map/map-item.ejs'
],
    function (Backbone, Marionette, EJS, MapCollectionView, MapItemView, Template) {
        return Marionette.CompositeView.extend({
            events: {
                "click a": "editPage"
            },

            itemView: MapItemView,

            itemViewContainer: '[data-id="pages"]',

            tagName: 'li',

            attributes: function() {
                return {
                    'data-name': this.model.get('name')
                };
            },

            template: new EJS({text: Template}),

            initialize: function(options) {
                this.controller = options.controller;
                this.collection = this.model.pages;
            },

            editPage: function() {
                this.controller.pageEditor(this.model);
            }
        });
    }
);