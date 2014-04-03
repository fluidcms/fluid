define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/variable/variable-item.ejs',
    'views/variable/variable-type/string'
],
    function (Backbone, Marionette, EJS, VariableItemTemplate, VariableStringView) {
        return Marionette.ItemView.extend({
            variableItemTemplate: new EJS({text: VariableItemTemplate}),

            render: function() {
                if (typeof this.model.attributes !== 'undefined' && typeof this.model.attributes.type !== 'undefined') {
                    if (this.model.attributes.type !== 'array') {
                        this.$el.html(this.variableItemTemplate.render(this.model.attributes));
                        this.value = new Marionette.Region({el: this.$el.find('[data-name="value"]')});
                        switch (this.model.attributes.type) {
                            case 'string':
                                this.value.show(new VariableStringView({model: this.model}));
                                break;
                        }
                    }
                }
                return this;
            }
        });
    }
);