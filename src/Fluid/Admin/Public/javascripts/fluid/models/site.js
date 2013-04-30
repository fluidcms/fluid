define(['backbone'], function (Backbone) {
    var Model = Backbone.Model.extend({
        url: 'site',

        initialize: function () {
        }
    });

    return {
        Model: Model
    };
});