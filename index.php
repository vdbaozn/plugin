<?php
/**
 * Plugin Name: Dự án
 * Plugin URI:  https://domain-cua-ban.com/my-first-plugin
 * Description: Đây là plugin đầu tiên của tôi giúp hiển thị một dòng thông báo.
 * Version:     1.0.0
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