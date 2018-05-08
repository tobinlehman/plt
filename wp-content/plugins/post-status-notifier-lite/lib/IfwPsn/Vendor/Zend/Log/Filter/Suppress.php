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
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Suppress.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 */

/** IfwPsn_Vendor_Zend_Log_Filter_Interface */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Filter/Abstract.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Suppress.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 */
class IfwPsn_Vendor_Zend_Log_Filter_Suppress extends IfwPsn_Vendor_Zend_Log_Filter_Abstract
{
    /**
     * @var boolean
     */
    protected $_accept = true;

    /**
     * This is a simple boolean filter.
     *
     * Call suppress(true) to suppress all log events.
     * Call suppress(false) to accept all log events.
     *
     * @param  boolean  $suppress  Should all log events be suppressed?
     * @return  void
     */
    public function suppress($suppress)
    {
        $this->_accept = (! $suppress);
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return $this->_accept;
    }

    /**
     * Create a new instance of IfwPsn_Vendor_Zend_Log_Filter_Suppress
     *
     * @param  array|IfwPsn_Vendor_Zend_Config $config
     * @return IfwPsn_Vendor_Zend_Log_Filter_Suppress
     * @throws IfwPsn_Vendor_Zend_Log_Exception
     */
    static public function factory($config)
    {
        return new self();
    }
}
