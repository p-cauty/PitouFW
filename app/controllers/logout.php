<?php

use PitouFW\Core\Alert;
use PitouFW\Model\UserModel;

UserModel::rejectGuests();

$success = UserModel::logout();
if ($success) {
    Alert::success(L::logout_success);
} else {
    Alert::error(L::logout_error);
}

header('location: ' . WEBROOT);
die;
