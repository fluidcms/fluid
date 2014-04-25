define(['backbone', 'ejs', 'jquery-ui', 'views/helpers/contextmenu', 'views/helpers/modal'], function (Backbone, EJS, jUI, ContextMenu, Modal) {
    // Copy file link modals
    var Copy = Backbone.View.extend($.extend({}, Modal, {
        events: _.extend({
            "copy :input": "copied"
        }, Modal.events),

        template: new EJS({url: ' javascripts/templates/files/copymodal.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.content = attrs.content;
        },

        renderData: function () {
            return {
                content: this.content
            };
        },

        copied: function (e) {
            var root = this;
            setTimeout(function () {
                root.close()
            }, 100);
        }
    }));

    // Files view
    return Backbone.View.extend({
        events: {
            "contextmenu li img": "contextmenu"
        },

        contextmenu: function (e) {
            e.preventDefault();
            if ($(e.currentTarget).attr('data-block') !== 'true') {
                var contextMenu = new ContextMenu({url: ' javascripts/templates/files/filecm.ejs', parent: this, event: e}).render();
            }
        },

        copyLink: function (element) {
            var target = $(element).parent('li');
            var model = this.collection.get(target.attr('data-id'));

            new Copy({content: model.get('src')}).render();
        },

        deleteImage: function (element) {
            var id = $(element).parent('li').attr('data-id');
            var model = this.collection.get(id);
            if (confirm('Are you sur you want to delete ' + model.get("name") + '?')) {
                model.destroy();
            }
        }
    });
});