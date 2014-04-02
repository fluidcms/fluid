define([
    'backbone',
    'marionette',
    'ejs',
    'views/variable/variable-collection',
    'text!templates/variable/variable-layout.ejs'
],
    function (Backbone, Marionette, EJS, VariableCollectionView, Template) {
        return Marionette.Layout.extend({
            template: new EJS({text: Template}),

            events: {
                "click nav a": "changeGroup"
            },

            regions: {}, // For some reasons, regions are not working here, we init regions in the render function

            render: function() {
                this.$el.html(this.template.render({
                    hasVariables: this.collection.hasVariables,
                    hasGroups: this.collection.hasGroups,
                    groups: this.collection.getGroups()
                }));
                this.variableCollectionRegion = new Marionette.Region({
                    el: this.$el.find('[data-name="variable-collection"]')
                });
                this.variableCollectionRegion.show(new VariableCollectionView({collection: this.collection}));
                return this;
            },

            changeGroup: function(e) {
                var group = $(e.currentTarget).attr('data-group');
                if (typeof group === 'undefined') {
                    this.variableCollectionRegion.show(new VariableCollectionView({collection: this.collection}));
                } else {
                    console.log(group);
                }
            }
        });
    }
);