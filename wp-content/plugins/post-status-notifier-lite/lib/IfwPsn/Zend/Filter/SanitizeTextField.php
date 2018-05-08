<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: SanitizeTextField.php 1248505 2015-09-18 13:49:54Z worschtebrot $
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Filter/Interface.php';

class IfwPsn_Zend_Filter_SanitizeTextField implements IfwPsn_Vendor_Zend_Filter_Interface
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws IfwPsn_Vendor_Zend_Filter_Exception If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($value);
        }
        return $value;
    }

}
