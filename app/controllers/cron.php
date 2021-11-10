<?php

use PitouFW\Core\Controller;
use PitouFW\Core\DB;
use PitouFW\Core\Mailer;
use PitouFW\Core\Redis;
use PitouFW\Core\Utils;

if (!isset($argc)) {
    Controller::http403Forbidden();
}

if ($argc !== 2) {
    echo 'Usage: php index.php cron' . "\n";
    die;
}

$start_time = time();
$end_time = $start_time + 55;

$redis = new Redis();

while ($end_time > time()) {
    DB::get()->beginTransaction();
    $req = DB::get()->query("SELECT * FROM email_queue WHERE sent_at IS NULL AND error IS NULL ORDER BY id FOR UPDATE");
    while ($email = $req->fetch()) {
        $mailer = new Mailer();
        $mailer->sendMail($email);
        echo '[' . Utils::datetime() . '] Mail #' . $email['id'] . ' sent!' . "\n";
    }
    DB::get()->commit();
    sleep(5);
}
