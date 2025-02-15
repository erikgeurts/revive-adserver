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

require_once RV_PATH . '/lib/RV.php';

require_once MAX_PATH . '/lib/OA.php';
require_once LIB_PATH . '/Extension/bannerTypeHtml/bannerTypeHtml.php';
require_once MAX_PATH . '/lib/max/Plugin/Common.php';

/**
 *
 * @package    OpenXPlugin
 * @subpackage Plugins_BannerTypes
 * @abstract
 */
class Plugins_BannerTypeHTML_demoBannerTypeHtml_demoHtml extends Plugins_BannerTypeHTML
{
    /**
     * Return description of banner type
     * for the dropdown selection on the banner-edit screen
     *
     * @return string A string describing the type of plugin.
     */
    public function getOptionDescription()
    {
        return $this->translate('Demonstration Plugin HTML Banner Type');
    }

    /**
     * Append type-specific form elements to the base form
     *
     * @param object $form
     * @param array $row
     */
    public function buildForm(&$form, &$row)
    {
        parent::buildForm($form, $row);
        $form->addElement('text', 'demofield', 'Demo Field');
        $form->addRule("demofield", $this->translate('Please enter http://www.revive-adserver.com'), 'regex', '/^http:\/\/www\.openx\.org$/');
    }

    /**
     * Custom validation method
     * This is executed AFTER form submit
     * Main validation is handled by adding rules to the form in buildForm()
     * which are processed prior to this method being called
     *
     * @param object $form
     * @return boolean
     */
    public function validateForm(&$form)
    {
        return true;
    }

    /**
     * This method is executed BEFORE
     * the core banners table is written to
     *
     * @param boolean $insert
     * @param integer $bannerid
     * @param array $aFields
     * @param array $aVariables
     * @return boolean
     */
    public function preprocessForm($insert, $bannerid, &$aFields, &$aVariables)
    {
        $aVariables['htmltemplate'] = $this->_buildHtmlTemplate($aVariables);
        $aVariables['comments'] = $this->translate('Demonstration OpenX Banner Type ID %s', [$aFields['bannerid']]);
        return true;
    }

    /**
     * This method is executed AFTER
     * the core banners table is written to
     *
     * @param boolean $insert
     * @param integer $bannerid
     * @param array $aFields
     * @return boolean
     */
    public function processForm($insert, $bannerid, &$aFields, &$aVariables)
    {
        /**
         * Uncomment the following lines IF
         * you have completed the steps to make this plugin data-aware
        */

        $doBanners = OA_Dal::factoryDO('banners_demo');
        if ($insert) {
            $doBanners->banners_demo_id = $bannerid;
            $doBanners->banners_demo_desc = $aFields['description'];
            return $doBanners->insert();
        } else {
            $doBanners->banners_demo_desc = $aFields['description'];
            $doBanners->whereAdd('banners_demo_id=' . $bannerid, 'AND');
            return $doBanners->update(DB_DATAOBJECT_WHEREADD_ONLY);
        }
        return true;
    }

    /**
     * You will need to compile your HTML for insertion
     * into the core banners table
     *
     * @param array $aFields
     * @return string
     */
    public function _buildHtmlTemplate($aFields)
    {
        $result = '<div>' . $this->translate('Demonstration OpenX Banner Type ID %s', [$aFields['bannerid']]) . '</div>';
        return $result;
    }

    public function exportData($identity = '')
    {
        $oDbh = OA_DB::singleton();
        switch ($oDbh->dbsyntax) {
            case 'mysql':
            case 'mysqli':
                $engine = $oDbh->getOption('default_table_type');
                $sql = "CREATE TABLE %s ENGINE={$engine} (SELECT * FROM %s %s)";
                break;
            case 'pgsql':
                $sql = 'CREATE TABLE "%1$s" (LIKE "%2$s" INCLUDING DEFAULTS); INSERT INTO "%1$s" SELECT * FROM "%2$s" "%3$s"';
                break;
        }
        $aConf = $GLOBALS['_MAX']['CONF']['table'];
        if (!$identity) {
            $identity = 'z_' . $this->component . date('Ymd_His');
        }

        $tblSrc = $aConf['prefix'] . 'banners_demo';
        $tblTgt = $aConf['prefix'] . $identity . $tblSrc;
        $where = "WHERE 1=1";
        $query = sprintf($sql, $tblTgt, $tblSrc, $where);
        $result1 = $oDbh->exec($query);

        $tblSrc = $aConf['prefix'] . 'banners';
        $tblTgt = $aConf['prefix'] . $identity . $tblSrc;
        $where = "WHERE ext_bannertype = '" . $this->getComponentIdentifier() . "'";
        $query = sprintf($sql, $tblTgt, $tblSrc, $where);
        $result2 = $oDbh->exec($query);

        if ($result1 && $result2) {
            return OA_DB_Table::listOATablesCaseSensitive($identity);
        }
        return false;
    }

    public function fetchBannersJoined($fetchmode = MDB2_FETCHMODE_ORDERED)
    {
        $aConf = $GLOBALS['_MAX']['CONF']['table'];
        $oDbh = OA_DB::singleton();
        $tblB = $oDbh->quoteIdentifier($aConf['prefix'] . 'banners', true);
        $tblD = $oDbh->quoteIdentifier($aConf['prefix'] . 'banners_demo');
        $query = "SELECT * FROM " . $tableB . " b"
                 . " LEFT JOIN " . $tableD . " d ON b.bannerid = d.banners_demo_id"
                 . " WHERE b.ext_bannertype = '" . $this->getComponentIdentifier() . "'";
        return $oDbh->queryAll($query, null, $fetchmode);
    }
}
