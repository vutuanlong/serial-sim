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
                var select = $("<select><option value=''>--Hãy chọn--</option>");
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
        row.find(".btn-save-so-web").show();

		row.find( 'td[data-field="serial_sim"]' ).find('select').select2();
    });

	$(".btn-save").on("click", function(){
        var row = $(this).closest("tr");
        var post_id = $(this).data("id");

		var nha_mang = row.find("td[data-field='nha_mang']").find('select').val(),
            loai_sim = row.find("td[data-field='loai_sim']").find('select').val(),
            dinh_dang_sim = row.find("td[data-field='dinh_dang_sim']").find('select').val(),
            tinh_trang_ban = row.find("td[data-field='tinh_trang_ban']").find('select').val(),
			serial_sim = row.find("td[data-field='serial_sim']").find('select').val();

        var data = {
            action: "save_so_tmdt_inline",
            post_id: post_id,
            sdt: row.find("td[data-field='sdt']").text(),
            sdt_chamdinhdang: row.find("td[data-field='sdt_chamdinhdang']").text(),
            dinh_dang_sim: dinh_dang_sim,
            nha_mang: nha_mang,
            loai_sim: loai_sim,
            coc_sim: row.find("td[data-field='coc_sim']").text(),
            serial_sim: serial_sim,
            cam_ket: row.find("td[data-field='cam_ket']").text(),
            goi_cuoc: row.find("td[data-field='goi_cuoc']").text(),
            kenh_ban: row.find("td[data-field='kenh_ban']").text(),
            tinh_trang_ban: tinh_trang_ban,
            ma_don_hang: row.find("td[data-field='ma_don_hang']").text(),
            ghi_chu: row.find("td[data-field='ghi_chu']").text(),
        };

        $.post(ajax_object.ajax_url, data, function(res){
            alert(res.data.message);
            row.find("td.editable").removeAttr("contenteditable");
            row.find(".btn-save").hide();
            row.find(".btn-edit").show();

			row.find('td[data-field="nha_mang"]').html(nha_mang);
			row.find('td[data-field="loai_sim"]').html(loai_sim);
			row.find('td[data-field="dinh_dang_sim"]').html(dinh_dang_sim);
			row.find('td[data-field="serial_sim"]').html(serial_sim);
			row.find('td[data-field="tinh_trang_ban"]').html(tinh_trang_ban);
        });
    });

	$(".btn-save-so-web").on("click", function(){
        var row = $(this).closest("tr");
        var post_id = $(this).data("id");

		var id_kho = row.find("td[data-field='id_kho']").find('select').val(),
            loai_sim = row.find("td[data-field='loai_sim']").find('select').val();

        var data = {
            action: "save_so_web_inline",
            post_id: post_id,
            sdt: row.find("td[data-field='sdt']").text(),
            sdt_chamdinhdang: row.find("td[data-field='sdt_chamdinhdang']").text(),
            id_kho: id_kho,
            coc_sim: row.find("td[data-field='coc_sim']").text(),
            gia_ban_le: row.find("td[data-field='gia_ban_le']").text(),
            gia_dai_ly: row.find("td[data-field='gia_dai_ly']").text(),
            loai_sim: loai_sim,
            cam_ket: row.find("td[data-field='cam_ket']").text(),
            kenh_ban: row.find("td[data-field='kenh_ban']").text(),
            ngay_ban: row.find("td[data-field='ngay_ban']").text(),
            tinh_trang_ban: row.find("td[data-field='tinh_trang_ban']").text(),
            ghi_chu: row.find("td[data-field='ghi_chu']").text(),
        };

        $.post(ajax_object.ajax_url, data, function(res){
            alert(res.data.message);
            row.find("td.editable").removeAttr("contenteditable");
            row.find(".btn-save-so-web").hide();
            row.find(".btn-edit").show();

			row.find('td[data-field="id_kho"]').html(id_kho);
			row.find('td[data-field="loai_sim"]').html(loai_sim);
        });
    });

	$(".filter-select").on("change", function(){
        let params = new URLSearchParams(window.location.search);
        params.set($(this).attr("name"), $(this).val());
        window.location.search = params.toString();
    });
} )