define(['sanitize'], function (Sanitize) {
    return function (element) {
        $(element)

            // Use <br> instead of <div>
            .on("keypress", function (e) {
                if (e.which == 13) {
                    var selection = window.getSelection(),
                        range = selection.getRangeAt(0),
                        br = document.createElement("br");

                    // Allow <li>
                    var parent = range.startContainer;
                    while (parent && typeof parent.parentNode !== 'undefined') {
                        if (parent.nodeName.toLowerCase() == 'li') {
                            return true;
                        }

                        if (parent.nodeName.toLowerCase() == 'div') {
                            parent = false;
                        } else {
                            parent = parent.parentNode;
                        }
                    }

                    // Otherwise append <br> and block normal behavior
                    range.deleteContents();
                    range.insertNode(br);
                    range.setStartAfter(br);
                    range.setEndAfter(br);
                    selection.removeAllRanges();
                    selection.addRange(range);
                    return false;
                }
            })

            // Sanitize Paste
            .on('paste', function () {
                setTimeout(function () {
                    var s = new Sanitize({
                        elements: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'font', 'br', 'b', 'strong', 'u', 'i', 'a', 'ul', 'ol', 'li', 'img'],
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