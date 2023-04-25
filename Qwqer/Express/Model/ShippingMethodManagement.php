<?php

namespace Qwqer\Express\Model;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Qwqer\Express\Model\Api\GeoCode;
use Qwqer\Express\Model\Api\ShippingCost;

/**
 * Save QWQER address to customer attributes in quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ShippingMethodManagement implements \Qwqer\Express\Api\ShipmentEstimationInterface
{
    /**
     * Quote repository model
     *
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $quoteRepository;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @var GeoCode
     */
    protected GeoCode $geoCode;

    /**
     * @var ShippingCost
     */
    protected ShippingCost $shippingCost;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Request $request
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GeoCode $geoCode
     * @param ShippingCost $shippingCost
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Request $request,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GeoCode $geoCode,
        ShippingCost $shippingCost
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->geoCode = $geoCode;
        $this->shippingCost = $shippingCost;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function estimateByExtendedAddress(string $cartId)
    {
        try {
            $quote = $this->quoteRepository->getActive($cartId);
        } catch (\Exception $e) {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        }

        /** @var Quote $quote */
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $params = $this->request->getBodyParams();
        $price = 0;
        if (isset($params['address']['data'])) {
            try {
                $addressString = $params['address']['data'];
                $params = ['address' => $addressString];
                $coordinates = $this->geoCode->executeRequest($params);
                if (!empty($coordinates)) {
                    $orderDataRequest = array_merge($params, $coordinates);
                    $result = $this->shippingCost->executeRequest($orderDataRequest);
                    if (!empty($result['data']) && isset($result['data']['client_price'])) {
                        $price = $result['data']['client_price'] / 100;
                        $quote->getShippingAddress()->getExtensionAttributes()->setQwqerAddress($addressString);
                    }
                }
            } catch (\Exception $e) {
                //skip
            }
        }
        return [$price];
    }
}
