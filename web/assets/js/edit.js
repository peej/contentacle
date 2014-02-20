$(function () {

    function getSelectionStart() {
        var node = document.getSelection().anchorNode,
            startNode = (node && node.nodeType === 3 ? node.parentNode : node);
        return startNode;
    }

    function htmlEncode(value) {
        return $('<div/>').text(value).html().replace(/\n&gt;/g, "\n>");
    }

    function htmlDecode(value) {
      return $('<div/>').html(value).text();
    }

    var updateTimeout = null;

    $("#edit").bind("keyup", function (e) {
        var textarea = this;

        window.clearTimeout(updateTimeout);
        updateTimeout = window.setTimeout(function () {
            $("#preview").html(marked(htmlEncode(textarea.value)));
        }, 1000);
    });

    $("#preview").attr("contenteditable", true).bind("keyup", function (e) {

        var nodes = this,
            node = getSelectionStart(),
            tagName;

        if (node && node.getAttribute('data-medium-element') && node.children.length === 0) {
            document.execCommand('formatBlock', false, 'p');
        }
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
            $("#edit").val(htmlDecode(toMarkdown(nodes.innerHTML.replace(/&nbsp;/g, " "))));
        }, 1000);
        
    });

    $("#preview").html(marked(htmlEncode($("#edit").val())));

    $("#edit, #preview").height($(window).height() - 130);

    $("#edit").bind("scroll", function () {
        var $textarea = $(this);
        if ($textarea.is(":focus")) {
            var $preview = $("#preview");
            var height = $textarea.outerHeight();
            var scrollTop = $textarea[0].scrollTop;
            var ratio = scrollTop / ($textarea[0].scrollHeight - height);
            $preview[0].scrollTop = ($preview[0].scrollHeight - height) * ratio;
        }
    });

    $("#preview").bind("scroll", function () {
        var $preview = $(this);
        if ($preview.is(":focus")) {
            var $textarea = $("#edit");
            var height = $textarea.outerHeight();
            var scrollTop = $preview[0].scrollTop;
            var ratio = scrollTop / ($preview[0].scrollHeight - height);
            $textarea[0].scrollTop = ($textarea[0].scrollHeight - height) * ratio;
        }
    });

});