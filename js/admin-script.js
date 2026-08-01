jQuery(document).ready(function($) {
    $(document).on('change', '.val_code', function () {
        var projectCode = $(this).val();
        var $targetCell = $(this).closest('tr').find('.val_project');

        // Bật F12 -> Console để xem giá trị nhập vào
        console.log('Mã nhập vào:', projectCode);

        if (projectCode === '') {
            $targetCell.html('');
            return;
        }

        $targetCell.html('<em>Đang tải...</em>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_project_info',
                code: projectCode
            },
           success: function (response) {
                console.log('Dữ liệu từ PHP:', response);

                if (response.success) {
                    // 1. Tìm trong cùng hàng <tr> trước
                    var $targetCell = $(this).closest('tr').find('.val_project');

                    // 2. Nếu không tìm thấy trong cùng <tr>, chọn trực tiếp class .val_project trên toàn trang
                    if ($targetCell.length === 0) {
                        $targetCell = $('.val_project');
                    }

                    // 3. In kết quả ra HTML
                    $targetCell.html(response.data);
                } else {
                    $('.val_project').html('<span style="color:red;">' + response.data + '</span>');
                }
            }.bind(this) 
        });
    });

    $(document).on('change', '.c_option', function() {
        var selectedOption = $(this).val(); // Giá trị 1, 2, hoặc 3
        var projectCode    = $('.val_code').val();
        var $contentArea = $('.c_content');  // Thẻ textarea

        // Lưu lại tham chiếu đến textarea
        $contentArea.val('Đang tải nội dung...');

        $.ajax({
            url: ajaxurl, // Biến mặc định sẵn có trong wp-admin
            type: 'POST',
            data: {
                action: 'get_content_by_option', // Tên action hook PHP
                option_val: selectedOption,
                project_code: projectCode
            },
            success: function(response) {
                if (response.success) {
                    // Cập nhật giá trị vào textarea
                    $contentArea.val(response.data);
                } else {
                    $contentArea.val('');
                    alert(response.data);
                }
            },
            error: function() {
                $contentArea.val('');
                alert('Có lỗi xảy ra khi kết nối server!');
            }
        });
    });
});