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

        reset: function(models) {
            var root = this;
            this.models = [];
            this.groups = [];
            this.variables = [];

            $.each(models, function(key, item) {
                if (typeof item.variables !== 'undefined') {
                    root.hasGroups = true;
                    var group = new VariableGroup(item.variables, {name: item.name});
                    root.models.push(group);
                    root.groups.push(group);
                } else {
                    root.hasVariables = true;
                    var variable = new Variable(item);
                    root.models.push(variable);
                    root.variables.push(variable);
                }
            });

            this.length = this.models.length;
        },

        getGroups: function() {
            return this.groups;
        }
    });
});
