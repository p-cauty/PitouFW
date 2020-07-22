<h1 class="h2 mb-3"><?= L::forgot_passwd_title ?></h1>
<div class="row">
    <div class="col-md-6">
        <form action="" method="post">
            <div class="form-group">
                <label for="email"><?= L::forgot_passwd_form_label ?></label>
                <input type="email" name="email" id="email" class="form-control" />
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    <?= L::forgot_passwd_form_submit ?>
                </button>
            </div>
        </form>
    </div>
</div>
