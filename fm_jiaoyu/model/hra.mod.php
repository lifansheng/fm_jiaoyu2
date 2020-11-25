<?php
function newsSync($noticeid){
    // !=3排除作业
    $allNotice = pdo_fetch("SELECT * FROM " . GetTableName('notice') . " WHERE id = '{$noticeid}' ");
    $returnData = [];
    $returnData['schoolId'] = $allNotice['schoolid'];
    $returnData['content'] = htmlspecialchars_decode($allNotice['content']) ;
    $returnData['category'] = $allNotice['type'] == 1 ? 2 : 1;
    if ($allNotice['bj_id']) {
        $returnData['classes'][]['class_id'] = $allNotice['bj_id'];
    } else {
        $returnData['classes'] = [];
    }
    $returnData['title'] = $allNotice['title'];
    $returnData['sender'] = $allNotice['tname'];
    $returnData['publish_time'] = date("Y-m-d H:i:s",$allNotice['createtime']);
    $url = "http://106.12.72.5:8780/api/v1/bancard/sync/notification";
    hra_http_post($url, $returnData);
};
 

function hra_http_post($url, $post_data){
    if (empty($url) || empty($post_data)) {
        return false;
    }
    $postUrl = $url;
    $curlPost = json_encode($post_data);
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type:application/json"
    ));
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
}
function hra_http_post_returndata($url, $post_data){
    if (empty($url) || empty($post_data)) {
        return false;
    }
    $postUrl = $url;
    $curlPost = json_encode($post_data);
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type:application/json"
    ));
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;
}

?>