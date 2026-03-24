<?php
// Porkbun configuration

Configure::set('Porkbun.email_templates', [
    'en_us' => [
        'lang' => 'en_us',
        'text' => 'Your new domain is being processed and will be registered soon!

Domain: {service.domain}

Thank you for your business!',
        'html' => '<p>Your new domain is being processed and will be registered soon!</p>
<p>Domain: {service.domain}</p>
<p>Thank you for your business!</p>'
    ]
]);

// Nameserver fields
Configure::set('Porkbun.nameserver_fields', [
    'ns1' => [
        'label' => Language::_('Porkbun.nameserver.ns1', true),
        'type' => 'text'
    ],
    'ns2' => [
        'label' => Language::_('Porkbun.nameserver.ns2', true),
        'type' => 'text'
    ],
    'ns3' => [
        'label' => Language::_('Porkbun.nameserver.ns3', true),
        'type' => 'text'
    ],
    'ns4' => [
        'label' => Language::_('Porkbun.nameserver.ns4', true),
        'type' => 'text'
    ],
    'ns5' => [
        'label' => Language::_('Porkbun.nameserver.ns5', true),
        'type' => 'text'
    ]
]);

// Transfer fields
Configure::set('Porkbun.transfer_fields', [
    'domain' => [
        'label' => Language::_('Porkbun.transfer.DomainName', true),
        'type' => 'text'
    ],
    'auth' => [
        'label' => Language::_('Porkbun.transfer.EPPCode', true),
        'type' => 'text'
    ]
]);

// Domain fields (empty by default, no extra fields needed for most TLDs)
Configure::set('Porkbun.domain_fields', []);

// WHOIS fields
$whois_sections = ['Registrant', 'Admin', 'Technical', 'Billing'];
$whois_fields = [];

foreach ($whois_sections as $section) {
    $whois_fields[$section . 'FirstName'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'FirstName', true),
        'type' => 'text',
        'rp' => 'fn',
        'lp' => 'first_name'
    ];
    $whois_fields[$section . 'LastName'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'LastName', true),
        'type' => 'text',
        'rp' => 'ln',
        'lp' => 'last_name'
    ];
    $whois_fields[$section . 'Organization'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'Organization', true),
        'type' => 'text',
        'rp' => 'cp',
        'lp' => 'company'
    ];
    $whois_fields[$section . 'Address1'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'Address1', true),
        'type' => 'text',
        'rp' => 'ad',
        'lp' => 'address1'
    ];
    $whois_fields[$section . 'Address2'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'Address2', true),
        'type' => 'text',
        'rp' => 'ad2',
        'lp' => 'address2'
    ];
    $whois_fields[$section . 'City'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'City', true),
        'type' => 'text',
        'rp' => 'cy',
        'lp' => 'city'
    ];
    $whois_fields[$section . 'StateProvince'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'StateProvince', true),
        'type' => 'text',
        'rp' => 'st',
        'lp' => 'state'
    ];
    $whois_fields[$section . 'PostalCode'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'PostalCode', true),
        'type' => 'text',
        'rp' => 'zp',
        'lp' => 'zip'
    ];
    $whois_fields[$section . 'Country'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'Country', true),
        'type' => 'text',
        'rp' => 'ct',
        'lp' => 'country'
    ];
    $whois_fields[$section . 'Phone'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'Phone', true),
        'type' => 'text',
        'rp' => 'ph',
        'lp' => 'phone'
    ];
    $whois_fields[$section . 'EmailAddress'] = [
        'label' => Language::_('Porkbun.whois.' . $section . 'EmailAddress', true),
        'type' => 'text',
        'rp' => 'em',
        'lp' => 'email'
    ];
}

Configure::set('Porkbun.whois_fields', $whois_fields);

// DNS record types supported by Porkbun
Configure::set('Porkbun.dns_record_types', [
    'A' => 'A',
    'AAAA' => 'AAAA',
    'MX' => 'MX',
    'CNAME' => 'CNAME',
    'ALIAS' => 'ALIAS',
    'TXT' => 'TXT',
    'NS' => 'NS',
    'SRV' => 'SRV',
    'TLSA' => 'TLSA',
    'CAA' => 'CAA',
    'HTTPS' => 'HTTPS',
    'SVCB' => 'SVCB',
    'SSHFP' => 'SSHFP'
]);
