define([
    'backbone',
    'ejs',
    'views/map/map-collection',
    'text!templates/map/map-layout.ejs'
],
    function (Backbone, EJS, MapCollectionView, Template) {
        return Backbone.Marionette.Layout.extend({
            initialize: function() {
                var root = this;
                //this.model.on('reset', function() { root.render(); });
            },

            template: new EJS({text: Template}),

            regions: {
                pages: "#map-pages"
            },

            render: function() {
                this.$el.html(this.template.render({
                    current: ''
                }));
                this.pages.show(new MapCollectionView({collection: this.model.pages}));
                return this;
            }
        });
    }
);