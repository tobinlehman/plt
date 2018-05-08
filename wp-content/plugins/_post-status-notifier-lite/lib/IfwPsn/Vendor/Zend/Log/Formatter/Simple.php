<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Log
 * @subpackage Formatter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Simple.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 */

/** IfwPsn_Vendor_Zend_Log_Formatter_Abstract */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Formatter/Abstract.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Log
 * @subpackage Formatter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Simple.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 */
class IfwPsn_Vendor_Zend_Log_Formatter_Simple extends IfwPsn_Vendor_Zend_Log_Formatter_Abstract
{
    /**
     * @var string
     */
    protected $_format;

    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message%';

    /**
     * Class constructor
     *
     * @param  null|string  $format  Format specifier for log messages
     * @return void
     * @throws IfwPsn_Vendor_Zend_Log_Exception
     */
    public function __construct($format = null)
    {
        if ($format === null) {
            $format = self::DEFAULT_FORMAT . PHP_EOL;
        }

        if (!is_string($format)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Exception.php';
            throw new IfwPsn_Vendor_Zend_Log_Exception('Format must be a string');
        }

        $this->_format = $format;
    }

    /**
	 * Factory for IfwPsn_Vendor_Zend_Log_Formatter_Simple classe
	 *
	 * @param array|IfwPsn_Vendor_Zend_Config $options
	 * @return IfwPsn_Vendor_Zend_Log_Formatter_Simple
     */
    public static function factory($options)
    {
        $format = null;
        if (null !== $options) {
            if ($options instanceof IfwPsn_Vendor_Zend_Config) {
                $options = $options->toArray();
            }

            if (array_key_exists('format', $options)) {
                $format = $options['format'];
            }
        }

        return new self($format);
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->_format;

        foreach ($event as $name => $value) {
            if ((is_object($value) && !method_exists($value,'__toString'))
                || is_array($value)
            ) {
                $value = gettype($value);
            }

            $output = str_replace("%$name%", $value, $output);
        }

        return $output;
    }
}
