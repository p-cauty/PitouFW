<?php

use PitouFW\Model\UserModel;

?>
<h1><?= L::home_welcome(NAME)  ?></h1>
<?php if (UserModel::isLogged()): ?>
<p><?= L::home_logged_as(UserModel::get()->getEmail()) ?></p>
<a href="<?= WEBROOT ?>logout" class="btn btn-danger">
    <i class="fas fa-user-slash"></i>
    <?= L::home_logout ?>
</a>
<?php else: ?>
<a href="<?= WEBROOT ?>register" class="btn btn-success mb-2 mr-2">
    <i class="fas fa-user-plus"></i>
    <?= L::home_register ?>
</a>
<a href="<?= WEBROOT ?>login" class="btn btn-primary mb-2">
    <i class="fas fa-user-lock"></i>
    <?= L::home_login ?>
</a>
<?php endif ?>
