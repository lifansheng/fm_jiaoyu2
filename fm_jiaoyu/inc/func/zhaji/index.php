<?php
require('zhaji.php');

$zhaji = new zhaji();

$mode = $_REQUEST['mode'];
if($mode && method_exists($zhaji,$mode)){
    $zhaji->$mode();
}

