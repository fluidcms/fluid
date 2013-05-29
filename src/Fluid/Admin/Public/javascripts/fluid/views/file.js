define(['backbone', 'ejs', 'jquery-ui', 'views/contextmenu', 'views/modal'], function (Backbone, EJS, jUI, ContextMenu, Modal) {
    var View = Backbone.View.extend({
        events: {
            'click a[data-action="addFile"]': "selectFile",
            "change #fileUploader": "uploader",
            "contextmenu li": "contextmenu"
        },

        className: 'files',

        template: new EJS({url: 'javascripts/fluid/templates/file/file.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.render();
            this.collection = attrs.collection;
            this.collection.on("reset add remove", this.render, this);
            this.collection.on("progress", this.progress, this);
            this.collection.on("display", this.display, this);
            this.collection.on("complete", this.complete, this);
        },

        render: function () {
            this.$el.html(this.template.render({collection: this.collection}));
            $("#main #content").append(this.$el);
            return this;
        },

        uploader: function (e) {
            var root = this;
            var files = e.target.files;
            if (typeof files !== 'undefined') {
                $.each(files, function (key, file) {
                    root.collection.addFile(file);
                });
            }
        },

        contextmenu: function (e) {
            e.preventDefault();
            if ($(e.currentTarget).attr('data-block') !== 'true') {
                var contextMenu = new ContextMenu({url: 'javascripts/fluid/templates/file/contextmenu.ejs', parent: this, event: e}).render();
            }
        },

        copyLink: function (element) {
            var target = $(element).parent('li');
            var path = "/fluidcms/"+fluidBranch+"/files/" + target.attr('data-id') + "/" + target.attr('data-name');

            new Copy({content: path}).render();
        },

        deleteImage: function (element) {
            var id = $(element).parent('li').attr('data-id');
            var model = this.collection.get(id);
            if (confirm('Are you sur you want to delete ' + model.get("name") + '?')) {
                model.destroy();
            }
        },

        progress: function (model, progress) {
            if (progress !== 100) {
                var element = this.$el.find('li[data-id=' + model.get('id') + ']');
                element.attr('data-block', 'true');
                element.find('img').addClass('dark');
                if (!element.find('.progress').length) {
                    element.append('<div class="progress"><div style="width: ' + progress + '%;"></div></div>');
                } else {
                    element.find('.progress div').css('width', progress + '%');
                }
            }
        },

        display: function (model) {
            this.$el.find('li[data-id=' + model.get('id') + '] img')
                .css('display', 'block')
                .attr({src: model.get('previewSrc'), width: model.get('previewWidth'), height: model.get('previewHeight')});
        },

        complete: function (model) {
            var element = this.$el.find('li[data-id=' + model.get('id') + ']').removeAttr('data-block');
            element.find('.progress').remove();
            element.find('img').removeClass('dark');
        }
    });

    var Copy = Modal.extend({
        events: _.extend({
            "copy :input": "copied"
        }, Modal.prototype.events),

        template: new EJS({url: 'javascripts/fluid/templates/file/copy-modal.ejs?' + (new Date()).getTime()}),  // !! Remove for production

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
    });

    return View;
});