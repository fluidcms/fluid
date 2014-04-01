define(['backbone', 'models/variable/variable'], function (Backbone, Variable) {
    return Backbone.Collection.extend({
        model: Variable,
        initialize: function (models, options) {
        }
    });
});
