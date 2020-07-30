<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\NewsletterEmail;

if (POST) {
    if (!empty($_POST['email'])) {
        if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            if (!NewsletterEmail::exists('email', $_POST['email'])) {
                $newsletter_email = new NewsletterEmail();
                $newsletter_email->setEmail($_POST['email'])
                    ->save();
                Alert::success(L::home_form_success);
            } else {
                Alert::warning(L::home_form_warning);
            }
        } else {
            Alert::error(L::home_form_error);
        }
    }
}

Data::get()->add('TITLE', L::home_title);
Controller::renderView('home/home');
