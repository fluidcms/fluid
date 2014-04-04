define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/variable/variable-type/content.ejs'
],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.ItemView.extend({
            template: new EJS({text: Template}),

            className: 'content',

            render: function() {
                this.$el.html(this.template.render($.extend({}, {
                    rendered_value: this.renderValue(this.model)
                }, this.model.attributes)));
                return this;
            },

            renderValue: function(model) {
                if (typeof model.attributes.value !== 'undefined') {
                    var data = model.attributes.value;
                    return data.text.replace(/^\s+|\s+$/g, '');
                }
                return null;
            },

            unrenderValue: function(content) {
                content = content.replace(/^\s+|\s+$/g, '');
                return {
                    text: content
                };
            }
        });
    }
);