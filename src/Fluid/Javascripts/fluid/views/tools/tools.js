define(['backbone', 'ejs'], function (Backbone, EJS) {
    return Backbone.View.extend({
        className: 'tools',

        dropbox: {},

        rendered: false,

        textEnabled: false,

        editor: null,

        editorElement: null,

        template: new EJS({url: 'javascripts/fluid/templates/tools/tools.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
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

        enable: function() {
            var root = this;

            if (!this.rendered) {
                setTimeout(function() { root.enable(); }, 10);
                return false;
            }

            if (this.editor.type === 'content') {
                this.textEnabled = true;
                this.editorElement = this.editor.$el.find('[contenteditable]');
                $(document).on('keypress keyup click mouseup', null, {root: this}, this.analyzeText);

                $(this.$el).find('div.text a[data-role]').on('mousedown', function(e) { root.formatText(e); }).on('mousedown', function() { root.analyzeText({data: {root: root}}); });

                $(this.$el).find('div.text select').on('change', function(e) {
                    root.formatText(e);
                    root.analyzeText({data: {root: root}});
                });

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

            if (root.editorElement.is(':focus')) {
                // Check if there is a paragrpah
                // TODO: shouldnt this be part of the editor view instead?
                // !!!!!!
                if (root.editorElement.find('p').length === 0) {
                    $("<p><br></p>").appendTo(root.editorElement);
                }

                // Check if there is spans, and destroy them
                if (root.editorElement.find('span').length !== 0) {
                    root.cleanContent();
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
                var tree = root.checkCursorInElement();

                // Check if text is not wrapped in an element
                if (!tree) {
                    root.cleanContent();
                } else {
                    var found = false;
                    var list;
                    $.each(tree, function(key, node) {
                        // Check if we are in a div
                        if (node.tagName === 'DIV') {
                            root.replaceDivWithP();
                        }

                        // Headers
                        if (node.tagName === 'P' || node.tagName === 'H1' || node.tagName === 'H2' || node.tagName === 'H3' || node.tagName === 'H4' || node.tagName === 'H5' || node.tagName === 'H6') {
                            found = true;
                            root.$el.find('[data-role="tag"]').val(node.tagName.toLowerCase());
                        }

                        // Paragraph
                        else if (!found) {
                            root.$el.find('[data-role="tag"]').val('p');
                        }

                        // Lists
                        if ((node.tagName === 'UL' || node.tagName === 'OL') && (typeof list === 'undefined' || list === null)) {
                            list = node.tagName.toLowerCase();
                        }

                    });

                    root.$el.find('[data-role="ol"]').removeClass('active');
                    root.$el.find('[data-role="ul"]').removeClass('active');

                    if (typeof list !== 'undefined' && list !== null) {
                        root.$el.find('[data-role="'+list+'"]').addClass('active');
                    }
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
            var tree = [];

            while (containerNode) {
                if (containerNode.nodeType == 1 && containerNode.tagName == 'DIV' && containerNode.getAttribute('contenteditable')) {
                    break;
                }
                parentNode = containerNode;
                containerNode = containerNode.parentNode;
                tree.push(parentNode);
            }

            if (typeof parentNode !== 'undefined' && parentNode.nodeType == 1) {
                return tree;
            }
            return false;
        },

        replaceDivWithP: function() {
            var selection = window.getSelection();
            var range = selection.getRangeAt(0);
            var element = range.startContainer.parentNode;
            $(element).replaceWith($('<p>' + $(element).html() + '</p>'));
        },

        cleanContent: function() {
            // Remove lone brs
            $.each(this.editorElement.find('>br'), function(key, node) {
                $(node).replaceWith($(node).html());
            });

            // Remove spans
            $.each(this.editorElement.find('span'), function(key, node) {
                $(node).replaceWith($(node).html());
            });

            // Replace text nodes by paragraphs
            $.each(this.editorElement.contents(), function(key, node) {
                if (node.nodeName === '#text') {
                    var nodeValue = node.nodeValue.replace(/^\s+|\s+$/g, '');
                    if (nodeValue === '') {
                        $(node).remove();
                    } else {
                        $(node).replaceWith($('<p>' + nodeValue + '</p>'));
                    }
                }
            });
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
                    this.formatBlock(role);
                    break;
                case 'ul':
                case 'ol':
                    this.formatList(role);
                    break;
                case 'p':
                    this.formatBlock(role);
                    break;
                case 'bold':
                case 'italic':
                case 'underline':
                case 'strikeThrough':
                    document.execCommand(role, false, null);
                    break;
                case 'anchor':
                    this.formatAnchor();
                    break;
                case 'indentRight':
                    this.addIndent();
                    break;
            }

            this.analyzeText({data: {root: this}});
            return false;
        },

        formatAnchor: function() {
            var range = window.getSelection().getRangeAt(0);

            var url = "";
            var parent = range.startContainer.parentNode;
            if (parent.nodeName == 'A') {
                url = $(parent).attr('href');
            }

            if (url = prompt(fluidLanguage['editor']['text']['linkPrompt'], url)) {
                document.execCommand("createLink", false, url);
            } else {
                if (url == '') {
                    document.execCommand("unlink", false, null);
                }
            }
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
            while (typeof parent.parentNode !== 'undefined' && parent.nodeName !== 'DIV' && parent.nodeName !== 'P' && parent.nodeName !== 'H1' && parent.nodeName !== 'H2' && parent.nodeName !== 'H3' && parent.nodeName !== 'H4' && parent.nodeName !== 'H5' && parent.nodeName !== 'H6') {
                if (parent.nodeName == 'UL' || parent.nodeName == 'OL') {
                    var child = parent;
                }
                parent = parent.parentNode;
            }

            if (typeof child !== 'undefined' && parent.getAttribute('contenteditable') !== 'true' && (parent.nodeName === 'DIV' || parent.nodeName === 'P' || parent.nodeName === 'H1' || parent.nodeName === 'H2' || parent.nodeName === 'H3' || parent.nodeName === 'H4' || parent.nodeName === 'H5' || parent.nodeName === 'H6')) {
                // TODO: divide into 2 blocks and put in the middle
                $(parent).after(child);
                var parentContent = $(parent).html().replace(/^\s+|\s+$/g, '');
                if (parentContent === '') {
                    $(parent).remove();
                }

                // TODO: fix this, this is not selecting the previous range all the time
                selection.removeAllRanges();
                selection.addRange(range);
            }
        },

        addIndent: function() {
            var selection;
            var range;
            var parent;
            var container;

            // Indent list (list inside list)
            selection = window.getSelection();
            range = selection.getRangeAt(0);
            container = range.startContainer;
            if (typeof container.tagName === 'undefined' || container.tagName === null) {
                container = container.parentNode;
            }
            parent = container;
            var list = false;
            var listType;
            while (typeof parent.parentNode !== 'undefined') {
                if (parent.tagName === 'DIV' && parent.getAttribute('contenteditable') !== 'true') {
                    break;
                }
                else if (parent.tagName === 'LI') {
                    list = parent;
                }
                else if (parent.tagName === 'UL') {
                    listType = 'UL';
                }
                else if (parent.tagName === 'OL') {
                    listType = 'OL';
                }
                parent = parent.parentNode;
            }

            if (list && typeof listType !== 'undefined' && listType !== null) {
                var ul = document.createElement("ul");
                var li = document.createElement("li");
                range.surroundContents(li);
                selection.removeAllRanges();
                selection.addRange(range);
                range.surroundContents(ul);
                selection.removeAllRanges();
                selection.addRange(range);

                // Remove trailing br
                if ($(ul).next('br').length) {
                    $(ul).next('br').remove();
                }



                // Add br before list if there is none

//                console.log($(list).text());
            }
        },

        formatBlock: function(type) {
            var parent;
            var container;
            var range;

            range = window.getSelection().getRangeAt(0);
            container = range.startContainer;
            parent = container.parentNode;

            // Check if in list
            if (container.tagName === 'LI') {
                parent = container;
            }

            if (parent.tagName === 'LI') {
                var content = $(parent).html().replace(/^\s+|\s+$/g, '');

                if (content === '') {
                    content = '<br>';
                }

                $(parent).html('<'+type.toLowerCase()+'>' + content + '</'+type.toLowerCase()+'>');
            }

            // Not in list
            else {
                document.execCommand("formatBlock", false, type);

                range = window.getSelection().getRangeAt(0);

                // Find paragraph or div
                parent = range.startContainer.parentNode;
                var element = false;
                var inContentEditable = false;
                var paragraph = false;
                while (typeof parent !== 'undefined' && parent !== null && parent !== false) {
                    if (parent.nodeName == 'DIV' && parent.getAttribute('contenteditable') == 'true') {
                        parent = false;
                        inContentEditable = true;
                    }

                    else if (parent.nodeName == type.toUpperCase()) {
                        element = parent;
                    }

                    else if (parent.nodeName == 'P' || parent.nodeName == 'H1' || parent.nodeName == 'H2' || parent.nodeName == 'H3' || parent.nodeName == 'H4' || parent.nodeName == 'H5' || parent.nodeName == 'H6') {
                        paragraph = parent;
                    }

                    parent = parent.parentNode;
                }

                if (inContentEditable && paragraph && element) {
                    // TODO: divide into 2 blocks and put in the middle
                    $(paragraph).before($(element));
                }
            }
        }
    });
});