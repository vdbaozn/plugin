<?php
/**
 * Plugin Name: Dự án
 * Plugin URI:  https://domain-cua-ban.com/my-first-plugin
 * Description: Đây là plugin đầu tiên của tôi giúp hiển thị một dòng thông báo.
 * Version:     1.1.1
 * Author:      Võ Duy Bảo
 * Author URI:  https://domain-cua-ban.com
 * License:     Vdbao
 */

// Ngăn chặn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter('pre_set_site_transient_update_plugins', 'check_my_plugin_update');

function check_my_plugin_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    // Link raw tới file info.json trên GitHub
    $json_url = 'https://raw.githubusercontent.com/vdbaozn/plugin/main/info.json';
    
    // Gọi lấy dữ liệu từ GitHub
    $response = wp_remote_get($json_url, array('timeout' => 10));
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return $transient;
    }

    $remote_info = json_decode(wp_remote_retrieve_body($response));
    
    // Lấy thông tin plugin hiện tại
    $plugin_file = plugin_basename(__FILE__); // VD: duan/duan.php
    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'];

    // So sánh phiên bản (Remote > Local)
    if ($remote_info && version_compare($current_version, $remote_info->version, '<')) {
        $obj = new stdClass();
        $obj->slug = plugin_basename(__FILE__); // Đường dẫn chính xác dạng folder/file.php
        $obj->plugin = plugin_basename(__FILE__);
        $obj->new_version = $remote_info->version;
        $obj->package = $remote_info->download_url; // Link tải file zip
        
        // Thêm các thông tin hiển thị (Tùy chọn)
        $obj->url = 'https://github.com/vdbaozn/plugin';
        
        $transient->response[$plugin_file] = $obj;
    }

    return $transient;
}

// Bổ sung: Hiển thị thông tin popup chi tiết bản cập nhật (khi bấm View version 5.1.2 details)
add_filter('plugins_api', 'my_plugin_popup_info', 20, 3);
function my_plugin_popup_info($res, $action, $args) {
    if ($action !== 'plugin_information') {
        return $res;
    }

    $plugin_file = plugin_basename(__FILE__);
    if (isset($args->slug) && $args->slug === $plugin_file) {
        $response = wp_remote_get('https://raw.githubusercontent.com/vdbaozn/plugin/main/info.json');
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $remote_info = json_decode(wp_remote_retrieve_body($response));
            $res = new stdClass();
            $res->name = $remote_info->name;
            $res->slug = $plugin_file;
            $res->version = $remote_info->version;
            $res->download_link = $remote_info->download_url;
            $res->sections = array(
                'description' => $remote_info->sections->description,
                'changelog' => $remote_info->sections->changelog
            );
            return $res;
        }
    }
    return $res;
}

//end update plugin

define( 'DUAN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DUAN_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, 'my_plugin_update_db_structure' );

function my_plugin_update_db_structure() {
    global $wpdb;
    $table_project = $wpdb->prefix . 'project';

    // 2. Kiểm tra xem cột 'project_urltest' đã có trong bảng chưa
    $column_exists = $wpdb->get_results( 
        $wpdb->prepare(
            "SHOW COLUMNS FROM `{$table_project}` LIKE %s", 
            'project_urltest' 
        ) 
    );

    // 3. Nếu CHƯA CÓ thì mới tiến hành chèn các cột mới vào
    if ( empty( $column_exists ) ) {
        // Gộp cả 4 cột vào 1 truy vấn ALTER TABLE duy nhất
        $sql = "ALTER TABLE `{$table_project}` 
                ADD `project_urltest` VARCHAR(100) NOT NULL AFTER `project_sku`,
                ADD `project_user` VARCHAR(100) NOT NULL AFTER `project_urltest`,
                ADD `project_pass` VARCHAR(100) NOT NULL AFTER `project_user`,
                ADD `project_urlhost` VARCHAR(100) NOT NULL AFTER `project_pass`;";

        $wpdb->query( $sql );
    }
}

add_action('admin_head', 'my_admin_custom_css');

function my_admin_custom_css() {
    $css_path = DUAN_PATH . 'css/admin-custom.css'; // Dùng PATH để kiểm tra file
    $css_url  = DUAN_URL . 'css/admin-custom.css';

    wp_enqueue_style(
        'my-admin-style',
        $css_url,
        array(),
        filemtime($css_path)
    );

    $js_path = DUAN_PATH . 'js/admin-script.js'; // Đường dẫn file JS của bạn
    $js_url  = DUAN_URL . 'js/admin-script.js';
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script(
            'my-admin-script',                                        // Handle ID duy nhất
            $js_url, // URL tới file JS
            array( 'jquery' ),                                       // Phụ thuộc vào jQuery
            filemtime( $js_path ),                                   // Version theo thời gian sửa file
            true                                                     // Load file ở footer (cuối trang)
        );
    }
}

function my_custom_admin_menu() {
    add_menu_page(
        'Dự án',        // Title của trang (hiển thị trên thẻ trình duyệt)
        'Dự án',                  // Tên menu hiển thị ở thanh bên trái
        'manage_options',               // Quyền hạn required để xem (Capability)
        'congty',          // Slug duy nhất của menu
        'my_congty_page_callback', // Hàm hiển thị nội dung trang
        'dashicons-admin-generic',      // Dashicon (Icon hiển thị)
        20                              // Vị trí xuất hiện trên thanh menu
    );
}
add_action('admin_menu', 'my_custom_admin_menu');

function my_congty_page_callback() {
    ?>
    <div class="wrap">
        <h1>Danh sách công ty</h1>
    </div>
    <?php
    require_once DUAN_PATH . 'views/my-page.php';
}


function my_custom_admin_submenu() {
    add_submenu_page(
        'congty',             // Slug của Menu Cha
        'Project',                 // Title trang con
        'Project',                 // Tên submenu hiển thị
        'manage_options',                  // Quyền hạn
        'project',          // Slug duy nhất của Submenu
        'my_custom_submenu_page_callback'  // Hàm hiển thị nội dung trang con
    );
    add_submenu_page(
        'congty',             // Slug của Menu Cha
        'Báo cáo',                 // Title trang con
        'Báo cáo',                 // Tên submenu hiển thị
        'manage_options',                  // Quyền hạn
        'baocao',          // Slug duy nhất của Submenu
        'my_baocao_page_callback'  // Hàm hiển thị nội dung trang con
    );
    
    add_submenu_page(
        null,             // Slug của Menu Cha
        'Backup',                 // Title trang con
        'Backup',                 // Tên submenu hiển thị
        'manage_options',                  // Quyền hạn
        'backup',          // Slug duy nhất của Submenu
        'my_backup_page_callback'  // Hàm hiển thị nội dung trang con
    );
}
add_action('admin_menu', 'my_custom_admin_submenu');

function my_custom_submenu_page_callback() {
    ?>
    <div class="wrap">
        <h1>Project</h1>
    </div>
    <?php
    require_once DUAN_PATH . 'views/project.php';
}

function my_baocao_page_callback(){?>
   <div class="wrap">
        <h1>Báo cáo</h1>
    </div>
    <?php require_once DUAN_PATH . 'views/baocao.php';
}

function my_backup_page_callback(){ ?>
    <div class="wrap">
        <h1>Backup</h1>
    </div>
    <?php require_once DUAN_PATH . 'views/backup.php';
    }

function handle_get_project_info_ajax() {
    global $wpdb;

    $table_project = $wpdb->prefix . 'project'; 
    $table_company = $wpdb->prefix . 'company';
    
    // 1. Lấy dữ liệu gửi sang và Sanitize
    $code = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';

    if ( empty( $code ) ) {
        wp_send_json_error( 'Mã không được để trống!' );
    }

    // 2. Truy vấn Database (Ví dụ lấy dự án từ bảng wp_company hoặc bảng bạn muốn)
    
    
    // Tìm thông tin theo project_code (sử dụng get_row như bài trước)
    $project = $wpdb->get_row( 
        $wpdb->prepare( "SELECT * FROM $table_project a, $table_company b WHERE a.id_company = b.id AND project_code = %s", $code ) 
    );

    // 3. Phản hồi kết quả về JS dạng JSON
    if ( $project ) {
        // Trả về chuỗi HTML hoặc nội dung bạn muốn hiển thị vào <td class="val_project">
        $output = '<strong>【'.$project->company_name.'】' . esc_html( $project->project_code ) . ' - '.$project->project_name.'</strong>';
        wp_send_json_success( $output );
    } else {
        wp_send_json_error( 'Không tìm thấy thông tin dự án!' );
    }

    wp_die(); // Bắt buộc kết thúc tiến trình AJAX trong WP
}

// Đăng ký action cho người dùng đã đăng nhập (wp-admin)
add_action( 'wp_ajax_get_project_info', 'handle_get_project_info_ajax' );

//ajax option bao bao
function handle_get_content_by_option() {
    // 1. Lấy value option được gửi từ AJAX
    $option = isset( $_POST['option_val'] ) ? sanitize_text_field( $_POST['option_val'] ) : '';
    $project_code = isset( $_POST['project_code'] ) ? sanitize_text_field( $_POST['project_code'] ) : '';

    global $wpdb;
    $table_project = $wpdb->prefix . 'project';
    $table_company = $wpdb->prefix . 'company';
    $table_baocao = $wpdb->prefix . 'baocao';

    if ( empty( $option ) ) {
        wp_send_json_error( 'Option không hợp lệ!' );
    }

    // 2. Xử lý lấy nội dung $content theo từng option
    // (Bạn có thể dùng switch/match hoặc query từ database)
    // $html = 'お疲れ様です。';
    // $info_product = $wpdb->get_var( 
    //     $wpdb->prepare( "SELECT project_name FROM {$table_project} WHERE project_code = %s", $project_code ) 
    // );
    $project = $wpdb->get_row( 
        $wpdb->prepare( "SELECT a.project_name,a.project_code,a.project_urltest,a.project_urlhost,a.project_user,a.project_pass,b.company_name FROM $table_project a, $table_company b, $table_baocao c WHERE a.id_company = b.id AND c.id_project = a.id AND a.project_code = %s", $project_code ) 
    );

    $url = $project->project_urltest;

    $urladmin = $url.'wp-admin';

    if($project->project_urlhost){
        $url = $project->project_urlhost;
        $urladmin = $url.'cms_login';
    }

    $content = 'お疲れ様です。
【'.$project->company_name.'】'.$project->project_code.' - '.$project->project_name.'のプロジェクトを送ります
'.$url.'

';

$basic='■基本認証：
ユーザー名：gkv 
パスワード：gkvgkv

';

$titleadmin = '■テストサイトの管理画面URL';

if($option=='3' || $project->project_urlhost){
    $titleadmin = '■サイトの管理画面URL';
    $basic = '';
}

$infoadmin= $titleadmin.' 
'.$urladmin.'
ユーザー名： '.$project->project_user.'
パスワード： '.$project->project_pass.'

';

$thongbao = '';
$checkpdf = '';

$uphost= 'Vào wp-admin->setting->Reading->Search engine visibility bỏ check
Cài đặt plugin SSL (Really Simple Security)
Xóa vesion bằng plugin sau đó xóa luôn plugin
Tắt htpass sau đó xóa plugin
Active plugin CloudSecure WP Security và thiết lập bảo mật
ログインURL変更
Chọn 有効
Check リダイレクト設定

Thiết lập: 画像認証追加
Chọn 有効

Thiết lập 管理画面アクセス制限	
Chọn 有効

Thiết lập ログイン通知
Chọn 無効

Vào tk khách =>
'.$url.'wp-admin/options.php        
Tìm và thay mail 2 chỗ cho khách
info@grits.co.jp


■また、以下のファイルはセキュリティ対策を設定しました：
●「.htaccess」：606
●「index.php」：504
●「wp-config.php」：400
●「wp-content/index.php」：504';

    if($option=='1'){
        $checkpdf = 'Check pdf:

';
$thongbao = '報告:
Đã làm xong trang TOP + 10 trang con

';
        $uphost='';
    }elseif($option=='2'){
        $uphost='';
        $thongbao = '報告:
Đã sửa xong nội dung chỉ thị bên dưới

';
    }else{
        
    }

    $content.= $basic.$infoadmin.$checkpdf.$uphost.$thongbao;

    $content .='どうぞ宜しくお願い致します。';
    
    /* 
    // Nếu nội dung lấy từ DB theo $option, bạn truy vấn dạng:
    // global $wpdb;
    // $content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$wpdb->prefix}options_content WHERE option_id = %d", $option ) );
    */

    // 3. Trả kết quả về cho JS
    wp_send_json_success( $content );

    wp_die(); // Bắt buộc để kết thúc tiến trình AJAX
}

// Đăng ký action hook trong Admin
add_action( 'wp_ajax_get_content_by_option', 'handle_get_content_by_option' );

// 1. Hook xử lý xuất file chạy ở 'admin_init' để đảm bảo chưa có HTML nào bị in ra trước đó
add_action( 'admin_init', 'handle_export_company_xml' );

function handle_export_company_xml() {
    // Kiểm tra xem người dùng có bấm nút "Export XML" hay không
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'export_company_xml' ) {
        
        // (Tùy chọn) Kiểm tra quyền Admin
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Bạn không có quyền thực hiện thao tác này!' );
        }

        global $wpdb;
        $table_project = $wpdb->prefix . 'project';
        $table_company = $wpdb->prefix . 'company';
        $table_baocao = $wpdb->prefix . 'baocao';

        // 2. Lấy dữ liệu từ Database
        $projects  = $wpdb->get_results( "SELECT * FROM {$table_project}", ARRAY_A );
        $companies = $wpdb->get_results( "SELECT * FROM {$table_company}", ARRAY_A );
        $baocaos   = $wpdb->get_results( "SELECT * FROM {$table_baocao}", ARRAY_A );

        // Xóa bộ nhớ đệm đầu ra tránh dính mã HTML trống
        if ( ob_get_length() ) {
            ob_clean();
        }

        // 3. Khởi tạo XML gốc
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><database_export/>' );

        // --- XUẤT BẢNG PROJECT ---
        $projects_node = $xml->addChild( 'projects' );
        if ( ! empty( $projects ) ) {
            foreach ( $projects as $row ) {
                $item = $projects_node->addChild( 'project' );
                foreach ( $row as $key => $value ) {
                    $item->addChild( $key, htmlspecialchars( $value ?? '' ) );
                }
            }
        }

        // --- XUẤT BẢNG COMPANY ---
        $companies_node = $xml->addChild( 'companies' );
        if ( ! empty( $companies ) ) {
            foreach ( $companies as $row ) {
                $item = $companies_node->addChild( 'company' );
                foreach ( $row as $key => $value ) {
                    $item->addChild( $key, htmlspecialchars( $value ?? '' ) );
                }
            }
        }

        // --- XUẤT BẢNG BAOCAO ---
        $baocaos_node = $xml->addChild( 'baocaos' );
        if ( ! empty( $baocaos ) ) {
            foreach ( $baocaos as $row ) {
                $item = $baocaos_node->addChild( 'baocao' );
                foreach ( $row as $key => $value ) {
                    $item->addChild( $key, htmlspecialchars( $value ?? '' ) );
                }
            }
        }

        // 4. Header ép trình duyệt tải file về
        $filename = 'export-database-' . date( 'Y-m-d_H-i' ) . '.xml';

        header( 'Content-Type: text/xml; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $xml->asXML();
        exit;
    }
}

add_action( 'admin_init', 'handle_import_all_tables_xml' );

function handle_import_all_tables_xml() {
    // 1. Kiểm tra khi người dùng submit form Import
    if ( isset( $_POST['submit_import_xml'] ) ) {

        // Kiểm tra bảo mật Nonce & Quyền Admin
        if ( ! check_admin_referer( 'import_xml_nonce_action', 'import_xml_nonce_field' ) || ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Yêu cầu không hợp lệ hoặc bạn không có quyền!' );
        }

        // 2. Kiểm tra xem file có được tải lên chưa
        if ( empty( $_FILES['xml_file']['tmp_name'] ) ) {
            wp_die( 'Vui lòng chọn một file XML để import!' );
        }

        $file_tmp = $_FILES['xml_file']['tmp_name'];

        // 3. Đọc dữ liệu XML từ file tạm
        // libxml_use_internal_errors giúp bắt lỗi nếu file XML bị hỏng
        libxml_use_internal_errors( true );
        $xml = simplexml_load_file( $file_tmp );

        if ( $xml === false ) {
            wp_die( 'File XML bị lỗi cấu trúc, không thể đọc được!' );
        }

        global $wpdb;
        $table_project = $wpdb->prefix . 'project';
        $table_company = $wpdb->prefix . 'company';
        $table_baocao  = $wpdb->prefix . 'baocao';

        // --- 4. IMPORT BẢNG PROJECT ---
        if ( isset( $xml->projects->project ) ) {
            foreach ( $xml->projects->project as $project ) {
                $data = array();
                foreach ( $project->children() as $col => $val ) {
                    $data[ $col ] = (string) $val;
                }
                // Dùng REPLACE INTO: nếu ID đã tồn tại thì ghi đè, chưa có thì thêm mới
                if ( ! empty( $data ) ) {
                    $wpdb->replace( $table_project, $data );
                }
            }
        }

        // --- 5. IMPORT BẢNG COMPANY ---
        if ( isset( $xml->companies->company ) ) {
            foreach ( $xml->companies->company as $company ) {
                $data = array();
                foreach ( $company->children() as $col => $val ) {
                    $data[ $col ] = (string) $val;
                }
                if ( ! empty( $data ) ) {
                    $wpdb->replace( $table_company, $data );
                }
            }
        }

        // --- 6. IMPORT BẢNG BAOCAO ---
        if ( isset( $xml->baocaos->baocao ) ) {
            foreach ( $xml->baocaos->baocao as $baocao ) {
                $data = array();
                foreach ( $baocao->children() as $col => $val ) {
                    $data[ $col ] = (string) $val;
                }
                if ( ! empty( $data ) ) {
                    $wpdb->replace( $table_baocao, $data );
                }
            }
        }

        // 7. Chuyển hướng thông báo thành công
        $current_page = sanitize_text_field( $_GET['page'] ?? 'export' );
        $redirect_url = add_query_arg(
            array(
                'page'    => $current_page,
                'message' => 'import_success'
            ),
            admin_url( 'admin.php' )
        );

        wp_redirect( esc_url_raw( $redirect_url ) );
        exit;
    }
}
