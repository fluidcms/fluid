define(['lib/sanitize'], function (Sanitize) {
    // !!!!!!!!
    // !!!!!!!!
    // TODO: Move all of this in the editor view
    // !!!!!!!!
    // !!!!!!!!
    return function (element, type, editor) {
        $(element)

            // Keyup events
            .on("keyup", function (e) {
                if (e.which == 13) {
                    var selection = window.getSelection();
                    var range = selection.getRangeAt(0);

                    // Force DIV into P
                    var parent = range.startContainer;
                    while (parent && typeof parent.parentNode !== 'undefined') {
                        if (parent.nodeName == 'DIV' && parent.getAttribute('contenteditable') === 'true') {
                            return;
                        } else if(parent.nodeName == 'DIV') {
                            document.execCommand("formatBlock", false, "p");
                            return;
                        } else  {
                            parent = parent.parentNode;
                        }
                    }
                }
            })

            // Keypress events
            .on("keypress", function (e) {
                var selection;
                var range;
                var br;
                var parent;

                // Block line return on strings
                if (e.which === 13 && type !== 'content') {
                    return false;
                }

                // Allow br when enter key is pressed with shift
                else if (e.which == 13 && e.shiftKey) {
                    // Insert br if user is pressing shift and enter
                    selection = window.getSelection();
                    range = selection.getRangeAt(0);
                    br = document.createElement("br");

                    // Insert br
                    range.deleteContents();
                    range.insertNode(br);
                    range.setStartAfter(br);
                    range.setEndAfter(br);

                    // Create another br after the one we inserted if there is no content
                    var textAfter = br.nextSibling.nodeValue.replace(/^\s+|\s+$/g, '');
                    if (!$(br).next('br').length && textAfter == '') {
                        br = document.createElement("br");
                        range.insertNode(br);
                        range.setStartAfter(br);
                        range.setEndAfter(br);
                    }

                    selection.removeAllRanges();
                    selection.addRange(range);
                    return false;
                }

                else if (e.which == 13) {
                    selection = window.getSelection();
                    range = selection.getRangeAt(0);

                    var container = range.startContainer;
                    if (typeof container.tagName === 'undefined' || container.tagName === null) {
                        container = container.parentNode;
                    }

                    // Check if use is in a block element in a list and wants to exit the block element
                    if (container.tagName === 'P' || container.tagName === 'H1' || container.tagName === 'H2' || container.tagName === 'H3' || container.tagName === 'H4' || container.tagName === 'H5' || container.tagName === 'H6') {
                        parent = container;
                        var list = false;
                        while (typeof parent.parentNode !== 'undefined') {
                            if (parent.tagName === 'DIV' && parent.getAttribute('contenteditable') !== 'true') {
                                break;
                            }
                            if (parent.tagName === 'LI') {
                                list = true;
                            }
                            parent = parent.parentNode;
                        }

                        if (list === true) {
                            var nextBr = $(container).next('br');

                            // Create a br so we can select the range
                            if (!nextBr.length) {
                                br = document.createElement("br");
                                $(container).after(br);
                            }
                            range.setStartAfter(container);
                            range.setEndAfter(container);
                            selection.removeAllRanges();
                            selection.addRange(range);

                            $(br).remove();
                            return true;
                        }
                    }

                    // Verify that lists don't try to escape their position
                    else if (container.tagName === 'LI') {
                        parent = container.parentNode;
                        var list = false;
                        while (typeof parent.parentNode !== 'undefined') {
                            if (parent.tagName === 'DIV' && parent.getAttribute('contenteditable') !== 'true') {
                                break;
                            }
                            if (parent.tagName === 'LI') {
                                list = parent;
                            }
                            parent = parent.parentNode;
                        }

                        if (list) {
                            var content = $(list).clone();
                            $.each(content.find('>ul:last>li:last'), function(key, node) {
                                if ($(node).html() === $(container).html()) {
                                    $(node).remove();
                                }
                            });
                            content = content.html();
                            var prev = $(container).prev('li');
                            setTimeout(function() { editor.fixIndentedList(container, prev, list, content); }, 0);
                            return true;
                        }
                    }
                }

                return true;
            })

            // Sanitize Paste
            .on('paste', function () {
                var selection = window.getSelection();
                var range = selection.getRangeAt(0);

                // Create dummy textarea
                var textarea = $("<textarea id='copyCapter'></textarea>");
                $(document.body).append(textarea);
                textarea.focus();

                setTimeout(function () {
                    var value = textarea.val();
                    textarea.remove();

                    var lines = value.split(/[\r\n]/);
                    var realLines = [];
                    var lastKey = 0;
                    for(var i = 0; i < lines.length; i++) {
                        var line = lines[i].trim();

                        if (line !== '') {
                            realLines.push({type: 'line', value: line});
                            lastKey = realLines.length - 1;
                        } else {
                            realLines[lastKey]['type'] = 'paragraph';
                        }
                    }

                    selection.removeAllRanges();
                    selection.addRange(range);

                    range.deleteContents();

                    for(var j = 0; j < realLines.length; j++) {
                        var node;
                        range.insertNode(node = document.createTextNode(realLines[j].value));
                        range.setStartAfter(node);
                        range.setEndAfter(node);

                        // TODO: add support for paragraphs
                        //if (realLines[j].type === 'line') {
                            range.insertNode(node = document.createElement("br"));
                            range.setStartAfter(node);
                            range.setEndAfter(node);
                        //} else {
                        //}

                    }
                }, 0);

                /*
                // TODO: revisit this code
                setTimeout(function () {
                    if (type === 'content') {
                        var s = new Sanitize({
                            elements: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'font', 'br', 'b', 'strong', 'u', 'i', 'a', 'ul', 'ol', 'li', 'img'],
                            attributes: {
                                a: ['href', 'title'],
                                div: ['style'],
                                font: ['color', 'size'],
                                img: ['src', 'width', 'height', 'alt', 'id']
                            },
                            protocols: {
                                a: { href: ['http', 'https', 'mailto'] }
                            }
                        });
                    } else {
                        var s = new Sanitize();
                    }
                    var cleaned_html = s.clean_node(element[0]);
                    $(element).empty().append(cleaned_html);
                    $(element).blur();
                }, 0);*/
            })

            .on('drop', function (e) {
                //e.preventDefault();
                //e.stopPropagation();
            });
    };
});