<?php

use function PitouFW\Core\webroot;

?>
<h1 class="h2 mb-3"><?= $success ?  L::confirm_success : L::confirm_error ?></h1>
<a href="<?= webroot() ?>">&larr; <?= L::back ?></a>
