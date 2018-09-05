<?php
/**
 * @var $view Symfony\Bundle\FrameworkBundle\Templating\PhpEngine
 * @var $assets Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper
 */

$assets = $view['assets'];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Strategy</title>
    <link rel="stylesheet" href="<?= $assets->getUrl('css/bootstrap.css') ?>">
    <link rel="stylesheet" href="<?= $assets->getUrl('css/bootstrap-grid.css') ?>">
    <link rel="stylesheet" href="<?= $assets->getUrl('css/bootstrap-reboot.css') ?>">
    <link rel="stylesheet" href="<?= $assets->getUrl('css/index.css') ?>">
</head>
<body>

    <h1>My Friends</h1>
    <div id="vu_app">

    </div>

    <!--<script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js"></script>-->
    <script src="<?= $assets->getUrl('js/index.js') ?>"></script>
</body>
</html>