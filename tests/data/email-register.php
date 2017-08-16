<?php
if (is_array($to)) {
    $to = reset($to);
}
//$to = reset($to);
?>
<!DOCTYPE>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body>
<style>
    .a-btn {
        display: inline-block;
        padding: 10px 20px;
        background: #28c8a0;
        color: #ffffff !important;
        text-decoration: none;
    }
</style>
<h3>Hello <?= $to ?> !</h3>
<br>
<p>你正在注册 Yak 请点击激活帐户，完成注册步骤。</p>
<br>
<a href="<?= $extra['url'] ?>" class="a-btn">点击激活</a>
</body>
</html>
