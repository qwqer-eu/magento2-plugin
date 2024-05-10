<?php

namespace Qwqer\Express\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Qwqer\Express\Model\Carrier\Express;
use Qwqer\Express\Model\Carrier\ScheduledToDoor;
use Qwqer\Express\Model\Carrier\ScheduledToParcel;
use Qwqer\Express\Service\PublishOrder;

class Sync extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * *
     *
     * @var Json
     */
    private Json $json;

    /**
     * *
     *
     * @var PublishOrder
     */
    private PublishOrder $publishOrder;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param PublishOrder $publishOrder
     * @param Json $json
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Action\Context           $context,
        Registry                 $coreRegistry,
        FileFactory              $fileFactory,
        InlineInterface          $translateInline,
        PageFactory              $resultPageFactory,
        JsonFactory              $resultJsonFactory,
        LayoutFactory            $resultLayoutFactory,
        RawFactory               $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface          $logger,
        PublishOrder             $publishOrder,
        Json $json,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->publishOrder = $publishOrder;
        $this->json = $json;
        $this->quoteRepository = $quoteRepository;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Qwqer_Express::sync';

    /**
     * Sync order with qwqer
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order = $this->_initOrder();
        if ($order && !$order->getQwqerOrderId()) {
            try {
                $quote = $this->quoteRepository->get($order->getQuoteId());
                $shippingMethod = $order->getShippingMethod(true);
                if ($shippingMethod
                    && ($shippingMethod->getData('carrier_code') == Express::CARRIER_CODE
                        || $shippingMethod->getData('carrier_code') == ScheduledToDoor::CARRIER_CODE
                        || $shippingMethod->getData('carrier_code') == ScheduledToParcel::CARRIER_CODE
                    )
                    && $quote->getShippingAddress()->getQwqerAddress()
                ) {
                    $placedOrder = $this->publishOrder->execute($order, $quote);
                    if ($placedOrder) {
                        $order->setQwqerData(json_encode($placedOrder));
                        if (!empty($placedOrder['data']['id'])) {
                            $order->addStatusHistoryComment('QWQER Order Id: ' . $placedOrder['data']['id']);
                            $order->setQwqerOrderId($placedOrder['data']['id']);
                            $this->orderRepository->save($order);
                        }
                    }
                    $this->messageManager->addSuccessMessage(__('Synced order to QWQER.'));
                } else {
                    $this->messageManager->addWarningMessage(__('Must be QWQER shipping method.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Can not sync order to QWQER.'));
            }
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
