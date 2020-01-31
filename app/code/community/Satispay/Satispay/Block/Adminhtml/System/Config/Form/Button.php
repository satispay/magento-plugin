<?php


class Satispay_Satispay_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('satispay/system/config/button.phtml');
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
    public function getAjaxGenerateKeys()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_keysgeneretor/generatekeys');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $helper = Mage::helper('satispay');
        $sandbox = $helper->isSandbox();
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'satispay_button',
            'label'     => $this->helper('adminhtml')->__('Generate Keys'),
            'onclick'   => 'javascript:generate(); return false;',
            'style'   => ($helper->getPublicKey(null, $sandbox) && $helper->getPrivateKey(null, $sandbox) && $helper->getKeyId(null, $sandbox)) ? "display: none;" : ''
            ));

        return $button->toHtml();
    }
}