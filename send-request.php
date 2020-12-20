<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Mime\Email;


/**
 * UMUTextStats Contact Form 
 *
 * @package joseantonio.garcia8@um.es
 */

error_reporting (E_ALL);
ini_set ('display_errors', 1);



// Detect request type
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code (400);
    die ();
}


/** @var $filter_request_payload Array */
$filter_request_payload = [
    'name'                        => FILTER_SANITIZE_STRING,
    'institution'                 => FILTER_SANITIZE_STRING,
    'comments'                    => FILTER_SANITIZE_STRING,
    'email'                       => FILTER_VALIDATE_EMAIL,
];


/** @var request_payload Array */
$request_payload = filter_var_array ($_POST, $filter_request_payload, true);



// Validate fields
if ( ! ($request_payload['name'] && $request_payload['institution'] && $request_payload['email'])) {
    http_response_code (400);
    die ();
}


// Does not allow urls in the name
if (preg_match ('/http|www/i', $request_payload['name'])) {
    http_response_code (400);
    die ();
}


// Require autoload 
require_once __DIR__ . '/vendor/autoload.php';

 
/** @var $dotenv Dotenv */
$dotenv = new Dotenv ();


// Load environment
$dotenv->load (__DIR__ . '/.env');


/** @var $transport Transport */
$transport = Transport::fromDsn ($_ENV['MAILER_DSN']);


/** @var $mailer Mailer */
$mailer = new Mailer ($transport);


/** @var $email */
$email = (new Email())
    ->from ('joseantonio.garcia8@um.es')
    ->to ('joseantonio.garcia8@um.es')
    ->subject ('Petición de acceso a UMUTextStats de ' . $request_payload['email'])
    ->text ($request_payload['name'] . ', (' . $request_payload['institution'] . ')' . "\r\n" . $request_payload['comments'])
;

$mailer->send ($email);


// Send feedback
$email = (new Email())
    ->from ('joseantonio.garcia8@um.es')
    ->to ($request_payload['email'])
    ->subject ('You have sent a request for using UMUTextStats')
    ->text ('Thanks! Your request has been sent. You will receive a response as soon as possible')
;

$mailer->send ($email);



// Redirect
header ('Location: index.html');