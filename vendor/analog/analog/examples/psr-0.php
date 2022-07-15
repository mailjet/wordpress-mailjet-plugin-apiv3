<?php

namespace MailjetWp;

require 'SplClassLoader.php';
$loader = new SplClassLoader('Analog', '../lib');
$loader->register();
use MailjetWp\Analog\Analog;
$log = '';
Analog::handler(\MailjetWp\Analog\Handler\Variable::init($log));
Analog::log('Test one');
Analog::log('Test two');
echo $log;
