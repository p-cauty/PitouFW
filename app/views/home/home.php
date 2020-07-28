<?php

use PitouFW\Model\UserModel;

?>
<h1><?= L::home_welcome(NAME)  ?></h1>
<?php if (UserModel::isLogged()): ?>
<p><?= L::home_logged_as(UserModel::get()->getEmail()) ?></p>
<a href="<?= WEBROOT ?>profile" class="btn btn-success">
    <i class="fas fa-user-cog"></i>
    <?= L::home_profile ?>
</a>
<a href="<?= WEBROOT ?>logout" class="btn btn-danger">
    <i class="fas fa-user-slash"></i>
    <?= L::home_logout ?>
</a>
<?php else: ?>
<a href="<?= WEBROOT ?>register" class="btn btn-success mb-2 mr-2">
    <i class="fas fa-user-plus"></i>
    <?= L::home_register ?>
</a>
<a href="<?= WEBROOT ?>login" class="btn btn-info mb-2">
    <i class="fas fa-user-lock"></i>
    <?= L::home_login ?>
</a>
<?php endif ?>
<div class="row">
    <div class="col-md-6">
        <form action="" method="post" class="mt-4 form-inline">
            <div class="form-group">
                <input class="form-control mr-2 mb-2" type="email" name="email" placeholder="<?= L::home_form_placeholder ?>" required />
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-envelope"></i>
                    <?= L::home_form_submit ?>
                </button>
            </div>
        </form>
    </div>
</div>
