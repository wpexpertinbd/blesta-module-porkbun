<?php
// Errors
$lang['Porkbun.!error.apikey.valid'] = 'Please enter a valid API key.';
$lang['Porkbun.!error.secretapikey.valid'] = 'Please enter a valid Secret API key.';
$lang['Porkbun.!error.key.valid_connection'] = 'Unable to connect to Porkbun with the given credentials. Please verify the API keys.';
$lang['Porkbun.!error.domain.valid'] = 'Invalid domain name.';
$lang['Porkbun.!error.domain.unavailable'] = 'Domain is not available.';
$lang['Porkbun.!error.epp.empty'] = 'EPP code is required for transfers.';
$lang['Porkbun.!error.invalid_term'] = 'Invalid registration term.';
$lang['Porkbun.!error.domain.format'] = 'The domain name contains invalid characters.';
$lang['Porkbun.!error.nameserver.format'] = 'The nameserver contains invalid characters.';
$lang['Porkbun.!error.nameserver.minimum'] = 'At least 2 nameservers are required.';
$lang['Porkbun.!error.epp.format'] = 'The authorization code contains invalid characters.';

// Module Labels
$lang['Porkbun.name'] = 'Porkbun';
$lang['Porkbun.description'] = 'Porkbun is an ICANN accredited domain registrar offering affordable domain registration with free WHOIS privacy and SSL certificates.';
$lang['Porkbun.module_row'] = 'Account';
$lang['Porkbun.module_row_plural'] = 'Accounts';

// Package fields
$lang['Porkbun.package_fields.type'] = 'Type';
$lang['Porkbun.package_fields.type_domain'] = 'Domain Registration';
$lang['Porkbun.package_fields.tld_options'] = 'TLDs';
$lang['Porkbun.package_fields.ns1'] = 'Name Server 1';
$lang['Porkbun.package_fields.ns2'] = 'Name Server 2';
$lang['Porkbun.package_fields.ns3'] = 'Name Server 3';
$lang['Porkbun.package_fields.ns4'] = 'Name Server 4';
$lang['Porkbun.package_fields.ns5'] = 'Name Server 5';

// Module Manage
$lang['Porkbun.manage.box_title'] = 'Manage Porkbun Accounts';
$lang['Porkbun.manage.title'] = 'Porkbun Accounts';
$lang['Porkbun.manage.module_row_title'] = 'Account';
$lang['Porkbun.manage.module_groups_title'] = 'Groups';
$lang['Porkbun.manage.module_rows_options'] = 'Options';
$lang['Porkbun.manage.module_rows.edit'] = 'Edit';
$lang['Porkbun.manage.module_rows.delete'] = 'Delete';
$lang['Porkbun.manage.module_rows.confirm_delete'] = 'Are you sure you want to delete this account?';
$lang['Porkbun.manage.module_rows_no_results'] = 'There are no accounts.';

// Module Management - Add Row
$lang['Porkbun.add_row.box_title'] = 'Add Porkbun Account';
$lang['Porkbun.add_row.basic_title'] = 'Basic Settings';
$lang['Porkbun.add_row.field_user'] = 'Account Label (for internal reference)';
$lang['Porkbun.add_row.field_apikey'] = 'API Key';
$lang['Porkbun.add_row.field_secretapikey'] = 'Secret API Key';
$lang['Porkbun.add_row.add_btn'] = 'Add Account';

// Module Management - Edit Row
$lang['Porkbun.edit_row.box_title'] = 'Edit Porkbun Account';
$lang['Porkbun.edit_row.basic_title'] = 'Basic Settings';
$lang['Porkbun.edit_row.field_user'] = 'Account Label';
$lang['Porkbun.edit_row.field_apikey'] = 'API Key';
$lang['Porkbun.edit_row.field_secretapikey'] = 'Secret API Key';
$lang['Porkbun.edit_row.add_btn'] = 'Update Account';

// Service Management Tabs
$lang['Porkbun.tab_nameservers.title'] = 'Name Servers';
$lang['Porkbun.tab_nameservers.field_ns'] = 'Name Server %1$s';
$lang['Porkbun.tab_nameservers.field_submit'] = 'Update Name Servers';

$lang['Porkbun.tab_settings.title'] = 'Settings';
$lang['Porkbun.tab_settings.field_registrar_lock'] = 'Registrar Lock';
$lang['Porkbun.tab_settings.field_registrar_lock_yes'] = 'Set the registrar lock. Recommended to prevent unauthorized transfer.';
$lang['Porkbun.tab_settings.field_registrar_lock_no'] = 'Release the registrar lock so the domain can be transferred.';
$lang['Porkbun.tab_settings.field_whois_privacy'] = 'WHOIS Privacy';
$lang['Porkbun.tab_settings.field_whois_privacy_yes'] = 'Enable WHOIS Privacy (free with Porkbun)';
$lang['Porkbun.tab_settings.field_whois_privacy_no'] = 'Disable WHOIS Privacy';
$lang['Porkbun.tab_settings.field_auto_renew'] = 'Auto Renew';
$lang['Porkbun.tab_settings.field_auto_renew_on'] = 'Enable auto renewal';
$lang['Porkbun.tab_settings.field_auto_renew_off'] = 'Disable auto renewal';
$lang['Porkbun.tab_settings.field_submit'] = 'Update Settings';

// DNS Records Tab
$lang['Porkbun.tab_dnsrecords.title_list'] = 'DNS Records';
$lang['Porkbun.tab_dnsrecords.add_record'] = 'Add Record';
$lang['Porkbun.tab_dnsrecords.record_type'] = 'Record Type';
$lang['Porkbun.tab_dnsrecords.host'] = 'Host / Subdomain';
$lang['Porkbun.tab_dnsrecords.value'] = 'Value';
$lang['Porkbun.tab_dnsrecords.ttl'] = 'TTL';
$lang['Porkbun.tab_dnsrecords.priority'] = 'Priority';
$lang['Porkbun.tab_dnsrecords.options'] = 'Options';
$lang['Porkbun.tab_dnsrecords.field_submit'] = 'Save Changes';
$lang['Porkbun.tab_dnsrecords.info'] = 'To use Porkbun DNS, you must use Porkbun nameservers.';

// URL Forwarding Tab
$lang['Porkbun.tab_urlforward.title'] = 'URL Forwarding';
$lang['Porkbun.tab_urlforward.add_record'] = 'Add Forward';
$lang['Porkbun.tab_urlforward.subdomain'] = 'Subdomain';
$lang['Porkbun.tab_urlforward.location'] = 'Destination URL';
$lang['Porkbun.tab_urlforward.type'] = 'Type';
$lang['Porkbun.tab_urlforward.type_temporary'] = 'Temporary (302)';
$lang['Porkbun.tab_urlforward.type_permanent'] = 'Permanent (301)';
$lang['Porkbun.tab_urlforward.include_path'] = 'Include Path';
$lang['Porkbun.tab_urlforward.wildcard'] = 'Wildcard';
$lang['Porkbun.tab_urlforward.options'] = 'Options';
$lang['Porkbun.tab_urlforward.field_submit'] = 'Save Changes';

// Admin Actions Tab
$lang['Porkbun.tab_adminactions.title'] = 'Admin Actions';
$lang['Porkbun.tab_admin_actions.title'] = 'Admin Actions';
$lang['Porkbun.tab_admin_actions.field_submit'] = 'Sync Renew Date';
$lang['Porkbun.tab_admin_actions.sync_date_tooltip'] = 'This will synchronize the renewal date with the registrar.';

// DNSSEC Tab
$lang['Porkbun.tab_dnssec.title'] = 'DNSSEC';
$lang['Porkbun.tab_dnssec.title_list'] = 'DNSSEC Records';
$lang['Porkbun.tab_dnssec.add_record'] = 'Add DNSSEC Record';
$lang['Porkbun.tab_dnssec.keytag'] = 'Key Tag';
$lang['Porkbun.tab_dnssec.algorithm'] = 'Algorithm';
$lang['Porkbun.tab_dnssec.digest_type'] = 'Digest Type';
$lang['Porkbun.tab_dnssec.digest'] = 'Digest';
$lang['Porkbun.tab_dnssec.max_sig_life'] = 'Max Sig Life';
$lang['Porkbun.tab_dnssec.key_data_flags'] = 'Key Data Flags';
$lang['Porkbun.tab_dnssec.key_data_protocol'] = 'Key Data Protocol';
$lang['Porkbun.tab_dnssec.key_data_algo'] = 'Key Data Algorithm';
$lang['Porkbun.tab_dnssec.key_data_pubkey'] = 'Key Data Public Key';
$lang['Porkbun.tab_dnssec.options'] = 'Options';
$lang['Porkbun.tab_dnssec.field_submit'] = 'Add DNSSEC Record';
$lang['Porkbun.tab_dnssec.info'] = 'DNSSEC adds a layer of security to your domain by enabling DNS response validation. DS records are managed at the registry level.';
$lang['Porkbun.tab_dnssec.advanced_title'] = 'Key Data (Advanced - Optional)';
$lang['Porkbun.tab_dnssec.no_records'] = 'No DNSSEC records found.';

// Glue Records Tab
$lang['Porkbun.tab_gluerecords.title'] = 'Glue Records';
$lang['Porkbun.tab_gluerecords.title_list'] = 'Glue Records (Child Nameservers)';
$lang['Porkbun.tab_gluerecords.add_record'] = 'Add Glue Record';
$lang['Porkbun.tab_gluerecords.edit_record'] = 'Update IPs';
$lang['Porkbun.tab_gluerecords.hostname'] = 'Hostname';
$lang['Porkbun.tab_gluerecords.subdomain'] = 'Subdomain';
$lang['Porkbun.tab_gluerecords.ips'] = 'IP Addresses';
$lang['Porkbun.tab_gluerecords.options'] = 'Options';
$lang['Porkbun.tab_gluerecords.field_submit'] = 'Add Glue Record';
$lang['Porkbun.tab_gluerecords.info'] = 'Glue records (child nameservers) allow you to use your own domain as a nameserver (e.g., ns1.yourdomain.com). Enter one IP address per line (IPv4 and/or IPv6).';
$lang['Porkbun.tab_gluerecords.no_records'] = 'No glue records found.';

// Domain Transfer
$lang['Porkbun.transfer.DomainName'] = 'Domain Name';
$lang['Porkbun.transfer.EPPCode'] = 'EPP Code';
$lang['Porkbun.domain.DomainAction'] = 'Domain Action';

// Nameservers
$lang['Porkbun.nameserver.ns1'] = 'Name Server 1';
$lang['Porkbun.nameserver.ns2'] = 'Name Server 2';
$lang['Porkbun.nameserver.ns3'] = 'Name Server 3';
$lang['Porkbun.nameserver.ns4'] = 'Name Server 4';
$lang['Porkbun.nameserver.ns5'] = 'Name Server 5';

// Whois Fields
foreach (['Registrant', 'Admin', 'Technical', 'Billing'] as $section) {
    $lang['Porkbun.whois.' . $section . 'FirstName'] = 'First Name';
    $lang['Porkbun.whois.' . $section . 'LastName'] = 'Last Name';
    $lang['Porkbun.whois.' . $section . 'Organization'] = 'Organization';
    $lang['Porkbun.whois.' . $section . 'Address1'] = 'Address 1';
    $lang['Porkbun.whois.' . $section . 'Address2'] = 'Address 2';
    $lang['Porkbun.whois.' . $section . 'City'] = 'City';
    $lang['Porkbun.whois.' . $section . 'StateProvince'] = 'State/Province';
    $lang['Porkbun.whois.' . $section . 'PostalCode'] = 'Postal Code';
    $lang['Porkbun.whois.' . $section . 'Country'] = 'Country';
    $lang['Porkbun.whois.' . $section . 'Phone'] = 'Phone';
    $lang['Porkbun.whois.' . $section . 'EmailAddress'] = 'Email';
}

// Service Info
$lang['Porkbun.service_info.domain'] = 'Domain';
$lang['Porkbun.service_info.status'] = 'Status';
$lang['Porkbun.service_info.expiration'] = 'Expiration Date';
$lang['Porkbun.service_info.lock_status'] = 'Registrar Lock';
$lang['Porkbun.service_info.auto_renew'] = 'Auto Renew';

// Tab Whois
$lang['Porkbun.tab_whois.title'] = 'Whois';
$lang['Porkbun.tab_whois.section_registrant'] = 'Registrant';
$lang['Porkbun.tab_whois.section_admin'] = 'Administrative';
$lang['Porkbun.tab_whois.section_technical'] = 'Technical';
$lang['Porkbun.tab_whois.section_billing'] = 'Billing';
$lang['Porkbun.tab_whois.field_submit'] = 'Update Whois';

// Notices
$lang['Porkbun.notice.whois_not_supported'] = 'Porkbun does not currently support WHOIS contact editing via API. Please manage WHOIS contacts from your Porkbun account dashboard.';

// API Errors
$lang['Porkbun.!error.api.http'] = 'The API request failed. Please try again later.';
$lang['Porkbun.!error.api.unknown'] = 'An unexpected error occurred. Please try again.';
$lang['Porkbun.!error.transfer.unsupported'] = 'Domain transfer via API is not currently supported by Porkbun. Please initiate transfers from the Porkbun dashboard.';
$lang['Porkbun.!error.epp.dashboard'] = 'Please retrieve the EPP/Auth code from your Porkbun dashboard.';
$lang['Porkbun.!error.user.valid'] = 'Please enter an account label.';
$lang['Porkbun.!error.lock.unsupported'] = 'Domain locking is managed through the Porkbun dashboard.';
$lang['Porkbun.!error.sandbox.valid'] = 'Sandbox must be either true or false.';
