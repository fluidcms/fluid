define(['sanitize'], function (Sanitize) {
    return function (element, type) {
        $(element)

            // Override return key
            .on("keypress", function (e) {
                if (e.which == 13) {
                    // Use p instead of div in content, use br if shift is used
                    if (type === 'content') {
                        // Insert br if user is pressing shift and enter
                        if (e.shiftKey) {
                            var selection = window.getSelection(),
                                range = selection.getRangeAt(0),
                                br = document.createElement("br");

                            // Insert br
                            range.deleteContents();
                            range.insertNode(br);
                            range.setStartAfter(br);
                            range.setEndAfter(br);
                            selection.removeAllRanges();
                            selection.addRange(range);
                            return false;
                        }
                        // Insert p element instead of divs
                        else {
                            var selection = window.getSelection(),
                                range = selection.getRangeAt(0),
                                element = document.createElement("p");

                            element.innerHTML = "&nbsp;"; // Tricky, chrome does not like empty nodes

                            // Allow normal behavior for li and p
                            var moveOutside = false;
                            var parent = range.startContainer;
                            while (parent && typeof parent.parentNode !== 'undefined') {
                                // Allow normal behavior on li and p
                                if (parent.nodeName.toLowerCase() == 'li' || parent.nodeName.toLowerCase() == 'p') {
                                    return true;
                                } else if (parent.nodeName.toLowerCase() == 'div') {
                                    parent = false;
                                } else {
                                    parent = parent.parentNode;
                                }
                            }

                            // Otherwise append p and block normal behavior
                            range.deleteContents();
                            range.insertNode(element);
                            range.selectNodeContents(element);
                            range.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(range);
                            return false;
                        }
                    }

                    // Block line return on strings
                    else {
                        return false;
                    }
                }
            })

            // Sanitize Paste
            .on('paste', function () {
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
                }, 0);
            })

            .on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
    };
});