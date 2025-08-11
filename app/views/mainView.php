<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Request;
use function PitouFW\Core\t;

?>
<!doctype html>
<html lang="<?= t()->getAppliedLang() ?>">
	<head>
		<title><?= $TITLE ?? 'PitouFW - Le framework de ceux qui n\'en veulent pas' ?></title>
        <meta name="author" content="<?= AUTHOR ?>" />
        <meta name="description" content="<?= $DESC ?? 'Lorem ipsum dolor sit amet' ?>" />
		<meta charset="utf-8" />
        <?php if (Request::get()->getArg(0) === 'home'): ?>
        <link rel="canonical" href="<?= APP_URL . t()->getAppliedLang() ?>" />
        <?php endif;
        foreach (ACCEPTED_LANGUAGES as $lang):
            if (t()->getAppliedLang() !== $lang): ?>
            <link rel="alternate" hreflang="<?= $lang ?>" href="<?= APP_URL . $lang ?>" />
        <?php endif;
        endforeach; ?>

        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?= $TITLE ?? 'PitouFW - Le framework de ceux qui n\'en veulent pas' ?>" />
        <meta property="og:description" content="<?= $DESC ?? 'Lorem ipsum dolor sit amet' ?>" />
        <meta property="og:url" content="<?= APP_URL ?>" />
        <meta property="og:image" content="<?= APP_URL ?>assets/img/banner.png" />
        <meta property="og:site_name" content="<?= NAME ?>" />
        <meta name="twitter:card" content="summary_large_image" />

        <link type="text/css" rel="stylesheet" href="<?= CSS . 'bootstrap.min.css' ?>" media="screen" />
        <link type="text/css" rel="stylesheet" href="<?= CSS . 'font-awesome_v5.15.1.min.css' ?>" media="screen" />
        <link type="text/css" rel="stylesheet" href="<?= CSS . 'style.css' ?>" media="screen" />

		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0" />
		<meta name="format-detection" content="telephone=no" />

        <link rel="icon" type="image/x-icon" href="<?= IMG ?>icon.png" />
	</head>

	<body>
        <main class="container container-xl">
            <?= Alert::handle() ?>
            <?php require_once @$appView; ?>
        </main>

		<script type="text/javascript" src="<?= JS . 'jquery.min.js' ?>"></script>
		<script type="text/javascript" src="<?= JS . 'bootstrap.min.js' ?>"></script>
		<script type="text/javascript" src="<?= JS . 'script.js' ?>"></script>
	</body>
</html>
