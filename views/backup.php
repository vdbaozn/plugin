<?php
$current_page = sanitize_text_field( $_GET['page'] ?? 'export' );

    // URL Export
    $export_url = add_query_arg(
        array(
            'page'   => $current_page,
            'action' => 'export_company_xml'
        ),
        admin_url( 'admin.php' )
    );
    ?>

    <div class="wrap">
        <h1>Quản lý Nhập / Xuất Dữ liệu</h1>

        <!-- THÔNG BÁO THÀNH CÔNG -->
        <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'import_success' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Thành công!</strong> Dữ liệu từ file XML đã được nạp vào cơ sở dữ liệu.</p>
            </div>
        <?php endif; ?>

        <hr>

        <!-- 1. NÚT EXPORT -->
        <h2>1. Xuất dữ liệu (Export)</h2>
        <a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">
            📥 Xuất dữ liệu ra XML
        </a>

        <br><br><hr>

        <!-- 2. FORM IMPORT (Lưu ý phải có enctype="multipart/form-data") -->
        <h2>2. Nhập dữ liệu (Import)</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field( 'import_xml_nonce_action', 'import_xml_nonce_field' ); ?>

            <p>Chọn file XML đã xuất trước đó để nạp lại dữ liệu vào hệ thống:</p>
            <p>
                <input type="file" name="xml_file" accept=".xml" required />
            </p>
            <p>
                <input type="submit" name="submit_import_xml" class="button button-secondary" value="📤 Tải lên & Import XML" onclick="return confirm('Lưu ý: Dữ liệu trùng ID sẽ bị ghi đè. Bạn có chắc chắn muốn import?');">
            </p>
        </form>
    </div>

