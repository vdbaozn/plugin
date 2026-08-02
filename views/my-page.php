<ul class="da_list-01">
    <li><a href="<?php echo get_bloginfo('url') ?>/wp-admin/admin.php?page=congty">Danh sách</a></li>
    <li><a href="<?php echo get_bloginfo('url') ?>/wp-admin/admin.php?page=congty&action=add">Thêm</a></li>
</ul><?php 
global $wpdb;
$table_project = $wpdb->prefix . 'wm_project';
$table_company = $wpdb->prefix . 'wm_company';
$table_baocao = $wpdb->prefix . 'baocao';
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';


if($action=='add'){
    if(isset($_POST['save'])){
        $in_name = $_POST['in_name'];
        $data = array(
            'wm_cp_name'       => $in_name
        );

        $format = array(
            '%s', // name là chuỗi
        );
        $result = $wpdb->insert( $table_company, $data, $format );
        if ( false === $result ) {
            // exit;
            // Lỗi khi thêm dữ liệu
            error_log( 'Lỗi thêm dữ liệu: ' . $wpdb->last_error );
            // return false;
            echo 'Lỗi khi thêm dữ liệu';
        } else {
            $redirect_url = admin_url( 'admin.php?page=congty' );
            echo '<script type="text/javascript">';
            echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
            echo '</script>';
            exit;
        }
    }

    ?>
    <form action="<?php echo get_bloginfo('url') ?>/wp-admin/admin.php?page=congty&action=add" method="post">
        <table>
            <tr>
                <td><input type="text" name="in_name"></td>
                <td><button type="submit" name="save" value="save">Lưu</button></td>
            </tr>
        </table>
    </form>
    <?php
}elseif($action=='edit'){
    $id=$_GET['id'];

    if(isset($_POST['save'])){
        $in_name = $_POST['in_name'];
        $data = array(
            'wm_cp_name'       => $in_name
        );

        $where = array(
            'id' => $id
        );

        $format = array(
            '%s', // name là chuỗi
        );
        $where_format = array( '%d' );
        // $result = $wpdb->insert( $table_company, $data, $format );
        $result = $wpdb->update( $table_company, $data, $where, $format, $where_format );
        if ( false === $result ) {
            exit;
            // Lỗi khi thêm dữ liệu
            error_log( 'Lỗi thêm dữ liệu: ' . $wpdb->last_error );
            return false;
        } else {
            // Trả về ID vừa được tạo tự động (Auto Increment ID)
            $redirect_url = admin_url( 'admin.php?page=congty' );
            // wp_redirect( $redirect_url );

            echo '<script type="text/javascript">';
            echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
            echo '</script>';
            exit;
        }
    }

    $congty = $wpdb->get_row( 
        $wpdb->prepare( "SELECT * FROM $table_company WHERE id = %d", $id ) 
    );?>
    <form action="#" method="post">
        <table>
            <tr>
                <td><input type="text" name="in_name" value="<?php echo $congty->wm_cp_name;?>"></td>
                <td><button type="submit" name="save" value="save">Lưu</button></td>
            </tr>
        </table>
    </form>
    <?php
}
elseif($action=='del'){
    $id=$_GET['id'];

    // 2. Điều kiện WHERE (Xóa dòng có id = $id)
    $where = array(
        'id' => $id
    );

    // 3. Format kiểu dữ liệu (%d cho số nguyên ID)
    $where_format = array( '%d' );

    // 4. Thực thi Xóa
    $result = $wpdb->delete( $table_company, $where, $where_format );

    // 5. Kiểm tra kết quả
    if ( false === $result ) {
        // Lỗi truy vấn SQL
        error_log( 'Lỗi Xóa DB: ' . $wpdb->last_error );
        return false;
    } else {
        // Trả về số dòng đã bị xóa (VD: 1 nếu xóa thành công, 0 nếu không tìm thấy ID để xóa)
        $redirect_url = admin_url( 'admin.php?page=congty' );
        echo '<script type="text/javascript">';
        echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
        echo '</script>';
        exit;
    }
}
else{
    $project_query = "SELECT * FROM $table_company  ORDER BY id DESC";

    // 3. Thực thi lấy dữ liệu dạng Object
    $project_list = $wpdb->get_results( $project_query );?>
    <table class="da_table-01">
        <tr>
            <td>STT</td><td>NAME</td><td></td>
        </tr>
        <?php
        $i=0;
        foreach($project_list as $item){
            $i++;
            echo '<tr>
            <td>'.$i.'</td><td>【'.$item->wm_cp_name.'】</td><td><a href="'.admin_url( 'admin.php?page=congty&action=edit&id='.$item->id ).'">Edit</a> - <a href="'.admin_url( 'admin.php?page=congty&action=del&id='.$item->id ).'">Del</a></td>
        </tr>';
        }
        ?>
    </table>
    <?php   
}
?>