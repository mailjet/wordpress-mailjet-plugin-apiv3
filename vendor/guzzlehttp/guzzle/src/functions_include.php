<?php

namespace MailjetWp;

// Don't redefine the functions if included multiple times.
if (!\function_exists('MailjetWp\\GuzzleHttp\\describe_type')) {
    require __DIR__ . '/functions.php';
}
