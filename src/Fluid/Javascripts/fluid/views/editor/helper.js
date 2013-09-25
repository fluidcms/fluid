define(['sanitize'], function (Sanitize) {
    // !!!!!!!!
    // !!!!!!!!
    // TODO: Move all of this in the editor view
    // !!!!!!!!
    // !!!!!!!!
    return function (element, type) {
        $(element)

            // Keyup events
            .on("keyup", function (e) {
                if (e.which == 13) {
                    var selection = window.getSelection();
                    var range = selection.getRangeAt(0);

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
                // Block line return on strings
                if (e.which === 13 && type !== 'content') {
                    return false;
                }

                // Allow br when enter key is pressed with shift
                else if (e.which == 13 && e.shiftKey) {
                    // Insert br if user is pressing shift and enter
                    var selection = window.getSelection();
                    var range = selection.getRangeAt(0);
                    var br = document.createElement("br");

                    // Insert br
                    range.deleteContents();
                    range.insertNode(br);
                    range.setStartAfter(br);
                    range.setEndAfter(br);

                    // Create another br after the one we inserted if there is none
                    if (!$(br).next('br').length) {
                        br = document.createElement("br");
                        range.insertNode(br);
                        range.setStartAfter(br);
                        range.setEndAfter(br);
                    }

                    selection.removeAllRanges();
                    selection.addRange(range);
                    return false;
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