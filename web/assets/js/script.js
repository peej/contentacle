$(function () {

    function getSelectionStart() {
        var node = document.getSelection().anchorNode,
            startNode = (node && node.nodeType === 3 ? node.parentNode : node);
        return startNode;
    }

    var updateTimeout = null;

    $("#nav-button").click(function () {
        $("body").addClass("show-nav");
    });

    $("#nav-overlay").click(function () {
        $("body").removeClass("show-nav");
    });

    $("#editable > textarea").bind("keyup", function (e) {
        var textarea = this;

        window.clearTimeout(updateTimeout);
        updateTimeout = window.setTimeout(function () {
            $("#editable > div").html(marked(textarea.value));
        }, 1000);
    });

    $("#editable > div").attr("contenteditable", true).bind("keyup", function (e) {

        var nodes = this,
            node = getSelectionStart(),
            tagName;
        if (node && node.getAttribute('data-medium-element') && node.children.length === 0) {
            document.execCommand('formatBlock', false, 'p');
        }
        console.log(e);
        if (e.which === 13 && !e.shiftKey) {
            tagName = node.tagName.toLowerCase();
            if (tagName !== 'li') {
                document.execCommand('formatBlock', false, 'p');
                if (tagName === 'a') {
                    document.execCommand('unlink', false, null);
                }
            }
        }

        window.clearTimeout(updateTimeout);
        updateTimeout = window.setTimeout(function () {
            $("#editable > textarea").val(toMarkdown(nodes.innerHTML));
        }, 1000);
        
    });

});