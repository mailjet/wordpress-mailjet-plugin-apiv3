<?php

namespace MailjetWp;

require '../lib/Analog.php';
Analog::handler(Analog\Handler\Ignore::init());
Analog::log('Hellooooooo');
