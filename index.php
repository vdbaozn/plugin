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
    $response = wp_remote_get($json_url);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return $transient;
    }

    $remote_info = json_decode(wp_remote_retrieve_body($response));
    
    // Version hiện tại của plugin dưới local
    $current_version = '1.0.0'; // Thay bằng version thực tế hoặc defined constant

    // So sánh phiên bản
    if ($remote_info && version_compare($current_version, $remote_info->version, '<')) {
        $obj = new stdClass();
        $obj->slug = $remote_info->slug;
        $obj->new_version = $remote_info->version;
        $obj->package = $remote_info->download_url; // Link tải zip
        
        $transient->response[$obj->slug] = $obj;
    }

    return $transient;
}

// end update plugin
