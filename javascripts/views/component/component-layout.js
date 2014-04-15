define([
    'backbone',
    'marionette',
    'ejs',
    //'views/map/map-collection',
    'text!templates/component/component-layout.ejs'
],
    function (Backbone, Marionette, EJS, MapCollectionView, Template) {
        return Marionette.Layout.extend({
            template: new EJS({text: Template}),

            regions: {
                //pages: "#map-pages"
            },

            initialize: function(options) {
                this.controller = options.controller;
            },

            /*render: function() {
                this.$el.html(this.template.render({
                    current: ''
                }));
                //this.pages.show(new MapCollectionView({controller: this.controller, collection: this.model.pages}));
                return this;
            }*/
        });
    }
);