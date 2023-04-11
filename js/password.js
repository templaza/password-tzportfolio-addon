(function ($, TZ_Portfolio_Plus) {
    "use strict";

    $.tzPortfolioPlusAddOnPassword  = function(el, options){
        var addonPassword = $.tzPortfolioPlusAddOnPassword,
            $el	= $(el),
            settings = $.extend(true,$.extend(true,{},$.tzPortfolioPlusAddOnPassword.defaults),options);
        // console.log(el);
        // console.log($el);
        $el.itemClick = function(obj){
            obj.off("click").on("click", function(e){
                e.preventDefault();

                var $item = $(this);
                var clicked = $item.data("tzPortfolioPlusAddOnPassword.clicked");
                if(clicked) {
                    return;
                }

                // clicked = true;
                $item.data("tzPortfolioPlusAddOnPassword.clicked", true);


                alert("test");
                // $el.processVote($item);
            });
        };

        $el.itemClick($el);


        // Call click method when ajaxloaded
        if(typeof TZ_Portfolio_Plus.infiniteScroll !== undefined) {
            if(typeof TZ_Portfolio_Plus.infiniteScroll.addAjaxComplete !== "undefined") {
                TZ_Portfolio_Plus.infiniteScroll.addAjaxComplete(function (newElements, masonryContainer) {
                    // alert("addAjaxComplete");
                    var $container = newElements.find($el.clone().get(0));
                    // console.log($el);
                    console.log(el);
                    console.log($el.clone().get(0));
                    console.log($container);

                    // $container.tzPortfolioPlusAddOnVote(settings);
                    // // Call back scroll ajax
                    // settings.ajaxScrollComplete($container, newElements, masonryContainer);
                });
            }
        }
    };

    $.tzPortfolioPlusAddOnPassword.defaults	= {};

    $.fn.tzPortfolioPlusAddOnPassword = function (options) {
        if (options === undefined) options = {};
        if (typeof options === "object") {
            // Call function
            return this.each(function(index, value) {
                // Call function
                if ($(this).data("tzPortfolioPlusAddOnPassword") === undefined) {
                    new $.tzPortfolioPlusAddOnPassword(this, options);
                }else{
                    $(this).data('tzPortfolioPlusAddOnPassword');
                }
            });
        }
    };
})(jQuery, TZ_Portfolio_Plus);