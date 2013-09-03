define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        className: 'tools',

        dropbox: {},

        rendered: false,

        textEnabled: false,

        editor: null,

        template: new EJS({url: 'javascripts/fluid/templates/tools/tools.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.map = attrs.map;
            this.map.on('editing', this.trackEditing, this);
        },

        render: function () {
            this.$el.html(this.template.render({
                textEnabled: this.textEnabled
            }));
            $("#main #content").append(this.$el);
            this.rendered = true;
            return this;
        },

        hide: function() {
            this.$el.hide();
        },

        show: function() {
            if (!this.rendered) {
                this.render();
            }
            this.$el.show();
        },

        trackEditing: function() {
            this.map.editor.view.on('editing', this.enable, this);
            this.map.editor.view.on('stopEditing', this.disable, this);
        },

        enable: function() {
            var root = this;

            if (this.map.editor.view.editor.type === 'content') {
                this.textEnabled = true;
                this.editor = this.map.editor.view.editor.$el.find('[contenteditable]');
                $(document).on('keypress keyup click mouseup', null, {root: this}, this.analyzeText);

                $(this.$el).find('div.text a[data-role]').on('mousedown', function(e) { root.formatText(e); }).on('mousedown', function() { root.analyzeText({data: {root: root}}); });
                $(this.$el).find('div.text select').on('change', function(e) { root.formatText(e); }).on('change', function() { root.analyzeText({data: {root: root}}); });

                this.analyzeText({data: {root: this}});
            }

            if (this.rendered) {
                if (this.textEnabled) {
                    this.$el.find('div.text select').removeAttr('disabled');
                    this.$el.find('div.text a').removeAttr('data-disabled');
                }
            }
        },

        disable: function() {
            var root = this;
            this.textEnabled = false;

            if (this.rendered) {
                this.$el.find('div.text select').attr('disabled', 'true').val('p');
                this.$el.find('div.text a').attr('data-disabled', "true").removeClass('active');
            }

            $(this.$el).find('div.text a[data-role]').off('mousedown');
            $(this.$el).find('div.text select').off('change');
            $(document).off('keypress keyup click mouseup', this.analyzeText);
        },

        analyzeText: function(e) {
            var root = e.data.root;

            var fontStyles = ['bold', 'italic', 'underline', 'strikeThrough'];

            if (root.editor.is(':focus')) {
                // Check if there is a paragrpah
                // TODO: shouldnt this be part of the editor view instead?
                // !!!!!!
                if (root.editor.find('p').length === 0) {
                    $("<p><br></p>").appendTo(root.editor);
                }

                // Check font styles
                $.each(fontStyles, function(key, value) {
                    if (document.queryCommandValue(value) === 'true') {
                        root.$el.find('[data-role="'+value+'"]').addClass('active');
                    } else {
                        root.$el.find('[data-role="'+value+'"]').removeClass('active');
                    }
                });

                // Check element type
                var tag = root.checkCursorInElement();

                // noinspection FallthroughInSwitchStatementJS
                switch(tag) {
                    case 'H1':
                    case 'H2':
                    case 'H3':
                    case 'H4':
                    case 'H5':
                    case 'H6':
                    case 'UL':
                    case 'OL':
                        root.$el.find('[data-role="tag"]').val(tag.toLowerCase());
                        break;
                    default:
                        root.$el.find('[data-role="tag"]').val('p');
                        break;
                }
            }
        },

        checkCursorInElement: function() {
            var sel, containerNode, parentNode;
            if (window.getSelection) {
                sel = window.getSelection();
                if (sel.rangeCount > 0) {
                    containerNode = sel.getRangeAt(0).commonAncestorContainer;
                }
            } else if ( (sel = document.selection) && sel.type != "Control" ) {
                containerNode = sel.createRange().parentElement();
            }
            while (containerNode) {
                if (containerNode.nodeType == 1 && containerNode.tagName == 'DIV' && containerNode.getAttribute('contenteditable')) {
                    break;
                }
                parentNode = containerNode;
                containerNode = containerNode.parentNode;
            }

            if (typeof parentNode !== 'undefined' && parentNode.nodeType == 1) {
                return parentNode.tagName;
            }
            return false;
        },

        formatText: function(e) {
            e.preventDefault();

            var role = $(e.currentTarget).attr('data-role');
            if (role === 'tag') {
                role = $(e.currentTarget).val();
            }

            // noinspection FallthroughInSwitchStatementJS
            switch(role) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    document.execCommand('formatBlock', false, role);
                    break;
                case 'ul':
                case 'ol':
                    this.formatList(role);
                    break;
                case 'p':
                    this.formatParagraph();
                    break;
                case 'bold':
                case 'italic':
                case 'underline':
                case 'strikeThrough':
                    document.execCommand(role, false, null);
                    break;
            }

            return false;
        },

        formatList: function(type) {
            if (type === 'ol') {
                document.execCommand('insertOrderedList', false, null);
            } else if (type === 'ul') {
                document.execCommand('insertUnorderedList', false, null);
            }

            var selection = window.getSelection();
            var range = selection.getRangeAt(0);

            // Find paragraph or div
            var parent = range.startContainer;
            while (typeof parent.parentNode !== 'undefined' && parent.nodeName !== 'DIV' && parent.nodeName !== 'P') {
                if (parent.nodeName == 'UL' || parent.nodeName == 'OL') {
                    var child = parent;
                }
                parent = parent.parentNode;
            }

            if (typeof child !== 'undefined' && parent.getAttribute('contenteditable') !== 'true' && (parent.nodeName === 'DIV' || parent.nodeName === 'P')) {
                $(parent).before(child);
                $(parent).remove();

                // TODO: fix this, this is not selecting the previous range all the time
                selection.removeAllRanges();
                selection.addRange(range);
            }
        },

        formatParagraph: function() {
            var range = window.getSelection().getRangeAt(0);

            // Remove from list before applying paragraph
            var parent = range.startContainer;
            while (parent !== null && typeof parent.parentNode !== 'undefined' && parent.nodeName !== 'DIV') {
                if (parent.nodeName == 'UL') {
                    document.execCommand('insertUnorderedList', false, null);
                } else if (parent.nodeName == 'OL') {
                    document.execCommand('insertOrderedList', false, null);
                }
                parent = parent.parentNode;
            }

            document.execCommand("formatBlock", false, "p");
        }
    });
});