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

/**
 * @package    OpenXPlugin
 * @subpackage 3rdPartyServers
 * @author     Matteo Beccati <matteo.beccati@openx.org>
 *
 */

require_once LIB_PATH . '/Extension/3rdPartyServers/3rdPartyServers.php';

/**
 *
 * 3rdPartyServer plugin. Allow for generating different banner html cache
 *
 * @static
 */
class Plugins_3rdPartyServers_ox3rdPartyServers_google extends Plugins_3rdPartyServers
{

    /**
     * Return the name of plugin
     *
     * @return string
     */
    function getName()
    {
        return $this->translate('Rich Media - Google AdSense');
    }

    /**
     * Return plugin cache
     *
     * @return string
     */
    function getBannerCache($buffer, &$noScript)
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        if (preg_match('/<script.*?src=".*?googlesyndication\.com/is', $buffer))
        {
            $buffer = "<span>".
                      "<script type='text/javascript'><!--// <![CDATA[\n".
                      "/* {$conf['var']['openads']}={url_prefix} {$conf['var']['adId']}={bannerid} {$conf['var']['zoneId']}={zoneid} {$conf['var']['channel']}={source} */\n".
                      "// ]]> --></script>".
                      $buffer.
                      "<script type='text/javascript' src='{url_prefix}/".$conf['file']['google']."'></script>".
                      "</span>";
        }

        return $buffer;
    }

}

?>
