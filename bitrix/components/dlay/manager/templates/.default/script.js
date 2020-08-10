$(function () {

    // Change Stock
    $(".js-change-stock").change(function () {
        let val = $(this).val();
        let parent = $(this).parents(".mng-item");
        if (val == "no") {
            parent.addClass("not-available");
            parent.find(".change-field").slideUp();
            parent.removeClass("padding-bottom");
        } else {
            parent.removeClass("not-available");
            if (val == "change") {
                parent.addClass("padding-bottom");
                parent.find(".change-field").slideDown();
            }
            if (val == "available") {
                parent.find(".change-field").slideUp();
                parent.removeClass("padding-bottom");
            }
        }
    });


    // Checked checkbox
    $(".mng-item.head .mng-check input").change(function () {
        let inputs = $(".mng-check input");
        if ($(this).prop("checked")) inputs.prop("checked", true);
        else inputs.prop("checked", false);
    });

    // Checked all
    $(".js-change-stock-all").change(function () {
        let value = $(this).val();
        let items = $(".mng-check input:checked").parents(".mng-item");
        items.find(".mng-stock select option[value='" + value + "']").prop("selected", true);
        $(".js-change-stock").change();
    });

    $(".js-change-stock").change();

});