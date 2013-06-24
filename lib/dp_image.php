<?php
if (!function_exists('add_action')) {
    require_once('../../../../wp-load.php');
}

echo file_get_contents(dp_img_resize('', $_REQUEST['src'],$_REQUEST['w'], $_REQUEST['h']));
?>