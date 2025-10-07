jQuery( function($) {
    $(".btn-edit").on("click", function(){
        var row = $(this).closest("tr");

        row.find("td.editable").each(function(){
            var td = $(this);
            var type = td.data("type");
            var val = td.text().trim();

            if(type === "select"){
                var options = td.data("options");
                if(typeof options === "string"){
                    options = JSON.parse(options);
                }
                var select = $("<select>");
                $.each(options, function(i, opt){
                    var option = $("<option>").val(opt).text(opt);
                    if(opt === val) option.attr("selected", "selected");
                    select.append(option);
                });
                td.data("old", val);
                td.html(select);
            } else {
                td.attr("contenteditable", "true").data("old", val);
            }
        });

        $(this).hide();
        row.find(".btn-save").show();

		row.find( 'td[data-field="serial_sim"]' ).find('select').select2();
    });

	$(".btn-save").on("click", function(){
        var row = $(this).closest("tr");
        var post_id = $(this).data("id");

		var nha_mang = row.find("td[data-field='nha_mang']").find('select').val(),
            loai_sim = row.find("td[data-field='loai_sim']").find('select').val(),
			serial_sim = row.find("td[data-field='serial_sim']").find('select').val();

        var data = {
            action: "save_so_tmdt_inline",
            post_id: post_id,
            sdt: row.find("td[data-field='sdt']").text(),
            sdt_chamdinhdang: row.find("td[data-field='sdt_chamdinhdang']").text(),
            dinh_dang_sim: row.find("td[data-field='dinh_dang_sim']").text(),
            nha_mang: nha_mang,
            loai_sim: loai_sim,
            serial_sim: serial_sim,
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

			row.find('td[data-field="nha_mang"]').html(nha_mang);
			row.find('td[data-field="loai_sim"]').html(loai_sim);
			row.find('td[data-field="serial_sim"]').html(serial_sim);
        });
    });
} )