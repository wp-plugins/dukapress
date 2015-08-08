<?php
if (!function_exists('add_action')) {
    require_once('../../../wp-load.php');
}


global $wpdb;
$table_name2 = $wpdb->prefix . "dpsc_temp_file_log";
$id = sanitize_text_field($_GET['id']);
$sql = "SELECT saved_name, real_name, count, TIMESTAMPDIFF(SECOND,sent_time,NOW()) as time_diff FROM `{$table_name2}` WHERE saved_name = %s";
$row = $wpdb->get_row($wpdb->prepare($sql,$id));
$dp_expire_hour = intval(get_option('dp_dl_link_expiration_time'));
$dp_expiration_time = $dp_expire_hour*60*60;
$dp_sent_time = $row->time_diff;
$download_count = $row->count;
$newfile_path = DP_DOWNLOAD_FILES_DIR_TEMP.$row->saved_name;
if ($dp_sent_time > $dp_expiration_time) {
    if (file_exists($newfile_path)) {
        @unlink($newfile_path);
    }
    $msg = '<br />Time Expired!<br />';
    echo $msg;
    exit;
}
else {
    $download_count++;
	$sql = "UPDATE {$table_name2} SET count={$download_count} WHERE saved_name = %s";
    $wpdb->query($wpdb->prepare($sql,$id));
    if (!file_exists($newfile_path)) {
        $msg = '<br />Invalid link or outdated link<br />';
        echo $msg;
        exit;
    }

    header("Content-type: application/force-download");
    header('Content-Disposition: inline; filename="' . $newfile_path . '"');
    header("Content-Transfer-Encoding: Binary");
    header("Content-length: ".filesize($newfile_path));
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $row->real_name . '"');

    readfile("$newfile_path");
}