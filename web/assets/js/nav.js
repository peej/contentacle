$(function () {

    $("#nav-button").click(function () {
        $("body").toggleClass("show-nav");
    });

    $("#props-button").click(function () {
        $("body").toggleClass("show-props");
    });

    $("#nav-overlay, #props-overlay").click(function () {
        $("body").removeClass("show-nav show-props");
    });

});