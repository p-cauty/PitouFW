<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Mailer;
use PitouFW\Core\Request;
use PitouFW\Core\Utils;
use PitouFW\Entity\EmailQueue;
use PitouFW\Model\EmailModel;
use PitouFW\Model\UserModel;
use function PitouFW\Core\t;

if (isset($_GET['render_key']) && $_GET['render_key'] === EmailModel::hashId(Request::get()->getArg(2))) {
    $email = EmailQueue::read(Request::get()->getArg(2));

    if ($email === false) {
        Controller::http404NotFound();
        Controller::renderApiError('E-Mail not found');
    }

    Data::get()->setData(json_decode($email->getParams(), true));
    Data::get()->add('unsubscribe_email', $email->getRecipient());
    Data::get()->add('unsubscribe_key', UserModel::hashInfo(strtolower($email->getRecipient()) . UNSUBSCRIBE_SALT));
    Data::get()->add('browser_id', $email->getId());
    Data::get()->add('browser_key', EmailModel::hashId($email->getId()));
    Controller::renderView($email->getTemplate(), null);
    die;
}

if (!Utils::isInternalCall()) {
    Controller::http401Unauthorized();
    Controller::renderApiError('Authentication failed');
}

if (!POST) {
    Controller::http405MethodNotAllowed();
    Controller::renderApiError('Only POST requests are allowed');
}

if (!isset($_POST['to'], $_POST['subject'], $_POST['body']) || $_POST['to'] === '' || $_POST['subject'] === '' || $_POST['body'] === '') {
    Controller::http400BadRequest();
    Controller::renderApiError('To, Subject or Body are missing');
}

if (!filter_var($_POST['to'], FILTER_VALIDATE_EMAIL)) {
    Controller::http400BadRequest();
    Controller::renderApiError('The destination E-Mail address must be valid');
}

$bcc = [];
if (isset($_POST['bcc']) && is_array($_POST['bcc'])) {
    $bcc = $_POST['bcc'];
}

$from = Mailer::SEND_AS_DEFAULT;
if (isset($_POST['from']) && $_POST['from'] !== '') {
    $from = $_POST['from'];
}

$params = [
    'subject' => Utils::secure($_POST['subject']),
    'body' => $_POST['body']
];

$lang = t()->getFallbackLang();
$wanted_lang = Request::get()->getArg(2);
if (file_exists(VIEWS . 'mail/' . $wanted_lang)) {
    $lang = $wanted_lang;
}

$wanted_template = Request::get()->getArg(3);
$template = $wanted_template !== '' ? $wanted_template : $wanted_lang;
switch ($template) {
    case 'default':
        if (isset($_POST['call_to_action'])) {
            if (
                !isset($_POST['call_to_action']['title'], $_POST['call_to_action']['link']) ||
                $_POST['call_to_action']['title'] === '' || $_POST['call_to_action']['link'] === ''
            ) {
                Controller::http400BadRequest();
                Controller::renderApiError('Bad Call-to-action format');
            }

            $params['call_to_action'] = Utils::secure($_POST['call_to_action']);
        }

        $template = 'mail/' . $lang . '/default';
        break;

    default:
        Controller::http404NotFound();
        Controller::renderApiError('This template does not exists');
}

$mailer = new Mailer();
$mailer->queueMail($_POST['to'], $_POST['subject'], $template, $params, $bcc, $from);

Controller::renderApiSuccess();
