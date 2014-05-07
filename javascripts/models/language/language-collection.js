define(['backbone', 'models/language/language'], function (Backbone, Language) {
    return Backbone.Collection.extend({
        model: Language,

        current: null,

        initialize: function (models, options) {
            this.models = [];
            if (typeof models === 'array' || typeof models === 'object') {
                for (var i = 0, len = models.length; i < len; ++i) {
                    this.add(new Language({
                        name: models[i]
                    }))
                }
            }
        },

        getCurrent: function () {
            return this.current;
        },

        setCurrent: function (name) {
            for (var i = 0, len = this.models.length; i < len; ++i) {
                if (this.models.get('')) {

                }
            }
        }
    });
});