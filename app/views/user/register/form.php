<?php

use PitouFW\Model\JustAuthMeFactory;
use function PitouFW\Core\t;
use function PitouFW\Core\webroot;

?>
<h1 class="h2 mb-3"><?= L::register_title ?></h1>
<?= JustAuthMeFactory::getSdk()->generateDefaultButtonHtml(t()->getAppliedLang() ?? 'en') ?>
<div class="row">
    <div class="col-md-6">
        <form action="" method="post">
            <div class="form-group">
                <label for="email"><?= L::labels_email ?></label>
                <input type="email" class="form-control" name="email" required id="email" />
            </div>
            <div class="form-group">
                <label for="pass1"><?= L::labels_pass ?></label>
                <input type="password" class="form-control" name="pass1" required id="pass1" />
            </div>
            <div class="form-group">
                <label for="pass2"><?= L::register_labels_pass2 ?></label>
                <input type="password" class="form-control" name="pass2" required id="pass2" />
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary mr-3 mb-2">
                    <i class="fas fa-user-plus"></i>
                    <?= L::register_submit ?>
                </button>
                <a href="<?= webroot() ?>user/login"><?= L::register_already_user ?></a>
            </div>
        </form>
    </div>
</div>
