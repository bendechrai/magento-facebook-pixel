<?php

class CoreValue_FacebookPixel_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isPixelEnabled()
    {
        return Mage::getStoreConfig("corevalue_facebookpixel/tracking/enabled");
    }

    public function isConversionEnabled()
    {
        return Mage::getStoreConfig("corevalue_facebookpixel/tracking/conversion");
    }

    public function getPixelId()
    {
        return Mage::getStoreConfig("corevalue_facebookpixel/tracking/pixel_id");
    }

    /**
     *
     * @param $quote Mage_Sales_Model_Quote
     * @return string
     */
    public function getCartProductIds($quote)
    {
        $productIds = [];
        /**
         * @var $item Mage_Catalog_Model_Product
         */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId()) {continue;}
            $productIds[] = $item->getSku();
        }

        return implode(', ', $productIds);
    }

    /**
     * @param array $categoryIds
     * @return string
     */
    public function getProductCategories($categoryIds)
    {
        $categories = [];

        if (is_array($categoryIds) && (count($categoryIds) > 0)) {
            foreach ($categoryIds as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $categories[] = $category->getName();
            }
        }

        return implode(', ', $categories);
    }
}