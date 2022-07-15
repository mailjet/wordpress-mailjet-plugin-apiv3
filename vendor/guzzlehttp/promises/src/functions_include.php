<?php

namespace MailjetWp;

// Don't redefine the functions if included multiple times.
if (!\function_exists('MailjetWp\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
