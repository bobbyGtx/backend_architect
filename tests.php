<?php
$tst = '1';

$var = explode(',', $tst);

echo is_array($var)?json_encode($var):$var;






