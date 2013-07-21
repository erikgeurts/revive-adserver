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

require_once MAX_PATH . '/lib/max/Plugin.php';
require_once LIB_PATH . '/Extension/deliveryLimitations/DeliveryLimitationsCommaSeparatedData.php';

/**
 * A class for testing the Plugins_DeliveryLimitations_Client_Browser class.
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@m3.net>
 */
class Plugins_DeliveryLimitations_ArrayData_Test extends UnitTestCase
{
     function Plugins_DeliveryLimitations_TestCase()
    {
        $this->UnitTestCase();
    }

    function test_preCompile()
    {
        $current_quotes_runtime = get_magic_quotes_runtime();

        $oPlugin = new Plugins_DeliveryLimitations_CommaSeparatedData();
        $this->assertEqual('ab,cd,ef,gh', $oPlugin->_preCompile('ab,cd,ef,gh'));
        $this->assertEqual('ab,cd,ef,gh', $oPlugin->_preCompile('aB,cD, ef,gh '));
        set_magic_quotes_runtime(1);
        $this->assertEqual('a\\b,cd,ef,gh', $oPlugin->_preCompile('a\\b,cd,ef,gh'));
        set_magic_quotes_runtime(0);
        $this->assertEqual('a\\\\b,cd,ef,gh', $oPlugin->_preCompile('a\\b,cd,ef,gh'));

        set_magic_quotes_runtime($current_quotes_runtime);
    }
}
?>