<?php

class Tivents_Tickets_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'tivents_tickets';

    /**
     * Returns available shipping rates for Inchoo Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        /** @var Tivents_Tickets_Helper_Data $expressMaxProducts */
        $expressMaxWeight = Mage::helper('tivents_tickets')->getExpressMaxWeight();

        $totalqty = 0;

        $expressAvailable = true;
        $vmvshipping = false;

        $shippingaddress = Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->getCountry();
        Mage::log($shippingaddress, null, 'shipping.log');
        foreach ($request->getAllItems() as $item) {
            if($item->getProductType() == 'simple') {
                $totalqty += $item->getQty();
                if ($item->getStoreId() == 10) {
                    $vmvshipping = true;
                }

            }
        }
        if ($vmvshipping == true) {
            $result->append($this->_getVmvRate($totalqty));
        }

        else {
            $result->append($this->_getStandardSoftRate());
            if ($shippingaddress == 'DE') {
                if($totalqty > 0) {
                    $result->append($this->_getGiftSoftRate());
                    $result->append($this->_getStandardHardRate($totalqty));
                    $result->append($this->_getGiftHardRate($totalqty));
                }
            }

            else {
                if($totalqty > 0) {
                    $result->append($this->_getGiftSoftRate());
                }
            }

        }
        return $result;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'a4'    =>  'zum Selbstausdrucken per E-Mail',
            'a4_gift'    =>  'zum Selbstausdrucken per E-Mail (ohne Preisaufdruck)',
            'ht'     =>  'Versand (unversichert) per Post - pro Ticket 1,90 €, insgesamt:',
            'ht_gift'     =>  'Versand (unversichert) per Post (ohne Preisaufdruck) - pro Ticket 1,90 €, insgesamt:',
            'ht_prio'     =>  'PRIO-Versand (versichert) per Post  - pro Ticket 1,90 €, insgesamt:',
            'ht_prio_gift'     =>  'PRIO-Versand (versichert) per Post (ohne Preisaufdruck) - pro Ticket 1,90 €, insgesamt:',
            'ht_vmv'     =>  'Versand über die Deutsche Post'
        );
    }

    /**
     * Get Standard rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardSoftRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */

        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('a4');
        $rate->setMethodTitle('zum Selbstausdrucken per E-Mail');
        $rate->setPrice(0);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getGiftSoftRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */

        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('a4_gift');
        $rate->setMethodTitle('zum Selbstausdrucken per E-Mail (ohne Preisaufdruck)');
        $rate->setPrice(0);
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Get Express rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardHardRate($totalqty)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('ht');
        $rate->setMethodTitle('Hardcover per Post zugestellt - pro Ticket 1,90 €, insgesamt:');


        $rate->setPrice(1.90 * $totalqty);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getGiftHardRate($totalqty)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('ht_gift');
        $rate->setMethodTitle('Hardcover per Post zugestellt (ohne Preisaufdruck) - pro Ticket 1,90 €, insgesamt:');


        $rate->setPrice(1.90 * $totalqty);
        $rate->setCost(0);

        return $rate;
    }


    protected function _getVmvRate($totalqty)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('ht_vmv');
        $rate->setMethodTitle('Versand über die Deutsche Post');


        #$rate->setPrice(1.90 * $totalqty);
        #$rate->setCost(0);

        return $rate;
    }
}