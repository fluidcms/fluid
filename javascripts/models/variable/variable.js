define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        collection: null,
        group: null,

        initialize: function(attributes, options) {
            if (typeof options.group !== 'undefined') {
                this.group = options.group;
                this.collection = options.group.collection;
            } else {
                this.collection = options.collection;
            }
        },

        save: function(attributes, options) {
            if (typeof attributes !== 'undefined') {
                this.set(attributes);
            }
            this.collection.save(null, {
                success: function(response) {
                    if (typeof options.success === 'function') {
                        options.success.call(response);
                    }
                }
            });
        }
    });
});