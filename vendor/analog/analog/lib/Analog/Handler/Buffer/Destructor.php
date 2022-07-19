<?php

namespace MailjetWp\Analog\Handler\Buffer;

/**
 * A destructor object to call close() for us at the end of the request.
 */
class Destructor
{
    public function __destruct()
    {
        \MailjetWp\Analog\Handler\Buffer::close();
    }
}
