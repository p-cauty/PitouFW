<?php

use PitouFW\Core\Alert;

?>
<!doctype html>
<html lang="fr">
	<head>
		<title><?= NAME . (isset($TITLE) ? ' - ' . $TITLE : '') ?></title>
		<meta charset="utf-8" />
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
