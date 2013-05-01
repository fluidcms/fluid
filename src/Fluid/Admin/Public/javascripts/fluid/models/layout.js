define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        url: fluidBranch + '/layouts',

        initialize: function () {
            this.fetch();
        }
    });
});
