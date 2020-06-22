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
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_keysgenerator/generatekeys');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $helper = Mage::helper('satispay');

        $isActivated = $helper->isActivated();
        $buttonData = array(
            'id' => 'satispay_button',
            'label' => $this->helper('adminhtml')->__($isActivated ? 'Activate with new activation code' : 'Activate'),
        );

        $isSandbox = $helper->isSandbox();
        if ($isSandbox) {
            $buttonData['label'] = $this->helper('adminhtml')->__($isActivated ? 'Activate sandbox with new activation code' : 'Activate sandbox');
        }

        $activationCodeNotSpecified = empty($helper->getToken($isSandbox));
        if ($activationCodeNotSpecified) {
            $buttonData['disabled'] = 'disabled';
        } else {
            $buttonData['onclick'] = 'javascript:generate(); return false;';
        }

        $button = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData($buttonData);

        return $button->toHtml();
    }
}
