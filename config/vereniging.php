<?php

return [

    /*
    |--------------------------------------------------------------------------
    | E-mailadres van de administratie
    |--------------------------------------------------------------------------
    |
    | Naar dit adres wordt een melding gestuurd als iemand zich aan- of afmeldt
    | als lid. Stel dit in via ADMIN_EMAIL in het .env bestand.
    |
    */

    'admin_email' => env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS', 'contact@vogelvereniging.nl')),

];
