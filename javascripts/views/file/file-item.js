define([
        'backbone',
        'marionette',
        'ejs',
        'text!templates/file/file-item.ejs'
    ],
    function (Backbone, Marionette, EJS, Template) {
        return Marionette.ItemView.extend({
            template: new EJS({text: Template}),

            initialize: function (options) {
            }
        });
    }
);