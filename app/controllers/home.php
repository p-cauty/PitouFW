<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
var_dump(\PitouFW\Model\EmailModel::hashId(1));
Data::get()->add('TITLE', L::home_title);
Controller::renderView('home/home');