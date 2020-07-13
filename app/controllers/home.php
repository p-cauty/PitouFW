<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\EmailQueue;

$email_queue = EmailQueue::read(1);
$email_queue->setTemplate('mail/default');
$email_queue->save();
var_dump(EmailQueue::fetchAll());

Data::get()->add('TITLE', 'Accueil');
Controller::renderView('home/home');