define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/page/page.ejs'
],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.ItemView.extend({
            initialize: function() {
            },

            events: {
                "click a": "editPage"
            },

            template: new EJS({text: Template})
        });
    }
);