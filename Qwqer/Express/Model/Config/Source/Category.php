<?php

namespace Qwqer\Express\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Category implements ArrayInterface
{
    /**
     * ToOptionArray
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'Other', 'label' => __('Other')],
            ['value' => 'Flowers', 'label' => __('Flowers')],
            ['value' => 'Food', 'label' => __('Food')],
            ['value' => 'Electronics', 'label' => __('Electronics')],
            ['value' => 'Cake', 'label' => __('Cake')],
            ['value' => 'Present', 'label' => __('Present')],
            ['value' => 'Clothes', 'label' => __('Clothes')],
            ['value' => 'Document', 'label' => __('Document')],
            ['value' => 'Jewelry', 'label' => __('Jewelry')],
        ];
    }
}
