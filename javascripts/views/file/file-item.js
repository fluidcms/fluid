define([
        'backbone',
        'marionette',
        'jquery-ui',
        'ejs',
        'text!templates/file/file-item.ejs'
    ],
    function (Backbone, Marionette, jUI, EJS, Template) {
        return Marionette.ItemView.extend({
            template: new EJS({text: Template}),

            tagName: "li",

            initialize: function (options) {
                this.model.on("progress", this.fileUploadProgress, this);
                this.model.on("complete", this.fileUploadCompleted, this);
            },

            render: function () {
                var root = this;

                this.$el.html(this.template.render($.extend({}, this.model.attributes, {
                    is_new: this.model.new !== false,
                    rootpath: window.location.rootpath
                })));

                if (this.model.new) {
                    this.getPreview(function (preview) {
                        root.showPreview(preview);
                    });
                }

                this.makeDraggable();

                return this;
            },

            makeDraggable: function () {
                this.$el.draggable({
                    connectToSortable: "div[contenteditable]",
                    helper: "clone",
                    containment: "document",
                    revert: "invalid",
                    revertDuration: 100,
                    iframeFix: true
                });
            },

            showPreview: function (preview) {
                this.$el.find('img').attr({
                    src: preview.image,
                    width: preview.width,
                    height: preview.height
                }).css('display', 'block');
            },

            fileUploadProgress: function (progress) {
                if (progress !== 100) {
                    this.$el.attr('data-block', 'true');
                    this.$el.find('img').addClass('dark');
                    if (!this.$el.find('.progress').length) {
                        this.$el.append('<div class="progress"><div style="width: ' + progress + '%;"></div></div>');
                    } else {
                        this.$el.find('.progress div').css('width', progress + '%');
                    }
                }
            },

            fileUploadCompleted: function () {
                var element = this.$el.removeAttr('data-block');
                element.find('.progress').remove();
                element.find('img').removeClass('dark');
            },

            getPreview: function (callback) {
                var root = this;
                var reader = new FileReader();
                var preview = {};
                reader.onload = (function (preview, reader) {
                    return function (e) {
                        var tmpImg = new Image();
                        tmpImg.src = reader.result;
                        tmpImg.onload = function () {
                            var size = root.getPreviewSize(tmpImg.width, tmpImg.height);
                            preview.image = reader.result;
                            preview.width = size.width;
                            preview.height = size.height;
                            callback(preview);
                        };
                    };
                }(preview, reader));
                reader.readAsDataURL(this.model.new);
            },

            getPreviewSize: function (width, height) {
                var max = 82;
                if (width > height) {
                    if (width > max) {
                        height *= max / width;
                        width = max;
                    }
                } else {
                    if (height > max) {
                        width *= max / height;
                        height = max;
                    }
                }
                return {
                    width: width,
                    height: height
                };
            }
        });
    }
);