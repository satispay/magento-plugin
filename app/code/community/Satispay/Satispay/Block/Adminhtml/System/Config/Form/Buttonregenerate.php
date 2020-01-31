<?php


class Satispay_Satispay_Block_Adminhtml_System_Config_Form_Buttonregenerate extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('satispay/system/config/buttonregenerate.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxReGenerateKeys()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_keysgeneretor/regeneratekeys');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'satispay_button_regenerate',
            'label'     => $this->helper('adminhtml')->__('Re-generate Keys'),
            'onclick'   => 'javascript:check(); return false;'
            ));

        return $button->toHtml();
    }
}