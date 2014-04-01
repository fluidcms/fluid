define(['backbone', 'models/variable/variable'], function (Backbone, Variable) {
    return Backbone.Collection.extend({
        model: Variable,
        name: null,

        initialize: function(models, options) {
            this.name = options.name;
        }
    });
});
