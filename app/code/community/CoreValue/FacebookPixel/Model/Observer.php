<?php

class CoreValue_FacebookPixel_Model_Observer
{
    public function addItemToCart()
    {
        $product = Mage::getModel('catalog/product')
            ->load(Mage::app()->getRequest()->getParam('product', 0));

        if (!$product->getId()) {
            $itemId = Mage::app()->getRequest()->getParam('item');

            /* @var $item Mage_Wishlist_Model_Item */
            $item = Mage::getModel('wishlist/item')->load($itemId);

            if( !$item->getProductId() ){
                return;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }


        $categories = $product->getCategoryIds();

        Mage::getModel('core/session')->setProductToShoppingCart(
            new Varien_Object(array(
                'id' => $product->getId(),
                'qty' => Mage::app()->getRequest()->getParam('qty', 1),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'sku' => $product->getSku(),
                'category_name' => Mage::getModel('catalog/category')->load($categories[0])->getName(),
            ))
        );
    }

    public function addItemToWishlist()
    {
        $product = Mage::getModel('catalog/product')
            ->load(Mage::app()->getRequest()->getParam('product', 0));

        if (!$product->getId()) {
            return;
        }

        $categories = $product->getCategoryIds();
        Mage::getModel('core/session')->setProductToWishlist(
            new Varien_Object(array(
                'id' => $product->getId(),
                'qty' => Mage::app()->getRequest()->getParam('qty', 1),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'sku' => $product->getSku(),
                'category_name' => Mage::getModel('catalog/category')->load($categories[0])->getName(),
            ))
        );
    }

    public function onCustomerNewsletterSubscribe(){
        $email = (string) Mage::app()->getRequest()->getPost('email');
        //After we create this object in core session
        //we check it in insertLeadBlockTemplate() to add the template in "head" block
        Mage::getModel('core/session')->setSubscriber( $this->createLeadVarien($email) );

    }

    public function postdispatchNewsletterManageSave()
    {
        /**
         * @var $_customer Mage_Customer_Model_Customer
         */
        $_customer = Mage::getSingleton('customer/session')->getCustomer();
        if( $_customer->getIsSubscribed() ){
            $email = $_customer->getEmail();
            $email = (string) Mage::app()->getRequest()->getPost('email');
            //After we create this object in core session
            //we check it in insertLeadBlockTemplate() to add the template in "head" block
            Mage::getModel('core/session')->setSubscriber( $this->createLeadVarien($email) );
        }
    }


    /**
     * Here we check if $_subscriber been saved in session previously
     * and it it was saved - we simply add "lead.phtml" template to head block
     *
     * @param $observer
     */
    public function insertLeadBlockTemplate($observer)
    {

        $helper = Mage::helper("corevalue_facebookpixel");
        if (!$helper->isPixelEnabled() || !$helper->isConversionEnabled()) {
            return;
        }
        $pixelId = $helper->getPixelId();

        $add_custom_block = false;

        /**
         * @var $_subscriber Varien_Object
         */
        $_subscriber = Mage::getModel('core/session')->getSubscriber();
        if ($_subscriber && $_subscriber->getEmail()) {
            $add_custom_block = true;
            $template_name = 'facebookpixel/lead.phtml';
        }

        /**
         * @var $_subscriber Varien_Object
         */
        $_customer = Mage::getModel('core/session')->getCompleteRegistration();
        if ($_customer && $_customer->getValue()) {
            $add_custom_block = true;
            $template_name = 'facebookpixel/completeregistration.phtml';
        }

        if ( $add_custom_block === true ) {
            /** @var $_block Mage_Core_Block_Abstract */

            /*Get block instance*/
            $_block = $observer->getBlock();
            /*get Block type*/
            $_type = $_block->getType();
            /*Check block type*/
            if ($_type == 'page/html_head') {

                /*Clone block instance*/
                $_child = clone $_block;
                /*set another type for block*/
                $_child->setType('core/template');
                /*set child template*/
                $_child->setTemplate($template_name);
                /*set child for block*/
                $_block->setChild('child', $_child);
            }

        }
    }

    /*
     * Here we add data to addPaymentInfo
     */
    public function predispatchCheckoutOnepageSavePayment($observer){
        $cart = Mage::getSingleton('checkout/session')->getQuote();
        $content_category_arr = array();
        $content_ids_arr = array();

        foreach ($cart->getAllItems() as $item) {
            $product = $item->getProduct();

            $cats = $product->getCategoryIds();
            $content_ids_arr[] = $product->getID();
            foreach ($cats as $category_id) {
                $_cat = Mage::getModel('catalog/category')->load($category_id) ;
                $content_category_arr[] = json_encode(
                    array('product_id ' => $product->getID(), 'category' => $_cat->getName() )
                );
            }

        }


        Mage::getModel('core/session')->setPaymentInfo(
            new Varien_Object(array(
                'value' => $cart->getGrandTotal(),
                'currency' => $cart->getQuoteCurrencyCode(),
                'content_ids' => join(', ',$content_ids_arr),
                'content_category' => join(', ', $content_category_arr),
            ))
        );

      //  Mage::log($addPaymentInfo, null, 'var_dump.log', true);
    }

    public function customerAccountCreatepost(){
        /**
         * @var $_customer Mage_Customer_Model_Customer
         */
        $_customer = Mage::getSingleton('customer/session')->getCustomer();
        if( $_customer  ){
            Mage::getModel('core/session')->setCompleteRegistration(
                new Varien_Object(array(
                    'value' => $_customer->getEmail(),
                    'content_name' => 'customer registration',
                    'status' => 'registered',
                ))
            );
        }

    }



    /**
     * Here we create an object, which later we save in core/session object
     *
     * @param $email
     * @return Varien_Object
     */
    public function createLeadVarien($email, $content_name = 'new subscriber', $content_category = 'content_category')
    {
        return new Varien_Object(array(
            'email' => $email,
            'content_name' => $content_name,
            'content_category' => $content_category
        ));
    }

    /**
     * Here we create an object, which later we save in core/session object
     * Event fires after customer changes billing info
     *
     * @param $email
     * @return Varien_Object
     */
    public function checkoutOnepageSaveBilling()
    {
        /**
         * @var $checkout Mage_Sales_Model_Quote
         */
        $checkout = Mage::getSingleton('checkout/session')->getQuote();
        $bilAddress = $checkout->getBillingAddress()->format('text');
        if ($bilAddress) {
            Mage::getModel('core/session')->setCustomerEditAddressData(
                new Varien_Object(array(
                    'value' => preg_replace( "/\r|\n/", " ", $bilAddress ),
                    'content_name' => 'customer edit billing info',
                ))
            );
        }
    }

    /**
     * Here we create an object, which later we save in core/session object
     * Event fires after customer changes billing info in customer account
     *
     * @param $email
     * @return Varien_Object
     */
    public function customerAddressSaveAfter($observer)
    {
        /**
         * @var $observer Varien_Event_Observer
         */
        $bilAddress = $observer->getCustomerAddress()->format('text');
        if ($bilAddress) {
            Mage::getModel('core/session')->setCustomerEditAddressData(
                new Varien_Object(array(
                    'value' => preg_replace( "/\r|\n/", " ", $bilAddress ),
                    'content_name' => 'customer edit billing info',
                ))
            );
        }
    }

}