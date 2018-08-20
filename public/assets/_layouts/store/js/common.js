$(function () {
    // (列表页，全选checkbox)
    $("table tbody tr td input[type=checkbox]").click(function () { //点击复选框,切换样式
        //一般的复选框
        var checked = $(this).prop("checked");

        //列表中的复选框
        var len = $(this).parents("tbody").find("label.pos-rel input[type=checkbox]").length;
        var checkLen = $(this).parents("tbody").find("label.pos-rel input[type=checkbox]:checked").length;
        if (len == checkLen) { //判断是否选中
            $(this).parents("tbody").siblings("thead").find("input[type=checkbox]").prop("checked", true);
        } else {
            $(this).parents("tbody").siblings("thead").find("input[type=checkbox]").prop("checked", false);
        }
    });

    // (列表页，全选checkbox)
    $("table thead tr th input[type=checkbox]").click(function () { //判断选中状态,修改样式
        var checked = $(this).prop("checked");
        if (checked) {
            $(this).parents("thead").siblings("tbody").find("label.pos-rel input[type=checkbox]").prop("checked", true);
        } else {
            $(this).parents("thead").siblings("tbody").find("label.pos-rel input[type=checkbox]").prop("checked", false);
        }
    });
    // 清除a和button标签的焦点
    $(document).on('focus', 'a, button', function () {
        this.blur();
    });
})