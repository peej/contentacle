$(function () {

    var $body = $("body"),
        $section = $("body > section"),
        $edit = $("#edit"),
        $preview = $("#preview"),
        $commit = $("#commit"),
        updateTimeout = null,
        scrollTimeout = null,
        currentScroller = null;

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

    $edit.bind("keyup", function (e) {
        var textarea = this;

        window.clearTimeout(updateTimeout);
        updateTimeout = window.setTimeout(function () {
            $preview.html(marked(htmlEncode(textarea.value)));
        }, 300);
    });

    $preview.attr("contenteditable", true).bind("keyup", function (e) {

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
            $edit.val(htmlDecode(toMarkdown(nodes.innerHTML.replace(/&nbsp;/g, " "))));
        }, 300);
        
    });

    $preview.html(marked(htmlEncode($edit.val())));

    function resize() {
        $section.width($(document).width() - 2);
        $edit.height($(window).height() - $edit.position().top - 40);
        $preview.height($(window).height() - $preview.position().top);
        $commit.height($(window).height());
    }
    $body.css("overflow", "hidden");
    $(window).bind("resize", resize).load(resize);

    $edit.bind("scroll", function () {
        if (!currentScroller || currentScroller == 'edit') {
            currentScroller = 'edit';
            window.clearTimeout(scrollTimeout);
            scrollTimeout = window.setTimeout(function () {
                currentScroller = null;
            }, 100);

            var height = $edit.outerHeight(),
                scrollTop = $edit[0].scrollTop,
                ratio = scrollTop / ($edit[0].scrollHeight - height);

            $preview[0].scrollTop = ($preview[0].scrollHeight - height) * ratio;
        }
    });

    $preview.bind("scroll", function () {
        if (!currentScroller || currentScroller == 'preview') {
            currentScroller = 'preview';
            window.clearTimeout(scrollTimeout);
            scrollTimeout = window.setTimeout(function () {
                currentScroller = null;
            }, 100);

            var height = $edit.outerHeight(),
                scrollTop = $preview[0].scrollTop,
                ratio = scrollTop / ($preview[0].scrollHeight - height);

            $edit[0].scrollTop = ($edit[0].scrollHeight - height) * ratio;
        }
    });

    $edit.detach();
    $preview.detach();
    $section.append($edit).append($preview).append("<div id=\"commit-toggle\"></div>");
    $body.prepend($("#commit-form").detach());

    $commit.hover(function () {
        $body.addClass("commit");
    }, function () {
        $body.removeClass("commit");
    });

    $("#commit-toggle").click(function () {
        $body.addClass("commit");
    });

});