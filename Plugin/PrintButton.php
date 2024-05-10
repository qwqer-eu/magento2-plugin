<?php

namespace Qwqer\Express\Plugin;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar\Interceptor;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Qwqer\Express\Model\Carrier\Express;
use Qwqer\Express\Model\Carrier\ScheduledToDoor;
use Qwqer\Express\Model\Carrier\ScheduledToParcel;

class PrintButton
{

    /**
     * *
     *
     * @var RequestInterface
     */
    private RequestInterface $_request;

    /**
     * *
     *
     * @var UrlInterface
     */
    private UrlInterface $_backendUrl;

    /**
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        UrlInterface $backendUrl
    ) {
        $this->_backendUrl = $backendUrl;
    }

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
                $shippingMethod = $order->getShippingMethod(true);
                $qwqerOrderId = $order->getData('qwqer_order_id');
                if ($qwqerOrderId) {
                    $url = "https://qwqer.hostcream.eu/storage/delivery-order-covers/" . $qwqerOrderId . ".pdf";
                    $buttonList->add(
                        'print_label',
                        [
                            'label' => __('Print QWQER Label'),
                            'onclick' => 'window.open(\'' . $url . '\', "_blank")',
                            'class' => 'reset'
                        ],
                        -1
                    );
                } elseif ($shippingMethod
                    && ($shippingMethod->getData('carrier_code') == Express::CARRIER_CODE
                        || $shippingMethod->getData('carrier_code') == ScheduledToDoor::CARRIER_CODE
                        || $shippingMethod->getData('carrier_code') == ScheduledToParcel::CARRIER_CODE
                    )
                ) {
                    $orderId = $this->_request->getParam('order_id');
                    $buttonList->add(
                        'sync_order_to_qwqer',
                        [
                            'label' => __('Sync to QWQER'),
                            'onclick' => 'setLocation(\'' . $this->getSynclUrl($orderId) . '\')', 'class' => 'reset',
                            'class' => 'reset'
                        ],
                        -1
                    );
                }
            }
        }
    }

    /**
     * *
     *
     * @param mixed $id
     * @return string
     */
    public function getSynclUrl(mixed $id)
    {
        return $this->_backendUrl->getUrl(
            'qwqer/*/sync',
            [
                'order_id' => $id,
            ]
        );
    }
}
