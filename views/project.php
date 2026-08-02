<ul class="da_list-01">
    <li><a href="<?php echo admin_url( 'admin.php?page=project' );?>">Danh sách</a></li>
    <li><a href="<?php echo admin_url( 'admin.php?page=project&action=add' );?>">Thêm</a></li>
</ul>
<?php 
global $wpdb;
$table_project = $wpdb->prefix . 'wm_project';
$table_company = $wpdb->prefix . 'wm_company';

function get_company_list() {
    global $wpdb;
$table_project = $wpdb->prefix . 'wm_project';
$table_company = $wpdb->prefix . 'wm_company';
    // 1. Tên bảng

    // 2. Viết câu lệnh SQL lấy toàn bộ dữ liệu (sắp xếp theo ID giảm dần)
    $query = "SELECT * FROM {$table_company} ORDER BY id DESC";

    // 3. Thực thi lấy dữ liệu dạng Object
    $results = $wpdb->get_results( $query );

    // 4. Kiểm tra và trả về dữ liệu
    if ( ! empty( $results ) ) {
        return $results;
    }

    return array(); // Trả về mảng rỗng nếu không có dữ liệu
}
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

if($action == 'add'){

    if(isset($_POST['save'])){
        $in_company = $_POST['in_company'];
        $in_project_code = $_POST['in_project_code'];
        $in_project_name = $_POST['in_project_name'];
        $in_project_sku = $_POST['in_project_sku'];
        $in_project_urltest = $_POST['in_project_urltest'];
        $in_project_user = $_POST['in_project_user'];
        $in_project_pass = $_POST['in_project_pass'];
        
        $check_code = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_project} WHERE wm_pj_code = %s",
                $in_project_code
            )
        );
        $data = array(
            'id_company'       => $in_company,
            'wm_pj_code'       => $in_project_code,
            'wm_pj_name'       => $in_project_name,
            'project_sku'       => $in_project_sku,
            'project_urltest'   => $in_project_urltest,
            'project_user'   => $in_project_user,
            'project_pass'   => $in_project_pass  
            //'created_at' => current_time('mysql') // Lấy thời gian hiện tại theo múi giờ WP
        );

        $format = array(
            '%s', 
            '%d', 
            '%s', 
            '%s', 
            '%s', 
            '%s', // name là chuỗi
        );

        if ( $check_code > 0 ) {
        // Thông báo nếu mã dự án đã tồn tại
        echo '<div class="notice notice-error"><p>Mã dự án ('.$in_project_code.') này đã tồn tại. Vui lòng nhập mã khác!</p></div>';
    } else {
        $result = $wpdb->insert( $table_project, $data, $format );
        if ( false === $result ) {
            // Lỗi khi thêm dữ liệu
            error_log( 'Lỗi thêm dữ liệu: ' . $wpdb->last_error );
            // return false;
            echo 'Lỗi khi thêm dữ liệu';
        } else {
            $redirect_url = admin_url( 'admin.php?page=project' );
            echo '<script type="text/javascript">';
            echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
            echo '</script>';
        }
    }
    }
     ?>
<form action="#" method="post">
    <table>
        <tr>
            <td>Company<br>
                <select name="in_company">
                    <?php foreach(get_company_list() as $item){
                        echo '<option value="'.$item->id.'">'.$item->wm_cp_name.'</option>';
                    } ?>
                    
                </select>
            </td>
            <td>
                Project code<br>
                <input type="text" name="in_project_code">
            </td>
            <td>Name<br><input type="text" name="in_project_name"></td>
            <td>SKU<br><input type="text" name="in_project_sku"></td>
        </tr>

        <tr>
            <td>URL test<br><input type="text" name="in_project_urltest" ></td>
            <td>User<br><input type="text" name="in_project_user" ></td>
            <td>Pass<br><input type="text" name="in_project_pass"></td>
        </tr>
        <tr>
            <td><button type="submit" name="save" class="da_btn-01">Lưu</button></td>
        </tr>
    </table>
</form>
<?php }
elseif($action=="edit"){
    $id=$_GET['id'];
    if(isset($_POST['save'])){
        $in_company = $_POST['in_company'];
        $in_project_code = $_POST['in_project_code'];
        $in_project_name = $_POST['in_project_name'];
        $in_project_sku = $_POST['in_project_sku'];
        $in_project_urltest = $_POST['in_project_urltest'];
        $in_project_user = $_POST['in_project_user'];
        $in_project_pass = $_POST['in_project_pass'];
        $in_project_urlhost = $_POST['in_project_urlhost'];
        
        $table_name = $wpdb->prefix . 'project';
        $data = array(
            'id_company'       => $in_company,
            'wm_pj_code'       => $in_project_code,
            'wm_pj_name'       => $in_project_name,
            'project_sku'       => $in_project_sku,
            'project_urltest'   => $in_project_urltest,
            'project_user'   => $in_project_user,
            'project_pass'   => $in_project_pass,
            'project_urlhost'=>$in_project_urlhost
        );

         $where = array(
            'id' => $id
        );

        $format = array(
            '%s', 
            '%d', 
            '%s',
            '%s',
            '%s', 
            '%s', 
            '%s',
            '%s', // name là chuỗi
        );
        $where_format = array( '%d' );
        $result = $wpdb->update( $table_project, $data, $where, $format, $where_format );
        if ( false === $result ) {
            // Lỗi khi thêm dữ liệu
            error_log( 'Lỗi thêm dữ liệu: ' . $wpdb->last_error );
            return false;
        } else {
            $redirect_url = admin_url( 'admin.php?page=project' );
            echo '<script type="text/javascript">';
            echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
            echo '</script>';
        }
    }
    $project = $wpdb->get_row( 
        $wpdb->prepare( "SELECT * FROM $table_project WHERE id = %d", $id ) 
    );
    ?>
    <form action="#" method="post">
    <table>
        <tr>
            <td>Company <?php echo $project->id_company?><br>
                <select name="in_company">
                    <?php foreach(get_company_list() as $item){
                        if($item->id==$project->id_company){
                            echo '<option selected value="'.$item->id.'">'.$item->wm_cp_name.'</option>';    
                        }else{
                            echo '<option value="'.$item->id.'">'.$item->wm_cp_name.'</option>';
                        }
                        
                    } ?>
                    
                </select>
            </td>
            <td>
                Project code<br>
                <input type="text" name="in_project_code" value="<?php echo $project->wm_pj_code ?>">
            </td>
            <td>Name<br><input type="text" name="in_project_name" value="<?php echo $project->wm_pj_name ?>"></td>
            <td>SKU<br><input type="text" name="in_project_sku" value="<?php echo $project->project_sku ?>"></td>
            
        </tr>
        <tr>
            <td>URL test<br><input type="text" name="in_project_urltest" value="<?php echo $project->project_urltest ?>"></td>
            <td>User<br><input type="text" name="in_project_user" value="<?php echo $project->project_user ?>"></td>
            <td>Pass<br><input type="text" name="in_project_pass" value="<?php echo $project->project_pass ?>"></td>
        </tr>
        <tr>
            <td>Url host<br><input type="text" name="in_project_urlhost" value="<?php echo $project->project_urlhost ?>"></td>
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
    $result = $wpdb->delete( $table_project, $where, $where_format );

    // 5. Kiểm tra kết quả
    if ( false === $result ) {
        // Lỗi truy vấn SQL
        error_log( 'Lỗi Xóa DB: ' . $wpdb->last_error );
        return false;
    } else {
        // Trả về số dòng đã bị xóa (VD: 1 nếu xóa thành công, 0 nếu không tìm thấy ID để xóa)
        $redirect_url = admin_url( 'admin.php?page=project' );
        echo '<script type="text/javascript">';
        echo 'window.location.href = "' . esc_url( $redirect_url ) . '";';
        echo '</script>';
        exit;
    }
}
else{
    
    $project_query = "SELECT a.id, a.wm_pj_code, a.project_sku,a.wm_pj_name, b.wm_cp_name FROM $table_project a, $table_company b WHERE b.id=a.id_company ORDER BY a.wm_pj_code DESC";

    // 3. Thực thi lấy dữ liệu dạng Object
    $project_list = $wpdb->get_results( $project_query );?>
    <table class="da_table-01">
        <tr>
            <td>STT</td><td>NAME</td><td>SKU</td><td></td>
        </tr>
        <?php
        $i=0;
        foreach($project_list as $item){
            $i++;
            //
            echo '<tr>
            <td>'.$i.'</td><td>【'.$item->wm_cp_name.'】'.$item->wm_pj_code.' - '.$item->wm_pj_name.'</td><td>'.$item->project_sku.'</td><td><a href="'.admin_url( 'admin.php?page=project&action=edit&id='.$item->id ).'">Edit</a> - <a href="'.admin_url( 'admin.php?page=project&action=del&id='.$item->id ).'">Del</a></td>
        </tr>';
        }
        ?>
    </table>
    <?php   
}
?>