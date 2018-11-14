<?php

class Paylike_Payment_Model_Observer {

    /**
     * This function is called on core_block_abstract_to_html_after event
     * We will append our block to the html
     * @param Varien_Event_Observer $observer
     */
    public function getSalesOrderViewInfo(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();

        /**
         * layout name should be same as used in app/design/adminhtml/default/default/layout/paylike.xml
         */
        if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('paylike.order.info.adminoperations.block'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }

    /**
     * To fetch the updated cart total based on the shipment and discount coupon specially for Magestore checkout
     * @param Varien_Event_Observer $observer
     */
    public function updatedGrandTotal(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if (($block->getNameInLayout() == 'review_info') && ($child = $block->getChild('paylike.updated.total.block'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }

}
