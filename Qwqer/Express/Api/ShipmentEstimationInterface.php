<?php

namespace Qwqer\Express\Api;

/**
 * Interface ShipmentManagementInterface
 * @api
 * @since 100.0.7
 */
interface ShipmentEstimationInterface
{
    /**
     * Save QWQER address to customer attributes in quote
     *
     * @param string $cartId
     * @return [] An array
     * @since 100.0.7
     */
    public function estimateByExtendedAddress(string $cartId);
}
