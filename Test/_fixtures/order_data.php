<?php declare(strict_types=1);

return [
    0 => [
        'id' => '67',
        'ordernumber' => '20006',
        'userID' => '1',
        'invoice_amount' => '462.85',
        'invoice_amount_net' => '388.95',
        'invoice_shipping' => '9.9',
        'invoice_shipping_net' => '8.32',
        'invoice_shipping_tax_rate' => '19',
        'ordertime' => '2018-10-09 11:48:06',
        'status' => '0',
        'cleared' => '17',
        'paymentID' => '5',
        'transactionID' => '',
        'comment' => '',
        'customercomment' => '',
        'internalcomment' => '',
        'net' => '0',
        'taxfree' => '0',
        'partnerID' => '',
        'temporaryID' => '',
        'referer' => '',
        'cleareddate' => null,
        'trackingcode' => '',
        'language' => '1',
        'dispatchID' => '14',
        'currency' => 'EUR',
        'currencyFactor' => '1',
        'subshopID' => '1',
        'remote_addr' => '::',
        'deviceType' => 'desktop',
        'is_proportional_calculation' => '0',
        'changed' => '2018-10-09 11:48:06',
        'attributes' => [
            'id' => '12',
            'orderID' => '67',
            'attribute1' => null,
            'attribute2' => null,
            'attribute3' => null,
            'attribute4' => null,
            'attribute5' => null,
            'attribute6' => null,
        ],
        'shippingMethod' => [
            'id' => '14',
            'name' => 'Express Versand',
            'type' => '0',
            'description' => 'Zustellung innerhalb von 2 Werktagen',
            'comment' => 'Zustellung innerhalb von 2 Werktagen',
            'active' => '1',
            'position' => '2',
            'calculation' => '1',
            'surcharge_calculation' => '3',
            'tax_calculation' => '0',
            'shippingfree' => null,
            'multishopID' => null,
            'customergroupID' => null,
            'bind_shippingfree' => '0',
            'bind_time_from' => null,
            'bind_time_to' => null,
            'bind_instock' => null,
            'bind_laststock' => '0',
            'bind_weekday_from' => null,
            'bind_weekday_to' => null,
            'bind_weight_from' => null,
            'bind_weight_to' => null,
            'bind_price_from' => null,
            'bind_price_to' => null,
            'bind_sql' => null,
            'status_link' => '',
            'calculation_sql' => null,
        ],
        'customer' => [
            'id' => '1',
            'password' => '$2y$10$zm367zCOCZgDxhrg0WvFwuMQ3TUsE2GVb/MpFZUcwXerVO7aSay5G',
            'encoder' => 'bcrypt',
            'email' => 'test@example.com',
            'active' => '1',
            'accountmode' => '0',
            'confirmationkey' => '',
            'paymentID' => '5',
            'doubleOptinRegister' => '0',
            'doubleOptinEmailSentDate' => null,
            'doubleOptinConfirmDate' => null,
            'firstlogin' => '2011-11-23',
            'lastlogin' => '2018-10-09 11:48:06',
            'sessionID' => '00vla7tcrsa9kptp72b5rs1tcc',
            'newsletter' => '0',
            'validation' => '',
            'affiliate' => '0',
            'customergroup' => 'EK',
            'paymentpreset' => '0',
            'language' => '1',
            'subshopID' => '1',
            'referer' => '',
            'pricegroupID' => null,
            'internalcomment' => '',
            'failedlogins' => '0',
            'lockeduntil' => null,
            'default_billing_address_id' => '1',
            'default_shipping_address_id' => '3',
            'title' => null,
            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => null,
            'customernumber' => '20001',
            'login_token' => 'c1ea7fff-74cc-4aef-ac5a-73eb96735012.1',
            'changed' => null,
        ],
        'orderstatus' => [
            'id' => '0',
            'name' => 'open',
            'description' => 'Offen',
            'position' => '1',
            'group' => 'state',
            'mail' => '1',
        ],
        'paymentstatus' => [
            'id' => '17',
            'name' => 'open',
            'description' => 'Offen',
            'position' => '0',
            'group' => 'payment',
            'mail' => '0',
        ],
        'billingaddress' => [
            'id' => '6',
            'userID' => '1',
            'orderID' => '67',
            'company' => 'Muster GmbH',
            'department' => '',
            'salutation' => 'mr',
            'customernumber' => '20001',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'street' => 'Musterstr. 55',
            'zipcode' => '55555',
            'city' => 'Musterhausen',
            'phone' => '05555 / 555555',
            'countryID' => '2',
            'stateID' => '3',
            'ustid' => null,
            'additional_address_line1' => null,
            'additional_address_line2' => null,
            'title' => null,
            'attributes' => [
                'id' => '6',
                'billingID' => '6',
                'text1' => null,
                'text2' => null,
                'text3' => null,
                'text4' => null,
                'text5' => null,
                'text6' => null,
            ],
            'country' => [
                'id' => '2',
                'countryname' => 'Deutschland',
                'countryiso' => 'DE',
                'areaID' => '1',
                'countryen' => 'GERMANY',
                'position' => '1',
                'notice' => '',
                'taxfree' => '0',
                'taxfree_ustid' => '0',
                'taxfree_ustid_checked' => '0',
                'active' => '1',
                'iso3' => 'DEU',
                'display_state_in_registration' => '0',
                'force_state_in_registration' => '0',
            ],
            'state' => [
                'id' => '3',
                'countryID' => '2',
                'name' => 'Nordrhein-Westfalen',
                'shortcode' => 'NW',
                'position' => '0',
                'active' => '1',
            ],
        ],
        'shippingaddress' => [
            'id' => '6',
            'userID' => '1',
            'orderID' => '67',
            'company' => 'shopware AG',
            'department' => '',
            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'street' => 'Mustermannstraße 92',
            'zipcode' => '48624',
            'city' => 'Schöppingen',
            'phone' => '',
            'countryID' => '2',
            'stateID' => null,
            'additional_address_line1' => '',
            'additional_address_line2' => '',
            'title' => '',
            'attributes' => [
                'id' => '6',
                'shippingID' => '6',
                'text1' => null,
                'text2' => null,
                'text3' => null,
                'text4' => null,
                'text5' => null,
                'text6' => null,
            ],
            'country' => [
                'id' => '2',
                'countryname' => 'Deutschland',
                'countryiso' => 'DE',
                'areaID' => '1',
                'countryen' => 'GERMANY',
                'position' => '1',
                'notice' => '',
                'taxfree' => '0',
                'taxfree_ustid' => '0',
                'taxfree_ustid_checked' => '0',
                'active' => '1',
                'iso3' => 'DEU',
                'display_state_in_registration' => '0',
                'force_state_in_registration' => '0',
            ],
        ],
        'payment' => [
            'id' => '5',
            'name' => 'prepayment',
            'description' => 'Vorkasse',
            'template' => 'prepayment.tpl',
            'class' => 'prepayment.php',
            'table' => '',
            'hide' => '0',
            'additionaldescription' => '',
            'debit_percent' => '0',
            'surcharge' => '0',
            'surchargestring' => '',
            'position' => '1',
            'active' => '1',
            'esdactive' => '0',
            'embediframe' => '',
            'hideprospect' => '0',
            'action' => null,
            'pluginID' => null,
            'source' => null,
            'mobile_inactive' => '0',
        ],
        'paymentcurrency' => [
            'id' => '1',
            'currency' => 'EUR',
            'name' => 'Euro',
            'standard' => '1',
            'factor' => '1',
            'templatechar' => '&euro;',
            'symbol_position' => '0',
            'position' => '0',
        ],
        '_locale' => 'de_DE',
        'details' => [
            0 => [
                'id' => '228',
                'orderID' => '67',
                'ordernumber' => '20006',
                'articleID' => '1',
                'articleordernumber' => 'SW10001',
                'price' => '459.95',
                'quantity' => '1',
                'name' => 'Hauptartikel',
                'status' => '0',
                'shipped' => '0',
                'shippedgroup' => '0',
                'releasedate' => '0000-00-00',
                'modus' => '0',
                'esdarticle' => '0',
                'taxID' => '1',
                'tax_rate' => '19',
                'config' => '',
                'ean' => '',
                'unit' => 'Stück',
                'pack_unit' => '',
                'articleDetailID' => '1',
                'attributes' => [
                    'id' => '31',
                    'detailID' => '228',
                    'attribute1' => '',
                    'attribute2' => null,
                    'attribute3' => null,
                    'attribute4' => null,
                    'attribute5' => null,
                    'attribute6' => null,
                    'swag_promotion_id' => null,
                ],
                'tax' => [
                    'id' => '1',
                    'tax' => '19.00',
                    'description' => '19%',
                ],
            ],
            1 => [
                'id' => '229',
                'orderID' => '67',
                'ordernumber' => '20006',
                'articleID' => '0',
                'articleordernumber' => 'SHIPPINGDISCOUNT',
                'price' => '-2',
                'quantity' => '1',
                'name' => 'Warenkorbrabatt',
                'status' => '0',
                'shipped' => '0',
                'shippedgroup' => '0',
                'releasedate' => '0000-00-00',
                'modus' => '4',
                'esdarticle' => '0',
                'taxID' => '0',
                'tax_rate' => '19',
                'config' => '',
                'ean' => '',
                'unit' => '',
                'pack_unit' => '',
                'articleDetailID' => '0',
                'attributes' => [
                    'id' => '32',
                    'detailID' => '229',
                    'attribute1' => null,
                    'attribute2' => null,
                    'attribute3' => null,
                    'attribute4' => null,
                    'attribute5' => null,
                    'attribute6' => null,
                    'swag_promotion_id' => null,
                ],
            ],
            2 => [
                'id' => '230',
                'orderID' => '67',
                'ordernumber' => '20006',
                'articleID' => '0',
                'articleordernumber' => 'cart-absolut',
                'price' => '-5',
                'quantity' => '1',
                'name' => 'Cart Absolut',
                'status' => '0',
                'shipped' => '0',
                'shippedgroup' => '0',
                'releasedate' => '0000-00-00',
                'modus' => '4',
                'esdarticle' => '0',
                'taxID' => '0',
                'tax_rate' => '19',
                'config' => '',
                'ean' => '',
                'unit' => '',
                'pack_unit' => '',
                'articleDetailID' => '0',
                'attributes' => [
                    'id' => '33',
                    'detailID' => '230',
                    'attribute1' => null,
                    'attribute2' => null,
                    'attribute3' => null,
                    'attribute4' => null,
                    'attribute5' => null,
                    'attribute6' => null,
                    'swag_promotion_id' => null,
                ],
            ],
        ],
    ],
    1 => [
        'id' => '70',
        'ordernumber' => '20007',
        'userID' => '2',
        'invoice_amount' => '956.45',
        'invoice_amount_net' => '803.74',
        'invoice_shipping' => '0',
        'invoice_shipping_net' => '0',
        'invoice_shipping_tax_rate' => null,
        'ordertime' => '2018-10-10 13:36:51',
        'status' => '0',
        'cleared' => '17',
        'paymentID' => '4',
        'transactionID' => '',
        'comment' => '',
        'customercomment' => '',
        'internalcomment' => '',
        'net' => '1',
        'taxfree' => '0',
        'partnerID' => '',
        'temporaryID' => '',
        'referer' => '',
        'cleareddate' => null,
        'trackingcode' => 'b2b-tracking-code',
        'language' => '1',
        'dispatchID' => '14',
        'currency' => 'EUR',
        'currencyFactor' => '1',
        'subshopID' => '1',
        'remote_addr' => '::',
        'deviceType' => 'desktop',
        'is_proportional_calculation' => '0',
        'changed' => '2018-10-10 13:36:51',
        'attributes' => [
            'id' => '15',
            'orderID' => '70',
            'attribute1' => null,
            'attribute2' => null,
            'attribute3' => null,
            'attribute4' => null,
            'attribute5' => null,
            'attribute6' => null,
        ],
        'shippingMethod' => [
            'id' => '14',
            'name' => 'Express Versand',
            'type' => '0',
            'description' => 'Zustellung innerhalb von 2 Werktagen',
            'comment' => 'Zustellung innerhalb von 2 Werktagen',
            'active' => '1',
            'position' => '2',
            'calculation' => '1',
            'surcharge_calculation' => '3',
            'tax_calculation' => '0',
            'shippingfree' => null,
            'multishopID' => null,
            'customergroupID' => null,
            'bind_shippingfree' => '0',
            'bind_time_from' => null,
            'bind_time_to' => null,
            'bind_instock' => null,
            'bind_laststock' => '0',
            'bind_weekday_from' => null,
            'bind_weekday_to' => null,
            'bind_weight_from' => null,
            'bind_weight_to' => null,
            'bind_price_from' => null,
            'bind_price_to' => null,
            'bind_sql' => null,
            'status_link' => '',
            'calculation_sql' => null,
        ],
        'customer' => [
            'id' => '2',
            'password' => '$2y$10$NXmIBQOpQnwt3a0u8oQQl./.De9s6aIkaaQmZwmZlz/84IPANQErq',
            'encoder' => 'bcrypt',
            'email' => 'mustermann@b2b.de',
            'active' => '1',
            'accountmode' => '0',
            'confirmationkey' => '',
            'paymentID' => '4',
            'doubleOptinRegister' => '0',
            'doubleOptinEmailSentDate' => null,
            'doubleOptinConfirmDate' => null,
            'firstlogin' => '2012-08-30',
            'lastlogin' => '2018-10-10 13:36:51',
            'sessionID' => '95f0u44agm8rtsep2mj237ha2p',
            'newsletter' => '0',
            'validation' => '0',
            'affiliate' => '0',
            'customergroup' => 'H',
            'paymentpreset' => '4',
            'language' => '1',
            'subshopID' => '1',
            'referer' => '',
            'pricegroupID' => null,
            'internalcomment' => '',
            'failedlogins' => '0',
            'lockeduntil' => null,
            'default_billing_address_id' => '2',
            'default_shipping_address_id' => '4',
            'title' => null,
            'salutation' => 'mr',
            'firstname' => 'Händler',
            'lastname' => 'Kundengruppe-Netto',
            'birthday' => null,
            'customernumber' => '20003',
            'login_token' => 'ab5cc9dc-06c9-446c-8167-2ef99b3d82dd.1',
            'changed' => null,
        ],
        'orderstatus' => [
            'id' => '0',
            'name' => 'open',
            'description' => 'Offen',
            'position' => '1',
            'group' => 'state',
            'mail' => '1',
        ],
        'paymentstatus' => [
            'id' => '17',
            'name' => 'open',
            'description' => 'Offen',
            'position' => '0',
            'group' => 'payment',
            'mail' => '0',
        ],
        'billingaddress' => [
            'id' => '7',
            'userID' => '2',
            'orderID' => '70',
            'company' => 'B2B',
            'department' => 'Einkauf',
            'salutation' => 'mr',
            'customernumber' => '20003',
            'firstname' => 'Händler',
            'lastname' => 'Kundengruppe-Netto',
            'street' => 'Musterweg 1',
            'zipcode' => '55555',
            'city' => 'Musterstadt',
            'phone' => '012345 / 6789',
            'countryID' => '2',
            'stateID' => '3',
            'ustid' => 'DE123TEST',
            'additional_address_line1' => null,
            'additional_address_line2' => null,
            'title' => null,
            'attributes' => [
                'id' => '7',
                'billingID' => '7',
                'text1' => null,
                'text2' => null,
                'text3' => null,
                'text4' => null,
                'text5' => null,
                'text6' => null,
            ],
            'country' => [
                'id' => '2',
                'countryname' => 'Deutschland',
                'countryiso' => 'DE',
                'areaID' => '1',
                'countryen' => 'GERMANY',
                'position' => '1',
                'notice' => '',
                'taxfree' => '0',
                'taxfree_ustid' => '0',
                'taxfree_ustid_checked' => '0',
                'active' => '1',
                'iso3' => 'DEU',
                'display_state_in_registration' => '0',
                'force_state_in_registration' => '0',
            ],
            'state' => [
                'id' => '3',
                'countryID' => '2',
                'name' => 'Nordrhein-Westfalen',
                'shortcode' => 'NW',
                'position' => '0',
                'active' => '1',
            ],
        ],
        'shippingaddress' => [
            'id' => '7',
            'userID' => '2',
            'orderID' => '70',
            'company' => 'B2B',
            'department' => 'Einkauf',
            'salutation' => 'mr',
            'firstname' => 'Händler',
            'lastname' => 'Kundengruppe-Netto',
            'street' => 'Scha-Silesi 123',
            'zipcode' => '1234',
            'city' => 'Paris',
            'phone' => '',
            'countryID' => '28',
            'stateID' => '21',
            'additional_address_line1' => '',
            'additional_address_line2' => '',
            'title' => '',
            'attributes' => [
                'id' => '7',
                'shippingID' => '7',
                'text1' => null,
                'text2' => null,
                'text3' => null,
                'text4' => null,
                'text5' => null,
                'text6' => null,
            ],
            'country' => [
                'id' => '28',
                'countryname' => 'USA',
                'countryiso' => 'US',
                'areaID' => '2',
                'countryen' => 'USA',
                'position' => '10',
                'notice' => '',
                'taxfree' => '0',
                'taxfree_ustid' => '0',
                'taxfree_ustid_checked' => '0',
                'active' => '0',
                'iso3' => 'USA',
                'display_state_in_registration' => '0',
                'force_state_in_registration' => '0',
            ],
            'state' => [
                'id' => '21',
                'countryID' => '28',
                'name' => 'Alaska',
                'shortcode' => 'AK',
                'position' => '0',
                'active' => '1',
            ],
        ],
        'payment' => [
            'id' => '4',
            'name' => 'invoice',
            'description' => 'Rechnung',
            'template' => 'invoice.tpl',
            'class' => 'invoice.php',
            'table' => '',
            'hide' => '0',
            'additionaldescription' => 'Sie zahlen einfach und bequem auf Rechnung. Shopware bietet z.B. auch die Möglichkeit, Rechnung automatisiert erst ab der 2. Bestellung für Kunden zur Verfügung zu stellen, um Zahlungsausfälle zu vermeiden.',
            'debit_percent' => '0',
            'surcharge' => '5',
            'surchargestring' => '',
            'position' => '3',
            'active' => '1',
            'esdactive' => '1',
            'embediframe' => '',
            'hideprospect' => '0',
            'action' => '',
            'pluginID' => null,
            'source' => null,
            'mobile_inactive' => '0',
        ],
        'paymentcurrency' => [
            'id' => '1',
            'currency' => 'EUR',
            'name' => 'Euro',
            'standard' => '1',
            'factor' => '1',
            'templatechar' => '&euro;',
            'symbol_position' => '0',
            'position' => '0',
        ],
        '_locale' => 'de_DE',
        'details' => [
            0 => [
                'id' => '235',
                'orderID' => '70',
                'ordernumber' => '20007',
                'articleID' => '2',
                'articleordernumber' => 'SW10002',
                'price' => '798.74',
                'quantity' => '1',
                'name' => 'Hauptartikel mit E-Mail-Benachrichtigung',
                'status' => '0',
                'shipped' => '0',
                'shippedgroup' => '0',
                'releasedate' => '0000-00-00',
                'modus' => '0',
                'esdarticle' => '0',
                'taxID' => '1',
                'tax_rate' => '19',
                'config' => '',
                'ean' => '',
                'unit' => 'Stück',
                'pack_unit' => '',
                'articleDetailID' => '2',
                'attributes' => [
                    'id' => '38',
                    'detailID' => '235',
                    'attribute1' => '',
                    'attribute2' => null,
                    'attribute3' => null,
                    'attribute4' => null,
                    'attribute5' => null,
                    'attribute6' => null,
                    'swag_promotion_id' => null,
                ],
                'tax' => [
                    'id' => '1',
                    'tax' => '19.00',
                    'description' => '19%',
                ],
            ],
            1 => [
                'id' => '236',
                'orderID' => '70',
                'ordernumber' => '20007',
                'articleID' => '0',
                'articleordernumber' => 'sw-payment-absolute',
                'price' => '5',
                'quantity' => '1',
                'name' => 'Zuschlag für Zahlungsart',
                'status' => '0',
                'shipped' => '0',
                'shippedgroup' => '0',
                'releasedate' => '0000-00-00',
                'modus' => '4',
                'esdarticle' => '0',
                'taxID' => '0',
                'tax_rate' => '19',
                'config' => '',
                'ean' => '',
                'unit' => '',
                'pack_unit' => '',
                'articleDetailID' => '0',
                'attributes' => [
                    'id' => '39',
                    'detailID' => '236',
                    'attribute1' => null,
                    'attribute2' => null,
                    'attribute3' => null,
                    'attribute4' => null,
                    'attribute5' => null,
                    'attribute6' => null,
                    'swag_promotion_id' => null,
                ],
            ],
        ],
    ],
];
