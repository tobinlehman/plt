<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Options field checkbox
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Checkbox.php 1042889 2014-12-11 19:35:19Z worschtebrot $
 */
require_once dirname(__FILE__) . '/../Field.php';

class IfwPsn_Wp_Options_Field_Checkbox extends IfwPsn_Wp_Options_Field
{
    public function render(array $params)
    {
        /**
         * @var IfwPsn_Wp_Options
         */
        $options = $params[0];

        $id = $options->getOptionRealId($this->_id);
        $name = $options->getPageId() . '['. $id .']';

        if (isset($this->_params['non_permanent'])) {
            $checked = '';
        } else {
            $checked = checked(1, $options->getOption($this->_id), false);
        }

        $html = '<input type="checkbox" id="'. $id .'" name="'. $name .'" value="1" ' . $checked . '/>';
        if (!empty($this->_description)) {
            $html .= '<label for="'. $id .'"> '  . $this->_description . '</label>';
        }
        echo $html;
    }
}
