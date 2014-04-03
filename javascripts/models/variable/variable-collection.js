define([
    'backbone',
    'models/variable/variable',
    'models/variable/variable-group'
], function (Backbone, Variable, VariableGroup) {
    return Backbone.Collection.extend({
        model: Variable,
        hasGroups: false,
        hasVariables: false,
        groups: [],
        variables: [],
        page: null,

        initialize: function(models, options) {
            this.page = options.page;
        },

        reset: function(models) {
            var root = this;
            this.models = [];
            this.groups = [];
            this.variables = [];

            $.each(models, function(key, item) {
                if (typeof item.variables !== 'undefined') {
                    root.hasGroups = true;
                    var group = new VariableGroup(item.variables, {name: item.name, collection: root});
                    root.models.push(group);
                    root.groups.push(group);
                } else {
                    root.hasVariables = true;
                    var variable = new Variable(item, {collection: root});
                    root.models.push(variable);
                    root.variables.push(variable);
                }
            });

            this.length = this.models.length;
        },

        getGroups: function() {
            return this.groups;
        },

        save: function() {
            this.page.save();
        }
    });
});
