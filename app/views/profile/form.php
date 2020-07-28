<?php

use PitouFW\Entity\User;

/** @var User $user */

?>
<h1><?= L::profile_title ?></h1>
<div class="row">
    <div class="col-lg-6">
        <form action="" method="post">
            <div class="form-group">
                <label for="email"><?= L::labels_email ?></label>
                <input type="email" name="email" id="email" class="form-control" value="<?= $user->getEmail() ?>" />
            </div>
            <div class="form-group">
                <label for="pass1"><?= L::profile_labels_newpass ?></label>
                <input type="password" name="pass1" id="pass1" class="form-control" />
            </div>
            <div class="form-group">
                <label for="pass2"><?= L::register_labels_pass2 ?></label>
                <input type="password" name="pass2" id="pass2" class="form-control" />
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-user-check"></i>
                    <?= L::profile_submit ?>
                </button>
            </div>
        </form>
    </div>
</div>
