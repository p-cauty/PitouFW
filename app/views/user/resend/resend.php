<?php

use function PitouFW\Core\webroot;

?>
<h1 class="h2 mb-3"><?= $message ?></h1>
<a href="<?= webroot() ?>">&larr; <?= L::back ?></a>
