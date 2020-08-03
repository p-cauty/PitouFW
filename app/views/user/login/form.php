<?php

use PitouFW\Model\JustAuthMeFactory;
use function PitouFW\Core\t;

?>
<h1 class="h2 mb-3"><?= L::login_title ?></h1>
<?= JustAuthMeFactory::getSdk()->generateDefaultButtonHtml(t()->getAppliedLang() ?? 'en') ?>
<div class="row">
    <div class="col-md-6">
        <form action="" method="post">
            <div class="form-group">
                <label for="email"><?= L::labels_email ?></label>
                <input type="email" class="form-control" name="email" required id="email" />
            </div>
            <div class="form-group">
                <label for="pass"><?= L::labels_pass ?></label>
                <input type="password" class="form-control" name="pass" required id="pass" />
            </div>
            <div class="form-group">
                <input type="checkbox" name="remember" id="remember" value="1" />
                <label for="remember"><?= L::login_labels_remember ?></label>
            </div>
            <div class="form-inline">
                <button type="submit" class="btn btn-primary mr-3 mb-2">
                    <i class="fas fa-user-lock"></i>
                    <?= L::login_submit ?>
                </button>
                <a href="<?= WEBROOT ?>user/forgot-passwd"><?= L::login_forgot ?></a>
                <a href="<?= WEBROOT ?>user/register"><?= L::login_no_account ?></a>
            </div>
        </form>
    </div>
</div>
