define([
        'backbone',
        'marionette',
        'views/file/file-item',
        'text!templates/file/file-composite.ejs'
    ],
    function (Backbone, Marionette, FileItemView, Template) {
        return Marionette.CompositeView.extend({
            events: {
                "change #fileUploader": "addFile"
            },

            template: new EJS({text: Template}),

            className: "files",

            itemView: FileItemView,

            itemViewContainer: "ul",

            itemViewOptions: function() {
                return {
                    controller: this.controller
                };
            },

            initialize: function (options) {
                this.controller = options.controller;
            },

            addFile: function(e) {
                var root = this;
                var files = e.target.files;
                if (typeof files !== 'undefined') {
                    $.each(files, function (key, file) {
                        root.collection.addFile(file);
                    });
                }
            }
        });
    }
);
