<?php

use PitouFW\Model\JustAuthMeFactory;

?>
<h1 class="h2 mb-3"><?= L::login_title ?></h1>
<?= JustAuthMeFactory::getSdk()->generateDefaultButtonHtml($_SESSION['lang'] ?? 'en') ?>
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
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-lock"></i>
                    <?= L::login_submit ?>
                </button>
            </div>
        </form>
    </div>
</div>
