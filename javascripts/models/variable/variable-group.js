define(['backbone', 'models/variable/variable'], function (Backbone, Variable) {
    return Backbone.Collection.extend({
        model: Variable,
        name: null,

        _prepareModel: function (model, options) {
            options.group = this;
            options.collection = this.collection;
            return Backbone.Collection.prototype._prepareModel.call(this, model, options);
        },

        initialize: function(models, options) {
            this.name = options.name;
            this.collection = options.collection;
        },

        toJSON: function(options) {
            var retval = {variables: []};
            var data = Backbone.Collection.prototype.toJSON.call(this, options);
            for (var i = 0, len = data.length; i < len; ++i) {
                retval.variables.push(data[i]);
            }
            retval.name = this.name;
            return retval;
        }
    });
});