<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

// Require the initialisation file
require_once '../../init-delivery.php';

// Required files
require_once MAX_PATH . '/lib/max/Delivery/adSelect.php';

// No Caching
MAX_commonSetNoCacheHeaders();

// Register any script specific input variables
MAX_commonRegisterGlobalsArray(['n']);
if (!isset($n)) {
    $n = 'default';
}

$richMedia = false;     // This is an image tag - we only need the filename (or URL?) of the image...
$target = '';           // Target cannot be dynamically set in basic tags.
$context = [];     // I don't think that $context is valid in adview.php...
$ct0 = '';              // Click tracking should be done using external tags rather than this way...
$withText = 0;          // Cannot write text using a simple tag...
$row = MAX_adSelect($what, $campaignid, $target, $source, $withText, $charset, $context, $richMedia, $ct0, $loc, $referer);

if (!empty($row['html'])) {
    // Send bannerid headers
    $cookie = [];
    $cookie[$conf['var']['adId']] = $row['bannerid'];
    // Send zoneid headers
    if ($zoneid != 0) {
        $cookie[$conf['var']['zoneId']] = $zoneid;
    }
    // Send source headers
    if (!empty($source)) {
        $cookie[$conf['var']['channel']] = $source;
    }
    if (!empty($row['clickwindow'])) {
        $cookie[$conf['var']['lastClick']] = 1;
    }
    // addUrlParams hook for plugins to add key=value pairs to the log/click URLs
    $componentParams = OX_Delivery_Common_hook('addUrlParams', [$row]);
    if (!empty($componentParams) && is_array($componentParams)) {
        foreach ($componentParams as $params) {
            if (!empty($params) && is_array($params)) {
                foreach ($params as $key => $value) {
                    $cookie[$key] = $value;
                }
            }
        }
    }

    // The call to view_raw() above will have tried to log the impression via a beacon,
    // but this type of ad doesn't work with beacons, so the impression must
    // be logged here
    if ($conf['logging']['adImpressions'] && empty($row['skip_log_blank'])) {
        MAX_Delivery_log_logAdImpression($row['bannerid'], $zoneid);
    }
    // Redirect to the banner
    MAX_cookieAdd($conf['var']['vars'] . "[$n]", json_encode($cookie, JSON_UNESCAPED_SLASHES));
    MAX_cookieFlush();
    if ($row['bannerid'] == '') {
        if ($row['default_banner_image_url'] != '') {
            // Show default banner image url
            MAX_redirect($row['default_banner_image_url']);
        } else {
            // Show 1x1 Gif, to ensure not broken image icon is shown.
            MAX_commonDisplay1x1();
        }
    } else {
        MAX_redirect($row['html']);
    }
} else {
    MAX_cookieUnset($conf['var']['vars'] . "[$n]");
    MAX_cookieFlush();
    // Show 1x1 Gif, to ensure not broken image icon is shown.
    MAX_commonDisplay1x1();
}

// Run automaintenance, if needed
if (!empty($GLOBALS['_MAX']['CONF']['maintenance']['autoMaintenance']) && empty($GLOBALS['_MAX']['CONF']['lb']['enabled'])) {
    if (MAX_cacheCheckIfMaintenanceShouldRun()) {
        include MAX_PATH . '/lib/OA/Maintenance/Auto.php';
        OA_Maintenance_Auto::run();
    }
}
