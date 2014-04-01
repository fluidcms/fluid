define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/page/page.ejs'
],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.CompositeView.extend({
            initialize: function() {
                this.model.fetch();
            },

            events: {
                "click a": "editPage"
            },

            template: new EJS({text: Template})
        });
    }
);