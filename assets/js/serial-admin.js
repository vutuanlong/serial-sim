jQuery( function($) {
    $(".btn-edit").on("click", function(){
        var row = $(this).closest("tr");
        row.find("td.editable").attr("contenteditable", "true");
        $(this).hide();
        row.find(".btn-save").show();
    });

	$(".btn-save").on("click", function(){
        var row = $(this).closest("tr");
        var post_id = $(this).data("id");

        var data = {
            action: "save_serial_inline",
            post_id: post_id,
            ngay_nhap: row.find("td[data-field='ngay_nhap']").text(),
            serial_sim: row.find("td[data-field='serial_sim']").text(),
            sdt: row.find("td[data-field='sdt']").text(),
            sdt_chamdinhdang: row.find("td[data-field='sdt_chamdinhdang']").text(),
            dinh_dang_sim: row.find("td[data-field='dinh_dang_sim']").text(),
            nha_mang: row.find("td[data-field='nha_mang']").text(),
            loai_sim: row.find("td[data-field='loai_sim']").text(),
            cam_ket: row.find("td[data-field='cam_ket']").text(),
            goi_cuoc: row.find("td[data-field='goi_cuoc']").text(),
            kenh_ban: row.find("td[data-field='kenh_ban']").text(),
            tinh_trang_ban: row.find("td[data-field='tinh_trang_ban']").text(),
            ghi_chu: row.find("td[data-field='ghi_chu']").text(),
        };

        $.post(ajax_object.ajax_url, data, function(res){
            alert(res.data.message);
            row.find("td.editable").removeAttr("contenteditable");
            row.find(".btn-save").hide();
            row.find(".btn-edit").show();
        });
    });
} )