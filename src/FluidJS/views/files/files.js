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
            'click a[data-action="addFile"]': "selectFile",
            "change #fileUploader": "uploader",
            "contextmenu li img": "contextmenu"
        },

        className: 'files',

        template: new EJS({url: ' javascripts/templates/files/files.ejs?' + (new Date()).getTime()}),  // !! Remove for production
        fileTemplate: new EJS({url: ' javascripts/templates/files/file.ejs'}),  // !! Remove for production

        initialize: function (attrs) {
            var root = this;
            this.collection = attrs.collection;
            this.collection.on("reset add remove", this.render, this);
            this.collection.on("progress", this.progress, this);
            this.collection.on("complete", this.complete, this);
        },

        render: function () {
            var root = this;
            this.$el.html(this.template.render({files: this.collection}));
            $("#main #content").append(this.$el);
            this.collection.each(function(item, key) {
                root.addFile(item, key);
            });
            return this;
        },

        addFile: function(item, key) {
            var root = this;
            if (typeof item.get('preview') !== 'undefined' && typeof item.get('preview').image !== 'undefined' && item.get('preview').image !== null) {
                root.$el.find('li[data-id="'+item.id+'"] img').remove();
                root.$el.find('li[data-id="'+item.id+'"] span').before($(root.fileTemplate.render({preview: item.get('preview')})));
                root.draggable(root.$el.find('li[data-id="'+item.id+'"] img'));
            } else {
                item.on('preview', function() {
                    root.$el.find('li[data-id="'+this.id+'"] img').remove();
                    root.$el.find('li[data-id="'+this.id+'"] span').before($(root.fileTemplate.render({preview: this.get('preview')})));
                    root.draggable(root.$el.find('li[data-id="'+this.id+'"] img'));
                });
                item.getPreview();
            }
        },

        draggable: function(item) {
            item.draggable({
                connectToSortable: "div[contenteditable]",
                helper: "clone",
                containment: "document",
                revert: "invalid",
                iframeFix: true
            });
        },

        hide: function() {
            this.$el.hide();
        },

        show: function() {
            this.$el.show();
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
        },

        // File upload methods
        uploader: function (e) {
            var root = this;
            var files = e.target.files;
            if (typeof files !== 'undefined') {
                $.each(files, function (key, file) {
                    root.collection.addFile(file);
                });
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

        complete: function (model) {
            var element = this.$el.find('li[data-id=' + model.get('id') + ']').removeAttr('data-block');
            element.find('.progress').remove();
            element.find('img').removeClass('dark');
        }
    });
});