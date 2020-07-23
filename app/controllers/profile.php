<?php

use PitouFW\Model\UserModel;

UserModel::rejectGuests();

$user = UserModel::get();

if (POST) {
    // TODO
}