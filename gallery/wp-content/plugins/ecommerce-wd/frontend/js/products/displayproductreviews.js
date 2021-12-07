jQuery(document).ready(function () {
    // star rater
    jQuery(".wd_shop_star_rater").each(function () {
        new WdBsStarRater(jQuery(this));
    });
});