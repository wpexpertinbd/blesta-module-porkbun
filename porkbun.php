<?php

/**
 * Porkbun Module
 *
 * @package blesta
 * @subpackage blesta.components.modules.porkbun
 * @copyright Copyright (c) 2025, BiswasHost
 * @link https://www.biswashost.com
 */

if (!class_exists('RegistrarModule')) {
    die('This file must be installed as a Blesta module.');
}

class Porkbun extends RegistrarModule
{
    /**
     * @var string Default module view path
     */
    private static $defaultModuleView;

    /**
     * @var PorkbunApi An instance of the Porkbun API
     */
    private $api;

    /**
     * Initializes the module
     */
    public function __construct()
    {
        $this->loadConfig(__DIR__ . DS . 'config.json');
        Loader::loadComponents($this, ['Input', 'Record']);
        Language::loadLang('porkbun', null, __DIR__ . DS . 'language' . DS);
        Configure::load('porkbun', __DIR__ . DS . 'config' . DS);
        self::$defaultModuleView = 'components' . DS . 'modules' . DS . 'porkbun' . DS;
    }

    /**
     * Performs any necessary bootstrapping actions
     *
     * @return array A numerically indexed array of meta data
     */
    public function install()
    {
        return [];
    }

    /**
     * Performs migration of data from $current_version
     *
     * @param string $current_version The current installed version of this module
     */
    public function upgrade($current_version)
    {
        if (version_compare($this->getVersion(), $current_version, '>')) {
            if (!isset($this->Record)) {
                Loader::loadComponents($this, ['Record']);
            }
        }
    }

    /**
     * Performs any necessary cleanup actions
     *
     * @param int $module_id The ID of the module being uninstalled
     * @param boolean $last_instance True if $module_id is the last instance
     */
    public function uninstall($module_id, $last_instance)
    {
        // Nothing to clean up
    }

    // =========================================================================
    // MODULE ROW MANAGEMENT
    // =========================================================================

    /**
     * Returns the rendered view of the manage module page
     */
    public function manageModule($module, array &$vars)
    {
        $this->view = new View('manage', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html', 'Widget']);

        $link_buttons = [];
        $link_buttons[] = [
            'name' => Language::_('Porkbun.add_row.add_btn', true),
            'attributes' => ['href' => $this->base_uri . 'settings/company/modules/addrow/' . $module->id]
        ];

        $this->view->set('module', $module);
        $this->view->set('link_buttons', $link_buttons);

        return $this->view->fetch();
    }

    /**
     * Returns the rendered view of the add module row page
     */
    public function manageAddRow(array &$vars)
    {
        $this->view = new View('add_row', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html', 'Widget']);

        $this->view->set('vars', (object) $vars);
        return $this->view->fetch();
    }

    /**
     * Returns the rendered view of the edit module row page
     */
    public function manageEditRow($module_row, array &$vars)
    {
        $this->view = new View('edit_row', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html', 'Widget']);

        if (empty($vars)) {
            $vars = (array) $module_row->meta;
        }

        $this->view->set('vars', (object) $vars);
        return $this->view->fetch();
    }

    /**
     * Adds the module row on the remote server
     */
    public function addModuleRow(array &$vars)
    {
        $meta_fields = ['user', 'apikey', 'secretapikey'];
        $encrypted_fields = ['apikey', 'secretapikey'];

        $this->Input->setRules($this->getRowRules($vars));

        if ($this->Input->validates($vars)) {
            $meta = [];
            foreach ($vars as $key => $value) {
                if (in_array($key, $meta_fields)) {
                    $meta[] = [
                        'key' => $key,
                        'value' => $value,
                        'encrypted' => in_array($key, $encrypted_fields) ? 1 : 0
                    ];
                }
            }
            return $meta;
        }
    }

    /**
     * Edits the module row on the remote server
     */
    public function editModuleRow($module_row, array &$vars)
    {
        $meta_fields = ['user', 'apikey', 'secretapikey'];
        $encrypted_fields = ['apikey', 'secretapikey'];

        $module_row_meta = array_merge((array) $module_row->meta, $vars);

        $this->Input->setRules($this->getRowRules($vars));

        if ($this->Input->validates($vars)) {
            $meta = [];
            foreach ($module_row_meta as $key => $value) {
                if (in_array($key, $meta_fields) || array_key_exists($key, (array) $module_row->meta)) {
                    $meta[] = [
                        'key' => $key,
                        'value' => $value,
                        'encrypted' => in_array($key, $encrypted_fields) ? 1 : 0
                    ];
                }
            }
            return $meta;
        }
    }

    /**
     * Gets the rules for adding/editing a module row
     */
    private function getRowRules(array $vars)
    {
        return [
            'user' => [
                'empty' => [
                    'rule' => ['isEmpty'],
                    'negate' => true,
                    'message' => Language::_('Porkbun.!error.user.valid', true),
                    'post_format' => 'trim'
                ]
            ],
            'apikey' => [
                'empty' => [
                    'rule' => ['isEmpty'],
                    'negate' => true,
                    'message' => Language::_('Porkbun.!error.apikey.valid', true),
                    'post_format' => 'trim'
                ]
            ],
            'secretapikey' => [
                'empty' => [
                    'rule' => ['isEmpty'],
                    'negate' => true,
                    'message' => Language::_('Porkbun.!error.secretapikey.valid', true),
                    'post_format' => 'trim'
                ],
                'valid' => [
                    'rule' => [[$this, 'validateConnection'], $vars['apikey'] ?? ''],
                    'message' => Language::_('Porkbun.!error.key.valid_connection', true),
                    'post_format' => 'trim'
                ]
            ]
        ];
    }

    /**
     * Validates that the given connection details are correct
     *
     * @param string $secretapikey The Secret API key
     * @param string $apikey The API key
     * @return bool True if valid
     */
    public function validateConnection($secretapikey, $apikey)
    {
        $api = $this->getApi($apikey, $secretapikey);
        $response = $api->submit('ping');
        return $response->status() == 'success';
    }

    // =========================================================================
    // API HELPER
    // =========================================================================

    /**
     * Initializes the PorkbunApi and returns an instance
     *
     * @param string|null $apikey The API key
     * @param string|null $secretapikey The secret API key
     * @return PorkbunApi
     */
    public function getApi($apikey = null, $secretapikey = null)
    {
        Loader::load(__DIR__ . DS . 'apis' . DS . 'porkbun_api.php');

        if (empty($apikey) || empty($secretapikey)) {
            if (($row = $this->getModuleRow())) {
                $apikey = $row->meta->apikey;
                $secretapikey = $row->meta->secretapikey;
            }
        }

        $this->api = new PorkbunApi($apikey, $secretapikey);
        return $this->api;
    }

    /**
     * Process API response, setting errors and logging the request
     */
    private function processResponse(PorkbunApi $api, PorkbunResponse $response)
    {
        $this->logRequest($api, $response);

        if ($api->httpcode != 200) {
            $this->Input->setErrors(['errors' => [Language::_('Porkbun.!error.api.http', true)]]);
            return;
        }

        if ($response->status() == 'error') {
            $errors = $response->errors();
            // Sanitize error messages from the API
            $sanitized = [];
            foreach ($errors as $error) {
                $sanitized[] = strip_tags((string) $error);
            }
            $this->Input->setErrors(['errors' => $sanitized]);
        }
    }

    /**
     * Logs the API request
     */
    private function logRequest(PorkbunApi $api, PorkbunResponse $response)
    {
        $last_request = $api->lastRequest();
        $url = $last_request['url'] ?? '';
        $args = $last_request['args'] ?? [];

        $this->log($url, serialize($args), 'input', true);
        $this->log($url, $response->raw(), 'output', $response->status() == 'success');
    }

    // =========================================================================
    // DOMAIN HELPER METHODS
    // =========================================================================

    /**
     * Returns the TLD of the given domain
     */
    private function getTld($domain)
    {
        return strstr($domain, '.');
    }

    /**
     * Retrieves the Porkbun module row
     */
    private function getRow()
    {
        if (!empty($this->row)) {
            return $this->row;
        }
        $module_rows = $this->getModuleRows();
        return $module_rows[0] ?? null;
    }

    /**
     * Gets the domain from a service
     */
    public function getServiceDomain($service)
    {
        if (isset($service->fields)) {
            foreach ($service->fields as $service_field) {
                if ($service_field->key == 'domain') {
                    return $service_field->value;
                }
            }
        }
        return $service->name ?? '';
    }

    /**
     * Sanitizes a value for safe use in a URL path segment.
     * Removes path traversal characters and encodes the value.
     *
     * @param string $value The value to sanitize
     * @return string The sanitized value
     */
    private function sanitizeUrlSegment($value)
    {
        // Remove any path traversal attempts
        $value = str_replace(['..', '/', '\\', "\0"], '', (string) $value);
        return rawurlencode($value);
    }

    /**
     * Validates that a DNS record type is one of the allowed types.
     *
     * @param string $type The DNS record type
     * @return bool True if valid
     */
    private function isValidDnsType($type)
    {
        $allowed = ['A', 'MX', 'CNAME', 'ALIAS', 'TXT', 'NS', 'AAAA', 'SRV', 'TLSA', 'CAA', 'HTTPS', 'SVCB', 'SSHFP'];
        return in_array(strtoupper((string) $type), $allowed, true);
    }

    /**
     * Validates an IP address (IPv4 or IPv6).
     *
     * @param string $ip The IP address to validate
     * @return bool True if valid
     */
    private function isValidIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    /**
     * Validates service info
     */
    public function validateService($package, array $vars = null)
    {
        $rules = [];

        if (!empty($vars['domain'])) {
            $vars['domain'] = strtolower(trim($vars['domain']));
        }

        $domain_pattern = '/^[a-z0-9]([a-z0-9\-\.]*[a-z0-9])?$/';
        $ns_pattern = '/^(?=.{1,253}$)([a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i';

        if (isset($vars['domain'])) {
            $rules['domain'] = [
                'empty' => [
                    'rule' => ['isEmpty'],
                    'negate' => true,
                    'message' => Language::_('Porkbun.!error.domain.valid', true),
                    'post_format' => 'trim'
                ],
                'format' => [
                    'rule' => ['matches', $domain_pattern],
                    'message' => Language::_('Porkbun.!error.domain.format', true)
                ]
            ];
        }

        for ($i = 1; $i <= 5; $i++) {
            if (!empty($vars['ns' . $i])) {
                $rules['ns' . $i] = [
                    'format' => [
                        'rule' => ['matches', $ns_pattern],
                        'message' => Language::_('Porkbun.!error.nameserver.format', true)
                    ]
                ];
            }
        }

        if (isset($vars['transfer']) && ($vars['transfer'] == '1' || $vars['transfer'] === true)) {
            $rules['auth'] = [
                'empty' => [
                    'rule' => ['isEmpty'],
                    'negate' => true,
                    'message' => Language::_('Porkbun.!error.epp.empty', true),
                    'post_format' => 'trim'
                ]
            ];
        }

        if (!empty($rules)) {
            $this->Input->setRules($rules);
            return $this->Input->validates($vars);
        }

        return true;
    }

    // =========================================================================
    // DOMAIN AVAILABILITY & REGISTRATION
    // =========================================================================

    /**
     * Verifies that the provided domain name is available
     */
    public function checkAvailability($domain, $module_row_id = null)
    {
        $row = $this->getModuleRow($module_row_id);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        $result = $api->submit('domain/checkDomain/' . $domain);
        $this->processResponse($api, $result);

        if ($result->status() === 'error') {
            return false;
        }

        $response = $result->response();
        if (isset($response->response->avail)) {
            return strtolower($response->response->avail) === 'yes';
        }

        return false;
    }

    /**
     * Verifies that the provided domain name is available for transfer
     */
    public function checkTransferAvailability($domain, $module_row_id = null)
    {
        // If the domain is available for registration, it's NOT available for transfer
        return !$this->checkAvailability($domain, $module_row_id);
    }

    /**
     * Verifies if a domain of the provided TLD can be registered by the provided term
     */
    public function isValidTerm($tld, $term, $transfer = false)
    {
        // Porkbun registers for minimum duration allowed by registry (usually 1 year)
        if ($term > 10 || ($transfer && $term > 1)) {
            return false;
        }
        return true;
    }

    /**
     * Register a domain via Porkbun API
     */
    public function registerDomain($domain, $module_row_id = null, array $vars = [])
    {
        $row = $this->getModuleRow($module_row_id);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        // First check the domain to get the cost
        $check = $api->submit('domain/checkDomain/' . $domain);
        $check_res = $check->response();

        if (!isset($check_res->response->price)) {
            $this->Input->setErrors(['errors' => ['Unable to determine domain pricing.']]);
            return false;
        }

        // Cost in pennies (price * 100) * minDuration
        $price = (float) $check_res->response->price;
        $minDuration = (int) ($check_res->response->minDuration ?? 1);
        $years = $vars['years'] ?? 1;
        $cost = (int) round($price * 100 * max($years, $minDuration));

        $args = [
            'cost' => $cost,
            'agreeToTerms' => 'yes'
        ];

        $response = $api->submit('domain/create/' . $domain, $args);
        $this->processResponse($api, $response);

        // Set nameservers if provided
        if ($response->status() == 'success' && !empty($vars['ns'])) {
            $ns_args = ['ns' => array_values($vars['ns'])];
            $api->submit('domain/updateNs/' . $domain, $ns_args);
        }

        return $response->status() == 'success';
    }

    /**
     * Transfer a domain
     * Note: Porkbun does not currently support domain transfer via API
     */
    public function transferDomainIN($domain, $module_row_id = null, array $vars = [])
    {
        $this->Input->setErrors(['errors' => [Language::_('Porkbun.!error.transfer.unsupported', true)]]);
        return false;
    }

    /**
     * Renew a domain
     * Note: Porkbun does not currently expose a renewal endpoint in their API
     * Domains should be set to auto-renew
     */
    public function renewDomain($domain, $module_row_id = null, array $vars = [])
    {
        // Porkbun doesn't have a renewal API endpoint
        // Set auto-renew on instead
        $row = $this->getModuleRow($module_row_id);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        $response = $api->submit('domain/updateAutoRenew/' . $domain, ['status' => 'on']);
        $this->processResponse($api, $response);

        return $response->status() == 'success';
    }

    // =========================================================================
    // NAMESERVERS
    // =========================================================================

    /**
     * Gets a list of name server data associated with a domain
     */
    public function getDomainNameServers($domain, $module_row_id = null)
    {
        $row = $this->getModuleRow($module_row_id);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        $response = $api->submit('domain/getNs/' . $domain);
        $this->processResponse($api, $response);
        $result = $response->response();

        $nameservers = [];
        if (isset($result->ns) && is_array($result->ns)) {
            foreach ($result->ns as $ns) {
                $nameservers[] = [
                    'url' => trim($ns),
                    'ips' => []
                ];
            }
        }

        return $nameservers;
    }

    /**
     * Assign new name servers to a domain
     */
    public function setDomainNameservers($domain, $module_row_id = null, array $vars = [])
    {
        $ns_list = [];
        foreach ($vars as $ns) {
            if (!empty($ns)) {
                $ns_list[] = strtolower(trim($ns));
            }
        }

        if (count($ns_list) < 2) {
            $this->Input->setErrors([
                'nameserver' => [
                    'min' => Language::_('Porkbun.!error.nameserver.minimum', true)
                ]
            ]);
            return false;
        }

        $row = $this->getModuleRow($module_row_id);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        $response = $api->submit('domain/updateNs/' . $domain, ['ns' => $ns_list]);
        $this->processResponse($api, $response);

        return $response->status() == 'success';
    }

    // =========================================================================
    // DOMAIN INFO & LOCK
    // =========================================================================

    /**
     * Get domain info by listing all domains and finding the match
     */
    public function getDomainInfo($domain, $module_row_id = null)
    {
        $row = $this->getModuleRow($module_row_id);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        $response = $api->submit('domain/listAll');
        $result = $response->response();

        if (isset($result->domains) && is_array($result->domains)) {
            foreach ($result->domains as $d) {
                if (strtolower($d->domain) === strtolower($domain)) {
                    return [
                        'domain' => $d->domain,
                        'status' => $d->status ?? 'Unknown',
                        'createDate' => $d->createDate ?? null,
                        'expireDate' => $d->expireDate ?? null,
                        'securityLock' => $d->securityLock ?? '0',
                        'whoisPrivacy' => $d->whoisPrivacy ?? '0',
                        'autoRenew' => $d->autoRenew ?? 0,
                    ];
                }
            }
        }
        return [];
    }

    public function getDomainIsLocked($domain, $module_row_id = null)
    {
        $info = $this->getDomainInfo($domain, $module_row_id);
        return isset($info['securityLock']) && $info['securityLock'] === '1';
    }

    public function lockDomain($domain, $module_row_id = null)
    {
        // Porkbun doesn't expose a lock API endpoint
        // Security lock is managed through the dashboard
        return false;
    }

    public function unlockDomain($domain, $module_row_id = null)
    {
        // Porkbun doesn't expose an unlock API endpoint
        // Security lock is managed through the dashboard
        return false;
    }

    public function getExpirationDate($service, $format = 'Y-m-d H:i:s')
    {
        Loader::loadHelpers($this, ['Date']);
        $domain = $this->getServiceDomain($service);
        $info = $this->getDomainInfo($domain, $service->module_row_id ?? null);

        if (!empty($info['expireDate'])) {
            return $this->Date->format($format, $info['expireDate']);
        }
        return false;
    }

    public function getRegistrationDate($service, $format = 'Y-m-d H:i:s')
    {
        Loader::loadHelpers($this, ['Date']);
        $domain = $this->getServiceDomain($service);
        $info = $this->getDomainInfo($domain, $service->module_row_id ?? null);

        if (!empty($info['createDate'])) {
            return $this->Date->format($format, $info['createDate']);
        }
        return false;
    }

    // =========================================================================
    // SERVICE MANAGEMENT
    // =========================================================================

    /**
     * Adds the service to the remote server
     */
    public function addService($package, array $vars = null, $parent_package = null, $parent_service = null, $status = 'pending')
    {
        $row = $this->getModuleRow();
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);

        if (isset($vars['domain'])) {
            $tld = $this->getTld($vars['domain']);
            $vars['domain'] = trim($vars['domain']);
        }

        if (isset($vars['use_module']) && $vars['use_module'] == 'true') {
            if ($package->meta->type == 'domain') {
                $vars['years'] = 1;
                foreach ($package->pricing as $pricing) {
                    if ($pricing->id == $vars['pricing_id']) {
                        $vars['years'] = $pricing->term;
                        break;
                    }
                }

                // Prepare nameservers
                $nameservers = [];
                for ($i = 1; $i <= 5; $i++) {
                    if (isset($vars['ns' . $i]) && !empty($vars['ns' . $i])) {
                        $nameservers[] = $vars['ns' . $i];
                    }
                }
                $vars['ns'] = $nameservers;

                if (!$this->isValidTerm($tld, $vars['years'] ?? 1, !empty($vars['auth']))) {
                    $this->Input->setErrors(
                        ['term' => ['invalid' => Language::_('Porkbun.!error.invalid_term', true)]]
                    );
                    return;
                }

                // Handle transfer
                if (isset($vars['auth']) && $vars['auth']) {
                    $this->transferDomainIN($vars['domain'], $row->id, $vars);
                } else {
                    // Check availability
                    if (!$this->checkAvailability($vars['domain'], $row->id)) {
                        $this->Input->setErrors(
                            ['domain' => ['unavailable' => Language::_('Porkbun.!error.domain.unavailable', true)]]
                        );
                        return;
                    }
                    $this->registerDomain($vars['domain'], $row->id, $vars);
                }
            }
        }

        $meta = [];
        $fields = ['domain', 'auth', 'ns1', 'ns2', 'ns3', 'ns4', 'ns5'];
        foreach ($vars as $key => $value) {
            if (in_array($key, $fields)) {
                $meta[] = [
                    'key' => $key,
                    'value' => $value,
                    'encrypted' => 0
                ];
            }
        }

        return $meta;
    }

    /**
     * Edits the service on the remote server
     */
    public function editService($package, $service, array $vars = [], $parent_package = null, $parent_service = null)
    {
        $domain = $this->getServiceDomain($service);

        // Only interact with the module if use_module is enabled
        $use_module = ($vars['use_module'] ?? 'true') == 'true';

        if ($use_module) {
            // Renew
            $renew = isset($vars['renew']) ? (int) $vars['renew'] : 0;
            if ($renew > 0) {
                $this->renewService($package, $service, $parent_package, $parent_service, $renew);
                unset($vars['renew']);
            }

            // Update nameservers
            if (!empty($vars['ns1']) && !empty($vars['ns2'])) {
                $ns = [];
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($vars['ns' . $i])) {
                        $ns[] = $vars['ns' . $i];
                    }
                }
                if (!empty($ns)) {
                    $this->setDomainNameservers($domain, $service->module_row_id, $ns);
                }
            }
        }

        return null;
    }

    /**
     * Cancels the service on the remote server.
     * Sets auto-renew to off so the domain expires naturally.
     * Note: Blesta only calls this when "Use Module" is checked.
     */
    public function cancelService($package, $service, $parent_package = null, $parent_service = null)
    {
        if ($package->meta->type == 'domain') {
            $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
            $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
            $domain = $this->getServiceDomain($service);

            // Disable auto-renew so domain expires
            $api->submit('domain/updateAutoRenew/' . $domain, ['status' => 'off']);
        }
        return null;
    }

    /**
     * Suspends the service on the remote server.
     * Note: Blesta only calls this when "Use Module" is checked.
     */
    public function suspendService($package, $service, $parent_package = null, $parent_service = null)
    {
        return $this->cancelService($package, $service, $parent_package, $parent_service);
    }

    /**
     * Unsuspends the service on the remote server.
     * Note: Blesta only calls this when "Use Module" is checked.
     */
    public function unsuspendService($package, $service, $parent_package = null, $parent_service = null)
    {
        if ($package->meta->type == 'domain') {
            $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
            $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
            $domain = $this->getServiceDomain($service);

            // Re-enable auto-renew on unsuspend
            $api->submit('domain/updateAutoRenew/' . $domain, ['status' => 'on']);
        }
        return null;
    }

    /**
     * Allows the module to perform an action when the service is ready to renew
     */
    public function renewService($package, $service, $parent_package = null, $parent_service = null, $years = null)
    {
        if ($package->meta->type == 'domain') {
            $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
            $domain = $this->getServiceDomain($service);

            if (!$years) {
                foreach ($package->pricing as $pricing) {
                    if ($pricing->id == $service->pricing_id) {
                        $years = $pricing->term;
                        break;
                    }
                }
            }

            $this->renewDomain($domain, $row->id, ['years' => $years]);
        }
        return null;
    }

    // =========================================================================
    // PACKAGE MANAGEMENT
    // =========================================================================

    public function addPackage(array $vars = null)
    {
        $meta = [];
        if (isset($vars['meta']) && is_array($vars['meta'])) {
            foreach ($vars['meta'] as $key => $value) {
                $meta[] = ['key' => $key, 'value' => $value, 'encrypted' => 0];
            }
        }
        return $meta;
    }

    public function editPackage($package, array $vars = null)
    {
        return $this->addPackage($vars);
    }

    public function deletePackage($package)
    {
        return true;
    }

    public function getPackageFields($vars = null)
    {
        Loader::loadHelpers($this, ['Html']);
        $fields = new ModuleFields();

        $tld_options = $fields->label(Language::_('Porkbun.package_fields.tld_options', true));
        $tlds = $this->getTlds();
        sort($tlds);
        foreach ($tlds as $tld) {
            $tld_label = $fields->label($tld, 'tld_' . $tld);
            $tld_options->attach(
                $fields->fieldCheckbox(
                    'meta[tlds][]',
                    $tld,
                    (isset($vars->meta['tlds']) && in_array($tld, $vars->meta['tlds'])),
                    ['id' => 'tld_' . $tld],
                    $tld_label
                )
            );
        }
        $fields->setField($tld_options);

        return $fields;
    }

    // =========================================================================
    // SERVICE FIELDS
    // =========================================================================

    public function getAdminEditFields($package, $vars = null)
    {
        Loader::loadHelpers($this, ['Form', 'Html']);
        $fields = new ModuleFields();

        if ($package->meta->type == 'domain') {
            $fields->setField(
                $fields->label(Language::_('Porkbun.service_info.domain', true), 'domain')
                    ->attach($fields->fieldText('domain', $vars->domain ?? null, ['id' => 'domain']))
            );
            for ($i = 1; $i <= 5; $i++) {
                $fields->setField(
                    $fields->label(Language::_('Porkbun.nameserver.ns' . $i, true), 'ns' . $i)
                        ->attach($fields->fieldText('ns' . $i, $vars->{'ns' . $i} ?? null, ['id' => 'ns' . $i]))
                );
            }
        }

        return $fields;
    }

    public function getAdminAddFields($package, $vars = null)
    {
        Loader::loadHelpers($this, ['Form', 'Html']);

        if ($package->meta->type == 'domain') {
            if (!isset($vars->ns1) && isset($package->meta->ns)) {
                $i = 1;
                foreach ($package->meta->ns as $ns) {
                    $vars->{'ns' . $i++} = $ns;
                }
            }

            $fields = Configure::get('Porkbun.transfer_fields');
            $fields['transfer'] = [
                'label' => Language::_('Porkbun.domain.DomainAction', true),
                'type' => 'radio',
                'value' => '0',
                'options' => ['0' => 'Register', '1' => 'Transfer'],
            ];
            $fields['auth'] = ['label' => Language::_('Porkbun.transfer.EPPCode', true), 'type' => 'text'];

            $module_fields = $this->arrayToModuleFields(
                array_merge($fields, Configure::get('Porkbun.nameserver_fields')),
                null,
                $vars
            );

            $module_fields->setHtml("
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        if ($('input[name=\"transfer\"]:checked').val() == '1') {
                            $('#auth').closest('li').show();
                        } else {
                            $('#auth').closest('li').hide();
                        }
                        $('input[name=\"transfer\"]').change(function() {
                            if ($('input[name=\"transfer\"]:checked').val() == '1') {
                                $('#auth').closest('li').show();
                            } else {
                                $('#auth').closest('li').hide();
                            }
                        });
                    });
                </script>
            ");

            return $module_fields;
        }

        return new ModuleFields();
    }

    public function getClientAddFields($package, $vars = null)
    {
        if (isset($vars->domain)) {
            $vars->domain = $vars->domain;
        }

        if ($package->meta->type == 'domain') {
            if (!isset($vars->ns1) && isset($package->meta->ns)) {
                $i = 1;
                foreach ($package->meta->ns as $ns) {
                    $vars->{'ns' . $i++} = $ns;
                }
            }

            if ((isset($vars->transfer) && $vars->transfer) || isset($vars->auth)) {
                $fields = Configure::get('Porkbun.transfer_fields');
                $fields['domain']['type'] = 'hidden';
                $fields['domain']['label'] = null;
                return $this->arrayToModuleFields($fields, null, $vars);
            } else {
                $fields = array_merge(
                    Configure::get('Porkbun.nameserver_fields'),
                    Configure::get('Porkbun.domain_fields')
                );
                $fields['domain']['type'] = 'hidden';
                $fields['domain']['label'] = null;
                return $this->arrayToModuleFields($fields, null, $vars);
            }
        }
        return new ModuleFields();
    }

    // =========================================================================
    // SERVICE INFO VIEWS
    // =========================================================================

    public function getAdminServiceInfo($service, $package)
    {
        $vars = new stdClass();
        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $domain = $this->getServiceDomain($service);
        $info = $this->getDomainInfo($domain, $service->module_row_id ?? $package->module_row);

        $vars->domain = $domain;
        $vars->status = $info['status'] ?? 'Unknown';
        $vars->expiration = $info['expireDate'] ?? '';
        $vars->locked = (isset($info['securityLock']) && $info['securityLock'] == '1') ? 'Yes' : 'No';
        $vars->auto_renew = (isset($info['autoRenew']) && $info['autoRenew']) ? 'Yes' : 'No';

        $this->view = new View('admin_service_info', 'default');
        Loader::loadHelpers($this, ['Html', 'Date']);
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        $this->view->set('vars', $vars);

        return $this->view->fetch();
    }

    public function getClientServiceInfo($service, $package)
    {
        $vars = new stdClass();
        $domain = $this->getServiceDomain($service);
        $info = $this->getDomainInfo($domain, $service->module_row_id ?? $package->module_row);

        $vars->domain = $domain;
        $vars->status = $info['status'] ?? 'Unknown';
        $vars->expiration = $info['expireDate'] ?? '';
        $vars->locked = (isset($info['securityLock']) && $info['securityLock'] == '1') ? 'Yes' : 'No';

        $this->view = new View('client_service_info', 'default');
        Loader::loadHelpers($this, ['Html', 'Date']);
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        $this->view->set('vars', $vars);

        return $this->view->fetch();
    }

    // =========================================================================
    // TABS
    // =========================================================================

    public function getAdminServiceTabs($service)
    {
        return [
            'tabNameservers' => Language::_('Porkbun.tab_nameservers.title', true),
            'tabSettings' => Language::_('Porkbun.tab_settings.title', true),
            'tabDnsRecords' => Language::_('Porkbun.tab_dnsrecords.title_list', true),
            'tabDnssec' => Language::_('Porkbun.tab_dnssec.title', true),
            'tabGlueRecords' => Language::_('Porkbun.tab_gluerecords.title', true),
            'tabUrlForward' => Language::_('Porkbun.tab_urlforward.title', true),
            'tabAdminActions' => Language::_('Porkbun.tab_adminactions.title', true),
        ];
    }

    public function getClientServiceTabs($service)
    {
        return [
            'tabClientNameservers' => ['name' => Language::_('Porkbun.tab_nameservers.title', true), 'icon' => 'fas fa-server'],
            'tabClientSettings' => ['name' => Language::_('Porkbun.tab_settings.title', true), 'icon' => 'fas fa-cog'],
            'tabClientDnsRecords' => ['name' => Language::_('Porkbun.tab_dnsrecords.title_list', true), 'icon' => 'fas fa-sitemap'],
            'tabClientDnssec' => ['name' => Language::_('Porkbun.tab_dnssec.title', true), 'icon' => 'fas fa-lock'],
            'tabClientGlueRecords' => ['name' => Language::_('Porkbun.tab_gluerecords.title', true), 'icon' => 'fas fa-hdd'],
            'tabClientUrlForward' => ['name' => Language::_('Porkbun.tab_urlforward.title', true), 'icon' => 'fas fa-share'],
        ];
    }

    // --- Nameservers Tab ---
    public function tabNameservers($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageNameservers('tab_nameservers', $package, $service, $get, $post, $files);
    }

    public function tabClientNameservers($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageNameservers('tab_client_nameservers', $package, $service, $get, $post, $files);
    }

    private function manageNameservers($view, $package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View($view, 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $domain = $this->getServiceDomain($service);

        if (!empty($post)) {
            $ns = $post['ns'] ?? [];
            if (!empty($ns)) {
                $this->setDomainNameservers($domain, $service->module_row_id, $ns);
            }
            $vars->ns = $ns;
        } else {
            $nameservers = $this->getDomainNameServers($domain, $service->module_row_id);
            $ns = [];
            foreach ($nameservers as $ns_entry) {
                $ns[] = $ns_entry['url'];
            }
            $vars->ns = $ns;
        }

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // --- Settings Tab ---
    public function tabSettings($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageSettings('tab_settings', $package, $service, $get, $post, $files);
    }

    public function tabClientSettings($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageSettings('tab_client_settings', $package, $service, $get, $post, $files);
    }

    private function manageSettings($view, $package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View($view, 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
        $domain = $this->getServiceDomain($service);

        if (!empty($post)) {
            // Update auto-renew (strict validation)
            if (isset($post['auto_renew']) && in_array($post['auto_renew'], ['on', 'off'], true)) {
                $api->submit('domain/updateAutoRenew/' . $domain, [
                    'status' => $post['auto_renew']
                ]);
            }
        }

        // Get current domain info
        $info = $this->getDomainInfo($domain, $service->module_row_id);
        $vars->auto_renew = (isset($info['autoRenew']) && $info['autoRenew']) ? 'on' : 'off';
        $vars->security_lock = (isset($info['securityLock']) && $info['securityLock'] == '1') ? 'yes' : 'no';
        $vars->whois_privacy = (isset($info['whoisPrivacy']) && $info['whoisPrivacy'] == '1') ? 'yes' : 'no';

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // --- DNS Records Tab ---
    public function tabDnsRecords($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageDnsRecords('tab_dnsrecords', $package, $service, $get, $post, $files);
    }

    public function tabClientDnsRecords($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageDnsRecords('tab_client_dnsrecords', $package, $service, $get, $post, $files);
    }

    private function manageDnsRecords($view, $package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View($view, 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
        $domain = $this->getServiceDomain($service);

        $vars->record_types = Configure::get('Porkbun.dns_record_types');

        if (!empty($post)) {
            // Handle add record
            if (!empty($post['new_type']) && !empty($post['new_content'])) {
                if ($this->isValidDnsType($post['new_type'])) {
                    $args = [
                        'type' => strtoupper($post['new_type']),
                        'content' => $post['new_content'],
                        'name' => $post['new_name'] ?? '',
                        'ttl' => max(600, min(86400, (int) ($post['new_ttl'] ?? 600))),
                        'prio' => $post['new_prio'] ?? ''
                    ];
                    $response = $api->submit('dns/create/' . $domain, $args);
                    $this->processResponse($api, $response);
                }
            }

            // Handle edit record
            if (!empty($post['edit_id']) && !empty($post['edit_type']) && !empty($post['edit_content'])) {
                if ($this->isValidDnsType($post['edit_type'])) {
                    $record_id = $this->sanitizeUrlSegment($post['edit_id']);
                    $args = [
                        'type' => strtoupper($post['edit_type']),
                        'content' => $post['edit_content'],
                        'name' => $post['edit_name'] ?? '',
                        'ttl' => max(600, min(86400, (int) ($post['edit_ttl'] ?? 600))),
                        'prio' => $post['edit_prio'] ?? ''
                    ];
                    $response = $api->submit('dns/edit/' . $domain . '/' . $record_id, $args);
                    $this->processResponse($api, $response);
                }
            }

            // Handle delete record
            if (!empty($post['delete_id'])) {
                $record_id = $this->sanitizeUrlSegment($post['delete_id']);
                $response = $api->submit('dns/delete/' . $domain . '/' . $record_id);
                $this->processResponse($api, $response);
            }
        }

        // Fetch existing records
        $response = $api->submit('dns/retrieve/' . $domain);
        $result = $response->response();

        $records = [];
        if (isset($result->records) && is_array($result->records)) {
            foreach ($result->records as $record) {
                $records[] = [
                    'id' => $record->id ?? '',
                    'name' => $record->name ?? '',
                    'type' => $record->type ?? '',
                    'content' => $record->content ?? '',
                    'ttl' => $record->ttl ?? '600',
                    'prio' => $record->prio ?? ''
                ];
            }
        }

        $vars->records = $records;
        $vars->domain = $domain;

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // --- URL Forwarding Tab ---
    public function tabUrlForward($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageUrlForward('tab_urlforward', $package, $service, $get, $post, $files);
    }

    public function tabClientUrlForward($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageUrlForward('tab_client_urlforward', $package, $service, $get, $post, $files);
    }

    private function manageUrlForward($view, $package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View($view, 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
        $domain = $this->getServiceDomain($service);

        if (!empty($post)) {
            // Add new forward
            if (!empty($post['new_location'])) {
                $fwd_type = in_array(($post['new_type'] ?? ''), ['temporary', 'permanent']) ? $post['new_type'] : 'temporary';
                $args = [
                    'subdomain' => $post['new_subdomain'] ?? '',
                    'location' => $post['new_location'],
                    'type' => $fwd_type,
                    'includePath' => ($post['new_include_path'] ?? 'no') === 'yes' ? 'yes' : 'no',
                    'wildcard' => ($post['new_wildcard'] ?? 'no') === 'yes' ? 'yes' : 'no'
                ];
                $api->submit('domain/addUrlForward/' . $domain, $args);
            }

            // Delete forward
            if (!empty($post['delete_id'])) {
                $fwd_id = $this->sanitizeUrlSegment($post['delete_id']);
                $api->submit('domain/deleteUrlForward/' . $domain . '/' . $fwd_id);
            }
        }

        // Fetch existing forwards
        $response = $api->submit('domain/getUrlForwarding/' . $domain);
        $result = $response->response();

        $forwards = [];
        if (isset($result->forwards) && is_array($result->forwards)) {
            foreach ($result->forwards as $fwd) {
                $forwards[] = [
                    'id' => $fwd->id ?? '',
                    'subdomain' => $fwd->subdomain ?? '',
                    'location' => $fwd->location ?? '',
                    'type' => $fwd->type ?? 'temporary',
                    'includePath' => $fwd->includePath ?? 'no',
                    'wildcard' => $fwd->wildcard ?? 'no'
                ];
            }
        }

        $vars->forwards = $forwards;
        $vars->domain = $domain;

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // --- DNSSEC Tab ---
    public function tabDnssec($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageDnssec('tab_dnssec', $package, $service, $get, $post, $files);
    }

    public function tabClientDnssec($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageDnssec('tab_client_dnssec', $package, $service, $get, $post, $files);
    }

    private function manageDnssec($view, $package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View($view, 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
        $domain = $this->getServiceDomain($service);

        if (!empty($post)) {
            // Add DNSSEC record
            if (!empty($post['new_keytag']) && !empty($post['new_alg']) && !empty($post['new_digest_type']) && !empty($post['new_digest'])) {
                $keytag = (string) (int) $post['new_keytag']; // Must be numeric
                $alg = (string) (int) $post['new_alg'];
                $digestType = (string) (int) $post['new_digest_type'];
                $digest = preg_replace('/[^a-fA-F0-9]/', '', $post['new_digest']); // Hex only

                if ($keytag > 0 && $alg > 0 && $digestType > 0 && strlen($digest) > 0) {
                    $args = [
                        'keyTag' => $keytag,
                        'alg' => $alg,
                        'digestType' => $digestType,
                        'digest' => $digest,
                        'maxSigLife' => $post['new_max_sig_life'] ?? '',
                        'keyDataFlags' => $post['new_key_data_flags'] ?? '',
                        'keyDataProtocol' => $post['new_key_data_protocol'] ?? '',
                        'keyDataAlgo' => $post['new_key_data_algo'] ?? '',
                        'keyDataPubKey' => $post['new_key_data_pubkey'] ?? ''
                    ];
                    $response = $api->submit('dns/createDnssecRecord/' . $domain, $args);
                    $this->processResponse($api, $response);
                }
            }

            // Delete DNSSEC record
            if (!empty($post['delete_keytag'])) {
                $keytag = $this->sanitizeUrlSegment((string) (int) $post['delete_keytag']);
                $response = $api->submit('dns/deleteDnssecRecord/' . $domain . '/' . $keytag);
                $this->processResponse($api, $response);
            }
        }

        // Fetch existing DNSSEC records
        $response = $api->submit('dns/getDnssecRecords/' . $domain);
        $result = $response->response();

        $records = [];
        if (isset($result->records) && (is_object($result->records) || is_array($result->records))) {
            foreach ($result->records as $keyTag => $record) {
                $records[] = [
                    'keyTag' => is_object($record) ? ($record->keyTag ?? $keyTag) : ($record['keyTag'] ?? $keyTag),
                    'alg' => is_object($record) ? ($record->alg ?? '') : ($record['alg'] ?? ''),
                    'digestType' => is_object($record) ? ($record->digestType ?? '') : ($record['digestType'] ?? ''),
                    'digest' => is_object($record) ? ($record->digest ?? '') : ($record['digest'] ?? '')
                ];
            }
        }

        $vars->records = $records;
        $vars->domain = $domain;

        // DS Algorithm options
        $vars->algorithms = [
            '' => '-- Select --',
            '3' => '3 - DSA/SHA-1',
            '5' => '5 - RSA/SHA-1',
            '6' => '6 - DSA-NSEC3-SHA1',
            '7' => '7 - RSASHA1-NSEC3-SHA1',
            '8' => '8 - RSA/SHA-256',
            '10' => '10 - RSA/SHA-512',
            '12' => '12 - GOST R 34.10-2001',
            '13' => '13 - ECDSA/SHA-256',
            '14' => '14 - ECDSA/SHA-384',
            '15' => '15 - Ed25519',
            '16' => '16 - Ed448',
        ];

        // Digest Type options
        $vars->digest_types = [
            '' => '-- Select --',
            '1' => '1 - SHA-1',
            '2' => '2 - SHA-256',
            '3' => '3 - GOST R 34.11-94',
            '4' => '4 - SHA-384',
        ];

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // --- Glue Records Tab ---
    public function tabGlueRecords($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageGlueRecords('tab_gluerecords', $package, $service, $get, $post, $files);
    }

    public function tabClientGlueRecords($package, $service, array $get = null, array $post = null, array $files = null)
    {
        return $this->manageGlueRecords('tab_client_gluerecords', $package, $service, $get, $post, $files);
    }

    private function manageGlueRecords($view, $package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View($view, 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $row = $this->getModuleRow($service->module_row_id ?? $package->module_row);
        $api = $this->getApi($row->meta->apikey, $row->meta->secretapikey);
        $domain = $this->getServiceDomain($service);

        if (!empty($post)) {
            // Create glue record
            if (!empty($post['new_subdomain']) && !empty($post['new_ips'])) {
                $subdomain = preg_replace('/[^a-z0-9\-]/i', '', $post['new_subdomain']);
                $raw_ips = array_filter(array_map('trim', explode("\n", $post['new_ips'])));
                $ips = array_values(array_filter($raw_ips, [$this, 'isValidIp']));
                if (!empty($subdomain) && !empty($ips)) {
                    $response = $api->submit('domain/createGlue/' . $domain . '/' . $this->sanitizeUrlSegment($subdomain), [
                        'ips' => $ips
                    ]);
                    $this->processResponse($api, $response);
                }
            }

            // Update glue record
            if (!empty($post['edit_subdomain']) && !empty($post['edit_ips'])) {
                $subdomain = preg_replace('/[^a-z0-9\-]/i', '', $post['edit_subdomain']);
                $raw_ips = array_filter(array_map('trim', explode("\n", $post['edit_ips'])));
                $ips = array_values(array_filter($raw_ips, [$this, 'isValidIp']));
                if (!empty($subdomain) && !empty($ips)) {
                    $response = $api->submit('domain/updateGlue/' . $domain . '/' . $this->sanitizeUrlSegment($subdomain), [
                        'ips' => $ips
                    ]);
                    $this->processResponse($api, $response);
                }
            }

            // Delete glue record
            if (!empty($post['delete_subdomain'])) {
                $subdomain = preg_replace('/[^a-z0-9\-]/i', '', $post['delete_subdomain']);
                if (!empty($subdomain)) {
                    $response = $api->submit('domain/deleteGlue/' . $domain . '/' . $this->sanitizeUrlSegment($subdomain));
                    $this->processResponse($api, $response);
                }
            }
        }

        // Fetch existing glue records
        $response = $api->submit('domain/getGlue/' . $domain);
        $result = $response->response();

        $records = [];
        if (isset($result->hosts) && is_array($result->hosts)) {
            foreach ($result->hosts as $host) {
                if (is_array($host) && count($host) >= 2) {
                    $hostname = $host[0];
                    $ipData = (object) $host[1];
                    $ips = [];
                    if (isset($ipData->v4) && is_array($ipData->v4)) {
                        $ips = array_merge($ips, $ipData->v4);
                    }
                    if (isset($ipData->v6) && is_array($ipData->v6)) {
                        $ips = array_merge($ips, $ipData->v6);
                    }
                    // Extract subdomain from full hostname
                    $subdomain = str_replace('.' . $domain, '', $hostname);
                    $records[] = [
                        'hostname' => $hostname,
                        'subdomain' => $subdomain,
                        'ips' => $ips
                    ];
                }
            }
        }

        $vars->records = $records;
        $vars->domain = $domain;

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // --- Admin Actions Tab ---
    public function tabAdminActions($package, $service, array $get = null, array $post = null, array $files = null)
    {
        $vars = new stdClass();
        $this->view = new View('tab_admin_actions', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView(self::$defaultModuleView);
        Loader::loadHelpers($this, ['Form', 'Html']);

        $domain = $this->getServiceDomain($service);

        if (!empty($post['action']) && $post['action'] === 'sync_date') {
            Loader::loadModels($this, ['Services']);
            $info = $this->getDomainInfo($domain, $service->module_row_id);

            if (!empty($info['expireDate'])) {
                $this->Services->edit($service->id, ['date_renews' => $info['expireDate']], true);
            }
        }

        $this->view->set('vars', $vars);
        return $this->view->fetch();
    }

    // =========================================================================
    // TLD LIST
    // =========================================================================

    /**
     * Returns a list of supported TLDs
     */
    public function getTlds($module_row_id = null)
    {
        // Try to fetch TLDs dynamically from Porkbun pricing API (no auth needed)
        $tlds = $this->getTldsFromApi();
        if (!empty($tlds)) {
            return $tlds;
        }

        // Fallback to hardcoded list if API is unreachable
        return $this->getDefaultTlds();
    }

    /**
     * Fetches all supported TLDs from Porkbun pricing API
     * Results are cached for 24 hours to avoid excessive API calls
     *
     * @return array List of TLDs prefixed with dots
     */
    private function getTldsFromApi()
    {
        // Check cache first
        $cache_file = CACHEDIR . 'porkbun_tlds.cache';
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
            $cached = is_readable($cache_file) ? file_get_contents($cache_file) : false;
            if ($cached !== false) {
                $tlds = json_decode($cached, true);
                if (!empty($tlds)) {
                    return $tlds;
                }
            }
        }

        // Fetch from Porkbun pricing API (no authentication required)
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.porkbun.com/api/json/v3/pricing/get',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || empty($response)) {
            return [];
        }

        $data = json_decode($response, true);
        if (!isset($data['status']) || $data['status'] !== 'SUCCESS' || empty($data['pricing'])) {
            return [];
        }

        $tlds = [];
        foreach (array_keys($data['pricing']) as $tld) {
            $tlds[] = '.' . ltrim($tld, '.');
        }
        sort($tlds);

        // Cache results for 24 hours
        if (defined('CACHEDIR') && is_writable(CACHEDIR)) {
            file_put_contents($cache_file, json_encode($tlds), LOCK_EX);
        }

        return $tlds;
    }

    /**
     * Returns a hardcoded fallback list of common TLDs
     *
     * @return array
     */
    private function getDefaultTlds()
    {
        return [
            '.aaa', '.aarp', '.abarth', '.abb', '.abbott', '.abbvie', '.abc', '.able',
            '.ac', '.academy', '.accenture', '.accountant', '.accountants', '.aco',
            '.actor', '.ad', '.adac', '.ads', '.adult', '.ae', '.aeg', '.aero',
            '.af', '.africa', '.ag', '.agency', '.ai', '.aig', '.airbus', '.airforce',
            '.airtel', '.akdn', '.al', '.ally', '.alsace', '.am', '.amazon', '.americanexpress',
            '.amp', '.android', '.anz', '.ao', '.apartments', '.app', '.apple', '.aq',
            '.ar', '.arab', '.aramco', '.archi', '.army', '.art', '.arte', '.as',
            '.asia', '.associates', '.at', '.attorney', '.au', '.auction', '.audi',
            '.audio', '.auto', '.autos', '.aw', '.aws', '.ax', '.axa', '.az',
            '.ba', '.baby', '.band', '.bank', '.bar', '.barcelona', '.barclaycard',
            '.barclays', '.bargains', '.bauhaus', '.bayern', '.bb', '.bbc', '.bbva',
            '.bcg', '.bcn', '.bd', '.be', '.beauty', '.beer', '.bentley', '.berlin',
            '.best', '.bet', '.bf', '.bg', '.bh', '.bharti', '.bi', '.bible', '.bid',
            '.bike', '.bing', '.bingo', '.bio', '.biz', '.bj', '.blog', '.blue',
            '.bm', '.bms', '.bmw', '.bn', '.bnpparibas', '.bo', '.boats', '.boehringer',
            '.bond', '.boo', '.book', '.boots', '.bosch', '.bostik', '.bot', '.boutique',
            '.br', '.bradesco', '.bridgestone', '.broadway', '.broker', '.brother',
            '.brussels', '.bs', '.bt', '.build', '.builders', '.business', '.buy',
            '.buzz', '.bv', '.bw', '.by', '.bz', '.bzh',
            '.ca', '.cab', '.cafe', '.cam', '.camera', '.camp', '.capital', '.car',
            '.cards', '.care', '.career', '.careers', '.cars', '.casa', '.cash',
            '.casino', '.cat', '.catering', '.cc', '.cd', '.center', '.ceo', '.cf',
            '.cg', '.ch', '.channel', '.chat', '.cheap', '.church', '.ci', '.circle',
            '.cisco', '.city', '.cl', '.claims', '.cleaning', '.click', '.clinic',
            '.clothing', '.cloud', '.club', '.cm', '.cn', '.co', '.coach', '.codes',
            '.coffee', '.college', '.com', '.community', '.company', '.compare',
            '.computer', '.condos', '.construction', '.consulting', '.contact',
            '.contractors', '.cooking', '.cool', '.country', '.coupons', '.courses',
            '.cr', '.credit', '.creditcard', '.cricket', '.cruise', '.cruises', '.cu',
            '.cv', '.cw', '.cx', '.cy', '.cymru', '.cz',
            '.dad', '.dance', '.data', '.date', '.dating', '.day', '.de', '.deal',
            '.deals', '.degree', '.delivery', '.democrat', '.dental', '.dentist',
            '.design', '.dev', '.diamonds', '.diet', '.digital', '.direct',
            '.directory', '.discount', '.dj', '.dk', '.dm', '.do', '.docs', '.dog',
            '.domains', '.download', '.drive', '.dz',
            '.earth', '.eat', '.ec', '.education', '.ee', '.eg', '.email', '.energy',
            '.engineer', '.engineering', '.enterprises', '.equipment', '.er', '.es',
            '.estate', '.et', '.eu', '.events', '.exchange', '.expert', '.exposed',
            '.express',
            '.fail', '.faith', '.family', '.fan', '.fans', '.farm', '.fashion',
            '.fi', '.film', '.final', '.finance', '.financial', '.fire', '.fish',
            '.fishing', '.fit', '.fitness', '.fj', '.fk', '.flights', '.florist',
            '.flowers', '.fly', '.fm', '.fo', '.foo', '.food', '.football', '.forex',
            '.forsale', '.forum', '.foundation', '.fr', '.free', '.fun', '.fund',
            '.furniture', '.fyi',
            '.ga', '.gal', '.gallery', '.game', '.games', '.garden', '.gb', '.gd',
            '.ge', '.gent', '.gf', '.gg', '.gh', '.gi', '.gift', '.gifts', '.gives',
            '.gl', '.glass', '.global', '.gm', '.gmail', '.gn', '.gold', '.golf',
            '.goo', '.google', '.gp', '.gq', '.gr', '.graphics', '.gratis', '.green',
            '.gripe', '.group', '.gs', '.gt', '.gu', '.guide', '.guitars', '.guru',
            '.gw', '.gy',
            '.hair', '.hamburg', '.haus', '.health', '.healthcare', '.help',
            '.helsinki', '.here', '.hiphop', '.hk', '.hm', '.hn', '.hockey',
            '.holdings', '.holiday', '.homes', '.honda', '.horse', '.host',
            '.hosting', '.house', '.how', '.hr', '.ht', '.hu',
            '.icu', '.id', '.ie', '.il', '.im', '.in', '.inc', '.industries',
            '.info', '.ink', '.institute', '.insurance', '.insure', '.int',
            '.international', '.investments', '.io', '.iq', '.ir', '.irish', '.is',
            '.it',
            '.je', '.jewelry', '.jm', '.jo', '.jobs', '.jp', '.juegos',
            '.kaufen', '.ke', '.kg', '.kh', '.ki', '.kim', '.kitchen', '.kiwi',
            '.km', '.kn', '.kp', '.kr', '.kw', '.ky', '.kz',
            '.la', '.land', '.lat', '.law', '.lawyer', '.lb', '.lc', '.lease',
            '.legal', '.li', '.life', '.lighting', '.limited', '.limo', '.link',
            '.live', '.lk', '.llc', '.loan', '.loans', '.lol', '.london', '.love',
            '.lr', '.ls', '.lt', '.ltd', '.lu', '.lv', '.ly',
            '.ma', '.management', '.map', '.market', '.marketing', '.markets',
            '.mba', '.mc', '.md', '.me', '.media', '.meet', '.melbourne', '.memorial',
            '.men', '.menu', '.mf', '.mg', '.mh', '.miami', '.mk', '.ml', '.mm',
            '.mn', '.mo', '.mobi', '.moda', '.mom', '.money', '.monster', '.mortgage',
            '.movie', '.mp', '.mq', '.mr', '.ms', '.mt', '.mu', '.museum', '.mv',
            '.mw', '.mx', '.my', '.mz',
            '.na', '.name', '.navy', '.nc', '.ne', '.net', '.network', '.new',
            '.news', '.nf', '.ng', '.ni', '.ninja', '.nl', '.no', '.np', '.nr',
            '.nu', '.nyc', '.nz',
            '.observer', '.okinawa', '.om', '.one', '.ong', '.onl', '.online',
            '.ooo', '.org', '.organic', '.osaka', '.ovh',
            '.pa', '.page', '.paris', '.partners', '.parts', '.party', '.pe',
            '.pet', '.pf', '.pg', '.ph', '.pharmacy', '.photo', '.photography',
            '.photos', '.physio', '.pics', '.pictures', '.pid', '.pink', '.pizza',
            '.pk', '.pl', '.place', '.plumbing', '.plus', '.pm', '.pn', '.poker',
            '.porn', '.post', '.pr', '.press', '.pro', '.productions', '.promo',
            '.properties', '.property', '.protection', '.ps', '.pt', '.pub', '.pw',
            '.py',
            '.qa', '.quebec', '.quest',
            '.racing', '.re', '.realestate', '.recipes', '.red', '.rehab', '.reise',
            '.reisen', '.rent', '.rentals', '.repair', '.report', '.republican',
            '.rest', '.restaurant', '.review', '.reviews', '.rich', '.rio', '.rip',
            '.ro', '.rocks', '.rodeo', '.rs', '.ru', '.rugby', '.run', '.rw',
            '.sa', '.sale', '.salon', '.sarl', '.sb', '.sc', '.school', '.science',
            '.sd', '.se', '.search', '.security', '.services', '.sg', '.sh', '.shiksha',
            '.shoes', '.shop', '.shopping', '.show', '.si', '.singles', '.site',
            '.sj', '.sk', '.ski', '.skin', '.sl', '.sm', '.sn', '.so', '.social',
            '.software', '.solar', '.solutions', '.space', '.sport', '.sr', '.ss',
            '.st', '.storage', '.store', '.stream', '.studio', '.study', '.style',
            '.su', '.supply', '.support', '.surf', '.surgery', '.sv', '.sx', '.sy',
            '.sydney', '.systems', '.sz',
            '.tax', '.taxi', '.tc', '.td', '.team', '.tech', '.technology', '.tel',
            '.tf', '.tg', '.th', '.theater', '.tips', '.tires', '.tj', '.tk', '.tl',
            '.tm', '.tn', '.to', '.today', '.tokyo', '.tools', '.top', '.tours',
            '.town', '.toys', '.tr', '.trade', '.trading', '.training', '.travel',
            '.tt', '.tube', '.tv', '.tw', '.tz',
            '.ua', '.ug', '.uk', '.university', '.uno', '.us', '.uy', '.uz',
            '.va', '.vacations', '.vc', '.ve', '.ventures', '.vet', '.vg', '.vi',
            '.video', '.villas', '.vin', '.vip', '.vision', '.vn', '.vodka',
            '.vote', '.voting', '.voyage', '.vu',
            '.wales', '.wang', '.watch', '.webcam', '.website', '.wedding', '.wf',
            '.wiki', '.win', '.wine', '.work', '.works', '.world', '.ws', '.wtf',
            '.xbox', '.xyz',
            '.yachts', '.ye', '.yoga', '.yokohama', '.you', '.yt',
            '.za', '.zm', '.zone', '.zw',
        ];
    }

    // =========================================================================
    // CONTACTS (WHOIS) - Note: Porkbun API does not support contact editing
    // =========================================================================

    public function getDomainContacts($domain, $module_row_id = null)
    {
        return [];
    }

    public function setDomainContacts($domain, array $vars = [], $module_row_id = null)
    {
        return false;
    }

    public function resendTransferEmail($domain, $module_row_id = null)
    {
        return false;
    }

    /**
     * Sends the EPP authorization code to the admin contact
     * Note: Porkbun does not support EPP retrieval via API.
     * Users must get the auth code from their Porkbun dashboard.
     *
     * @param string $domain The domain to request EPP for
     * @param int $module_row_id The module row ID
     * @return bool
     */
    public function sendEppEmail($domain, $module_row_id = null)
    {
        return true;
    }

    /**
     * Updates the EPP code for a domain (not supported by Porkbun API)
     *
     * @param string $domain The domain
     * @param string $epp_code The EPP code
     * @param int $module_row_id The module row ID
     * @param array $vars Additional vars
     * @return bool
     */
    public function updateEppCode($domain, $epp_code, $module_row_id = null, array $vars = [])
    {
        return true;
    }

    /**
     * Gets the EPP code for a domain
     * Porkbun doesn't expose EPP via API - users should get it from dashboard
     *
     * @param string $domain The domain
     * @param int $module_row_id The module row ID
     * @return string|false
     */
    public function getEppCode($domain, $module_row_id = null)
    {
        return Language::_('Porkbun.!error.epp.dashboard', true);
    }

    public function restoreDomain($domain, $module_row_id = null, array $vars = [])
    {
        return false;
    }
}
