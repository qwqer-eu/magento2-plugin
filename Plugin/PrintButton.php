<?php

namespace Qwqer\Express\Plugin;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar\Interceptor;
use Magento\Framework\View\Element\AbstractBlock;

class PrintButton
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private \Magento\Framework\App\RequestInterface $_request;

    /**
     * @param Interceptor $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return void
     */
    public function beforePushButtons(
        Interceptor $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        $this->_request = $context->getRequest();
        if ($this->_request->getFullActionName() == 'sales_order_view'){
            $order = $context->getOrder();
            if($order) {
                $qwqerOrderId = $order->getData('qwqer_order_id');
                if (!$qwqerOrderId) {
                   return;
                }
                $url = "https://qwqer.hostcream.eu/storage/delivery-order-covers/" . $qwqerOrderId . ".pdf";
                $buttonList->add(
                    'print_label',
                    [
                        'label' => __('Print Label'),
                        'onclick' => 'window.open(\'' . $url . '\', "_blank")',
                        'class' => 'reset'
                    ],
                    -1
                );
            }
        }

    }
}
