define([
    'backbone',
    'marionette',
    'ejs',
    'text!templates/variable/variable-type/content.ejs'
],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.ItemView.extend({
            template: new EJS({text: Template}),

            className: 'content'
        });
    }
);