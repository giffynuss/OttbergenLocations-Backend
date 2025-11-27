<?php
// E-Mail-Konfiguration für Ottbergen Locations
// SMTP-Einstellungen für Outlook

return [
    // SMTP Server Einstellungen (Gmail)
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',

    // Login-Daten
    'smtp_user' => 'patrykbulla.work@gmail.com',
    'smtp_pass' => 'rlor ghtk hvsa pzoa',

    // Absender
    'from_email' => 'patrykbulla.work@gmail.com',
    'from_name' => 'Ottbergen Locations',

    // Basis-URL für Links in E-Mails (Backend)
    'base_url' => 'http://localhost/OttbergenLocations-Backend',

    // Frontend-URL für Links in E-Mails
    'frontend_url' => 'http://localhost:5173',

    // Bankdaten für Überweisungen (statisch)
    'bank_details' => [
        'account_holder' => 'Ottbergen Locations GmbH',
        'iban' => 'DE89 3704 0044 0532 0130 00',
        'bic' => 'COBADEFFXXX',
        'bank_name' => 'Commerzbank'
    ]
];
