<ul class="da_list-01">
    <li><a href="<?php echo admin_url( 'admin.php?page=baocao' );?>">Danh sách</a></li>
    <li><a href="<?php echo admin_url( 'admin.php?page=baocao&action=add' );?>">Thêm</a></li>
</ul>
<?php 

global $wpdb;
$table_project = $wpdb->prefix . 'wm_project';
$table_company = $wpdb->prefix . 'wm_company';
$table_baocao = $wpdb->prefix . 'baocao';
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
if($action == 'add'){
    if(isset($_POST['save'])){
        $in_project_code = $_POST['in_project_code'];
        $in_bc_date = $_POST['in_bc_date'];
        $in_bc_content = $_POST['in_bc_content'];
        $in_option = $_POST['in_option'];

        $info_product = $wpdb->get_var( 
            $wpdb->prepare( "SELECT id FROM {$table_project} WHERE wm_pj_code = %s", $in_project_code ) 
        );

        $data = array(
            'id_project'       => $info_product,
            'bc_date'       => $in_bc_date,
            'bc_content'       => $in_bc_content,
            'bc_option'     =>$in_option
            //'created_at' => current_time('mysql') // Lấy thời gian hiện tại theo múi giờ WP
        );

        $format = array(
            '%s', 
            '%s', 
            '%s', // name là chuỗi
            '%d', 
        );
        $result = $wpdb->insert( $table_baocao, $data, $format );
        if ( false === $result ) {
            // Lỗi khi thêm dữ liệu
            error_log( 'Lỗi thêm dữ liệu: ' . $wpdb->last_error );
            return false;
        } else {
            $redirect_url = admin_url( 'admin.php?page=baocao' );
            echo '<script type="text/javascript">';
            echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
            echo '</script>';
            // exit;
        }
    }

    $content="Câu chào
Tên dự án

Basic:
User: gkv
Pass: gkvgkv

Báo cáo";
     ?>
<form action="#" method="post">
    <table class="da_table-01">
        <tr>
            <td>
                Project code<br>
                <input type="text" class="val_code" name="in_project_code">
            </td>
            <td>
                <label><input type="radio" class="c_option" name="in_option" value="1">Thêm mới</label>
                <label><input type="radio" class="c_option" name="in_option" value="2">Chỉnh sửa</label>
                <label><input type="radio" class="c_option" name="in_option" value="3">Uphost</label>
            </td>
            <td>
                Date<br>
                <input type="date" name="in_bc_date" value="<?php echo date('Y-m-d');?>">
            </td>
        </tr>
        <tr>
            <td class="val_project"></td>
        </tr>
        <tr>
            <td colspan="3">Content<br><textarea type="text" name="in_bc_content" class="c_content"><?php echo $content;?></textarea></td>
        </tr>
        <tr>
            <td><button type="submit" name="save" class="da_btn-01">Lưu</button></td>
        </tr>
    </table>
</form>
<?php }
elseif($action=='edit'){
    $id=$_GET['id'];

    if(isset($_POST['save'])){
        $in_project_code = $_POST['in_project_code'];
        $in_bc_date = $_POST['in_bc_date'];
        $in_bc_content = $_POST['in_bc_content'];
        $in_option = $_POST['in_option'];

        $info_product = $wpdb->get_var( 
            $wpdb->prepare( "SELECT id FROM {$table_project} WHERE wm_pj_code = %s", $in_project_code ) 
        );

        $data = array(
            'id_project' => $info_product,
            'bc_date' => $in_bc_date,
            'bc_content' => $in_bc_content,
            'bc_option' => $in_option
        );

        // 3. Điều kiện WHERE (Sửa dòng có id bằng $id)
        $where = array(
            'id' => $id
        );

        // 4. Format kiểu dữ liệu (%s cho chuỗi name, %d cho số nguyên id)
        $format       = array( '%d','%s','%s','%d' );
        $where_format = array( '%d' );

        // 5. Thực thi Cập nhật
        $result = $wpdb->update( $table_baocao, $data, $where, $format, $where_format );

        // 6. Kiểm tra kết quả
        if ( false === $result ) {
            // Lỗi truy vấn SQL
            error_log( 'Lỗi Update DB: ' . $wpdb->last_error );
            return false;
        } else {
            $redirect_url = admin_url( 'admin.php?page=baocao' );
            echo '<script type="text/javascript">';
            echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
            echo '</script>';
            // exit;
        }
    }

    $project = $wpdb->get_row( 
        $wpdb->prepare( "SELECT * FROM $table_project a, $table_company b, $table_baocao c WHERE a.id_company = b.id AND c.id_project = a.id AND c.id = %d", $id ) 
    );
    $content = $project->bc_content;
    ?>
<form action="#" method="post">
    <table class="da_table-01">
        <tr>
            <td>
                Project code<br>
                <input type="text" class="val_code" name="in_project_code" value="<?php echo $project->wm_pj_code; ?>">
            </td>
            <td>
                <?php 
                $option = $project->bc_option;
                ?>
                <label><input type="radio" name="in_option" value="1" <?php checked( $option, 1 ); ?>>Thêm mới</label>
                <label><input type="radio" name="in_option" value="2" <?php checked( $option, 2 ); ?>>Chỉnh sửa</label>
                <label><input type="radio" name="in_option" value="3" <?php checked( $option, 3 ); ?>>Uphost</label>
            </td>
            <td>
                Date<br>
                <input type="date" name="in_bc_date" value="<?php echo $project->bc_date ?? date('Y-m-d');?>">
            </td>
        </tr>
        <tr>
            <td class="val_project"><?php echo '【'.$project->wm_cp_name.'】'.$project->wm_pj_code.' - '.$project->wm_pj_name; ?></td>
        </tr>
        <tr>
            <td colspan="3">Content<br><textarea type="text" name="in_bc_content"><?php echo $content;?></textarea></td>
        </tr>
        <tr>
            <td><button type="submit" name="save" class="da_btn-01">Lưu</button></td>
        </tr>
    </table>
</form>
<?php }
elseif($action=='del'){
    $id=$_GET['id'];

    // 2. Điều kiện WHERE (Xóa dòng có id = $id)
    $where = array(
        'id' => $id
    );

    // 3. Format kiểu dữ liệu (%d cho số nguyên ID)
    $where_format = array( '%d' );

    // 4. Thực thi Xóa
    $result = $wpdb->delete( $table_baocao, $where, $where_format );

    // 5. Kiểm tra kết quả
    if ( false === $result ) {
        // Lỗi truy vấn SQL
        error_log( 'Lỗi Xóa DB: ' . $wpdb->last_error );
        return false;
    } else {
        // Trả về số dòng đã bị xóa (VD: 1 nếu xóa thành công, 0 nếu không tìm thấy ID để xóa)
        $redirect_url = admin_url( 'admin.php?page=baocao' );
        echo '<script type="text/javascript">';
        echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
        echo '</script>';
        exit;
    }
}
else{
    $project_query = "SELECT * FROM $table_project a, $table_company b, $table_baocao c WHERE b.id=a.id_company and c.id_project = a.id  ORDER BY c.id DESC";

    // 3. Thực thi lấy dữ liệu dạng Object
    $project_list = $wpdb->get_results( $project_query );?>
    <table class="da_table-01">
        <tr>
            <td>STT</td><td>NAME</td><td>DATE</td><td></td>
        </tr>
        <?php
        $i=0;
        foreach($project_list as $item){
            $i++;
            echo '<tr>
            <td>'.$i.'</td><td>【'.$item->wm_cp_name.'】'.$item->wm_pj_code.' - '.$item->wm_pj_name.'</td><td>'.$item->bc_date.'</td><td><a href="'.admin_url( 'admin.php?page=baocao&action=edit&id='.$item->id ).'">Edit</a> - <a href="'.admin_url( 'admin.php?page=baocao&action=del&id='.$item->id ).'">Del</a></td>
        </tr>';
        }
        ?>
    </table>
    <?php   
}

?>