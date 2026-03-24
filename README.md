# Porkbun Module for Blesta

A registrar module that integrates [Porkbun](https://porkbun.com) with [Blesta](https://www.blesta.com), enabling automated domain registration, renewals, DNS management, and full domain lifecycle control.

## Features

- **Domain Registration** -- register domains with automatic pricing lookup and contact creation
- **Domain Renewals** -- auto-renew toggle via API (Porkbun manages renewals server-side)
- **DNS Record Management** -- full CRUD for A, AAAA, CNAME, MX, TXT, SRV, NS, TLSA, and CAA records
- **DNSSEC Management** -- add and delete DS records with key data support
- **Glue Records** -- create, edit, and delete child nameservers with IPv4/IPv6 support
- **URL Forwarding** -- manage 301/302 redirects with wildcard and path-include options
- **Nameserver Management** -- set up to 5 custom nameservers per domain
- **Domain Settings** -- registrar lock, WHOIS privacy (free with Porkbun), and auto-renew toggles
- **EPP/Auth Code** -- retrieval guidance (managed via Porkbun dashboard)
- **Domain Date Sync** -- admin action to synchronize expiration dates from the registry
- **TLD Pricing Import** -- automatic pricing from Porkbun API with 24-hour caching
- **API Request Logging** -- all API calls logged with credential masking

## Requirements

- Blesta v5.x or later
- PHP 8.0 or later with cURL extension
- A Porkbun account with API access enabled

## Installation

1. Download or clone this repository
2. Copy the `porkbun` folder into your Blesta installation:
   ```
   /components/modules/porkbun/
   ```
3. Log in to Blesta admin and navigate to **Settings > Company > Modules**
4. Click **Install** next to "Porkbun"

## Configuration

### Adding an Account

1. Go to **Settings > Company > Modules > Porkbun**
2. Click **Add Account**
3. Fill in:
   - **Account Label** -- a friendly name for internal reference
   - **API Key** -- your Porkbun API key
   - **Secret API Key** -- your Porkbun secret API key

### Creating a Package

1. Go to **Packages > New**
2. Select "Porkbun" as the module and pick your account
3. On the module options tab:
   - **TLDs** -- check the TLDs you want to offer (auto-populated from Porkbun API)
   - **Default Nameservers** -- optional; pre-filled into new orders

## Client-Facing Tabs

| Tab | Description |
|-----|-------------|
| **Name Servers** | View and update up to 5 nameservers |
| **DNS Records** | Add, edit, and delete DNS records |
| **DNSSEC** | Add and remove DS records for DNSSEC |
| **Glue Records** | Manage child nameservers (glue records) |
| **URL Forwarding** | Set up 301/302 redirects |
| **Settings** | Registrar lock, WHOIS privacy, auto-renew, EPP code request |

## Admin-Facing Tabs

| Tab | Description |
|-----|-------------|
| **Admin Actions** | Sync renewal date from registry |
| All client tabs | Admins have access to all client-facing tabs |

## API Limitations

Porkbun's API has some limitations that affect this module:

| Feature | Status | Notes |
|---------|--------|-------|
| Domain Registration | Supported | Full API support |
| Domain Transfer | Not Supported | Must be done via Porkbun dashboard |
| Domain Renewal | Via Auto-Renew | No direct renewal endpoint; module toggles auto-renew |
| Domain Lock/Unlock | Not Supported | Managed via Porkbun dashboard |
| WHOIS Contact Editing | Not Supported | Managed via Porkbun dashboard |
| WHOIS Privacy | Supported | Free with all Porkbun domains |
| EPP/Auth Code | Not via API | Must be retrieved from Porkbun dashboard |

## File Structure

```
porkbun/
├── porkbun.php                      # Main module class
├── config.json                      # Module metadata and version
├── composer.json                    # PHP version requirement
├── config/
│   └── porkbun.php                  # TLD list, WHOIS fields, DNS types, email templates
├── apis/
│   ├── porkbun_api.php              # REST API wrapper with credential masking
│   └── porkbun_response.php         # API response handler
├── language/
│   └── en_us/
│       └── porkbun.php              # English language strings
└── views/default/
    ├── manage.pdt                   # Account list (admin)
    ├── add_row.pdt & edit_row.pdt   # Account forms
    ├── admin_service_info.pdt       # Admin service summary
    ├── client_service_info.pdt      # Client service summary
    ├── tab_nameservers.pdt          # Admin nameserver management
    ├── tab_client_nameservers.pdt   # Client nameserver management
    ├── tab_dnsrecords.pdt           # Admin DNS management
    ├── tab_client_dnsrecords.pdt    # Client DNS management
    ├── tab_dnssec.pdt               # Admin DNSSEC management
    ├── tab_client_dnssec.pdt        # Client DNSSEC management
    ├── tab_gluerecords.pdt          # Admin glue records
    ├── tab_client_gluerecords.pdt   # Client glue records
    ├── tab_urlforward.pdt           # Admin URL forwarding
    ├── tab_client_urlforward.pdt    # Client URL forwarding
    ├── tab_settings.pdt             # Admin domain settings
    ├── tab_client_settings.pdt      # Client domain settings
    └── tab_admin_actions.pdt        # Admin actions (sync date)
```

## Security

- API credentials are stored encrypted in the database
- API keys are masked in request logs (replaced with asterisks)
- All API communication uses HTTPS with SSL verification
- Template output is XSS-safe using `Html->safe()` throughout
- API error messages are sanitized with `strip_tags()` before display
- DNS record IDs and hostnames are sanitized before use in API URLs
- TTL values are validated and clamped (600--86400 seconds)
- DNS record types are validated against an allowlist

## Troubleshooting

- **API errors**: Check **Tools > Logs > Module** in Blesta admin for request/response logs
- **TLDs not loading**: The TLD list is cached for 24 hours in Blesta's cache directory; clear it to force refresh
- **Domain transfers**: Porkbun does not support transfers via API -- use the Porkbun dashboard
- **WHOIS editing**: Contact editing is not available via API -- use the Porkbun dashboard
- **Domain lock**: Lock/unlock is managed via the Porkbun dashboard, not via API

## License

Proprietary. All rights reserved.

## Author

[BiswasHost](https://www.biswashost.com)
