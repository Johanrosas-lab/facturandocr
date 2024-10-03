<?php

/**
 * Documentation for these can be found on default.settings.php
 * and example.settings.local.php
 */

// Common local settings
assert_options(ASSERT_ACTIVE, TRUE);
\Drupal\Component\Assertion\Handle::register();

// Allow Lando-based URLs.
$settings['trusted_host_patterns'] = array(
  '^.*$',
);

$settings['skip_permissions_hardening'] = TRUE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$settings['extension_discovery_scan_tests'] = TRUE;

// A local dev services.yml file than can be edited as necessary
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/dev.services.yml';

// Disabling caches
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';

// Verbose error
$config['system.logging']['error_level'] = 'verbose';

// Easilly accessible local private file system
$settings['file_private_path'] = 'sites/default/files/private';

// Local hash_salt not to use the same as remote ones.
$settings['hash_salt'] = 'development';

// Set temporary directory within the local filesystem.
$config['system.file']['path']['temporary'] = '/tmp';
// Another alternative is within the public files directory, but you might have
// to create that directory beforehand.
// $config['system.file']['path']['temporary'] = $app_root . '/sites/default/files/tmp';

// Reroute email enabled and sent to watchdog by default.
// Change in your local if needed.
$config['reroute_email.settings']['enable'] = TRUE;
$config['reroute_email.settings']['address'] = '';

// Configure config splits.
$config['config_split.config_split.development']['status'] = TRUE;
$config['config_role_split.role_split.development']['status'] = TRUE;
