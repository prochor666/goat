<?php
$config['email'] = [
    'smtp' => [
        'useSMTP'       => true, // use SMTP (true) or NOT (false)
        'systemMail'    => 'noreply@domain.tld', // optional, reply email
        'host'          => 'smtp.domain.tld', // SMTP server
        'port'          => 587, // SMTP port 25 form SMTP, 587 gmail/tls, 465 gmail/ssl
        'auth'          => true, // Authenticated SMTP true/false
        'user'          => 'postmaster@domain.tld', // SMTP user
        'password'      => 'smtppass', // SMTP password
        'delaySend'     => true, // used with more then 1 recipient
        'delay'         => 1, // sleep delay in seconds (used with more then 1 recipient)
        'security'      => 'tls', // null, 'ssl', 'tls'
    ],
];