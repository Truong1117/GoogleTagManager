<?php
namespace WeltPixel\GoogleTagManager\Block;

/**
 * Class \WeltPixel\GoogleTagManager\Block\Order
 */
class Order extends \WeltPixel\GoogleTagManager\Block\Core
{
    /**
     * Returns the product details for the purchase gtm event
     * @return array
     */
    public function getProducts() {
        $order = $this->getOrder();
        $products = [];

        $displayOption = $this->helper->getParentOrChildIdUsage();

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($displayOption == \WeltPixel\GoogleTagManager\Model\Config\Source\ParentVsChild::CHILD) {
                if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $children = $item->getChildrenItems();
                    foreach ($children as $child) {
                        $product = $child->getProduct();
                    }
                }
            }

            $productDetail = [];
            $productDetail['name'] = html_entity_decode($item->getName());
            $productDetail['id'] = $this->helper->getGtmProductId($product); //$this->helper->getGtmOrderItemId($item);
            $productDetail['price'] = number_format($item->getPrice(), 2, '.', '');
            if ($this->helper->isBrandEnabled()) :
                $productDetail['brand'] = $this->helper->getGtmBrand($product);
            endif;
            $categoryName = $this->helper->getGtmCategoryFromCategoryIds($product->getCategoryIds());
            $productDetail['category'] = $categoryName;
            $productDetail['list'] = $categoryName;
            $productDetail['quantity'] = $item->getQtyOrdered();
            $products[] = $productDetail;
        }

        return $products;
    }

    /**
     * Returns the product id's
     * @return array
     */
    public function getProductIds() {
        $order = $this->getOrder();
        $products = [];

        $displayOption = $this->helper->getParentOrChildIdUsage();

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($displayOption == \WeltPixel\GoogleTagManager\Model\Config\Source\ParentVsChild::CHILD) {
                if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $children = $item->getChildrenItems();
                    foreach ($children as $child) {
                        $product = $child->getProduct();
                    }
                }
            }

            $products[] = $this->helper->getGtmProductId($product); //$this->helper->getGtmOrderItemId($item);
        }

        return $products;
    }


    /**
     * Retuns the order total (subtotal or grandtotal)
     * @return float
     */
    public function getOrderTotal() {
        $orderTotalCalculationOption = $this->helper->getOrderTotalCalculation();
        $order =  $this->getOrder();
        switch ($orderTotalCalculationOption) {
            case \WeltPixel\GoogleTagManager\Model\Config\Source\OrderTotalCalculation::CALCULATE_SUBTOTAL :
                $orderTotal = $order->getSubtotal();
                break;
            case \WeltPixel\GoogleTagManager\Model\Config\Source\OrderTotalCalculation::CALCULATE_GRANDTOTAL :
            default:
                $orderTotal = $order->getGrandtotal();
                if ($this->excludeTaxFromTransaction()) {
                    $orderTotal -= $order->getTaxAmount();
                }
                if ($this->excludeShippingFromTransaction()) {
                    $orderTotal -= $order->getShippingAmount();
                    if ($this->excludeShippingFromTransactionIncludingTax()) {
                        $orderTotal -= $order->getShippingTaxAmount();
                    }
                }
            break;
        }

        return $orderTotal;
    }
}
