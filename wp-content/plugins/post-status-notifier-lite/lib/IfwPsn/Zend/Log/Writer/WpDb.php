<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: WpDb.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Writer/Abstract.php';

class IfwPsn_Zend_Log_Writer_WpDb extends IfwPsn_Vendor_Zend_Log_Writer_Abstract
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var
     */
    protected $_modelName;

    /**
     * @var string
     */
    public static $timeToKeepDefault = '1m';



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $modelName
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $modelName)
    {
        $this->_pm = $pm;
        $this->setModelName($modelName);
    }

    /**
     * Write a message to the log.
     *
     * @param  array $event  log data event
     * @return bool
     */
    protected function _write($event)
    {
        $dataToInsert = array();
        $r = new ReflectionProperty($this->_modelName, 'eventItems');

        foreach ($r->getValue() as $item) {
            if (isset($event[$item])) {
                if (is_array($event[$item]) || is_object($event[$item])) {
                    $dataToInsert[$item] = var_export($dataToInsert[$item], true);
                } else {
                    //$dataToInsert[$item] = htmlentities(utf8_encode($event[$item]), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset());
                    //$dataToInsert[$item] = htmlentities($event[$item], ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset());
                    $dataToInsert[$item] = $event[$item];
                }
            }
        }

        return IfwPsn_Wp_ORM_Model::factory($this->_modelName)->create($dataToInsert)->save();
    }

    /**
     * Construct a IfwPsn_Vendor_Zend_Log driver
     *
     * @param  array|IfwPsn_Vendor_Zend_Config $config
     * @return IfwPsn_Vendor_Zend_Log_FactoryInterface
     */
    static public function factory($config)
    {
        // not supported
    }

    /**
     * @param  $modelName
     */
    public function setModelName($modelName)
    {
        $this->_modelName = $modelName;
    }

    /**
     * @return
     */
    public function getModelName()
    {
        return $this->_modelName;
    }

    /**
     * @return array
     */
    public static function getStorageTimeLimits()
    {
        return array(
            '1d' => __('1 day', 'ifw'),
            '3d' => __('3 days', 'ifw'),
            '1w' => __('1 week', 'ifw'),
            '2w' => __('2 weeks', 'ifw'),
            '1m' => __('1 month', 'ifw'),
        );
    }

    /**
     * @param $time
     * @return bool
     */
    public static function isValidTimeToKeep($time)
    {
        return in_array($time, array_keys(self::getStorageTimeLimits()));
    }
}
