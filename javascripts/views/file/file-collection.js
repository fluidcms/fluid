define([
        'backbone',
        'marionette',
        'views/file/file-item'
    ],
    function (Backbone, Marionette, FileItemView) {
        return Marionette.CollectionView.extend({
            tagName: 'ul',

            className: 'files',

            itemView: FileItemView,

            itemViewOptions: function() {
                return {
                    controller: this.controller
                };
            },

            initialize: function(options) {
                this.controller = options.controller;
            }
        });
    }
);