<?php
/**
 * Created by PhpStorm.
 * User: amuh
 * Date: 12/20/2019
 * Time: 11:19 PM
 */

namespace MagArs\CODRestriction\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class PaymentMethodAvailable implements ObserverInterface {

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        $items = $this->cart->getQuote()->getAllItems();
        $codFlag = true;
        foreach($items as $item) {
            $productId = $item->getProductId();
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);
            if(!$product->getCustomAttribute('allow_cod')->getValue()){
                $codFlag = false;
                break;
            }
        }
        if($observer->getEvent()->getMethodInstance()->getCode() == "cashondelivery"){
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', $codFlag);
        }
    }
}
