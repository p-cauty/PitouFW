<h1 class="h2 mb-3"><?= L::passwd_reset_title ?></h1>
<div class="row">
    <div class="col-md-6">
        <form action="" method="post">
            <div class="form-group">
                <label for="pass1"><?= L::passwd_reset_label ?></label>
                <input type="password" name="pass1" id="pass1" required class="form-control" />
            </div>
            <div class="form-group">
                <label for="pass2"><?= L::register_labels_pass2 ?></label>
                <input type="password" name="pass2" id="pass2" required class="form-control" />
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-circle"></i>
                    <?= L::passwd_reset_submit ?>
                </button>
            </div>
        </form>
    </div>
</div>
