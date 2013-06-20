define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        url: fluidBranch + '/languages',

        initialize: function () {
            this.fetch();
        }
    });
});
