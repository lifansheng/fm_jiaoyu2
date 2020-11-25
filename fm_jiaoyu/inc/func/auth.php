<?php
/**
 * By 高贵血迹
 */
load()->model('mc'); 
mc_oauth_userinfo();
$url = $_SESSION['authurl'];
if(empty($url)){
	message('非法访问','','error');
}
echo "<script>window.location.href='{$url}';</script>";
exit();
?>