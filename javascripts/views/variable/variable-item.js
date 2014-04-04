define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/variable/variable-item.ejs',
    'views/variable/variable-type/string',
    'views/variable/variable-type/content'
],
    function (Backbone, Marionette, EJS, VariableItemTemplate, VariableStringView, VariableContentView) {
        return Marionette.ItemView.extend({
            variableItemTemplate: new EJS({text: VariableItemTemplate}),

            variableView: null,

            events: {
                "click": "edit"
            },

            initialize: function(options) {
                this.controller = options.controller;
                this.model.on('change', this.render);
            },

            render: function() {
                if (typeof this.model.attributes !== 'undefined' && typeof this.model.attributes.type !== 'undefined') {
                    if (this.model.attributes.type !== 'array') {
                        this.$el.html(this.variableItemTemplate.render(this.model.attributes));
                        this.value = new Marionette.Region({el: this.$el.find('[data-name="value"]')});
                        switch (this.model.attributes.type) {
                            case 'string':
                                this.variableView =new VariableStringView({model: this.model});
                                this.value.show(this.variableView);
                                break;
                            case 'content':
                                this.variableView =new VariableContentView({model: this.model});
                                this.value.show(this.variableView);
                                break;
                        }
                    }
                }
                return this;
            },

            edit: function() {
                this.controller.editor(this.model.attributes.type, this.model, this.variableView);
            }
        });
    }
);