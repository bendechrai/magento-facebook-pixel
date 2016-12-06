#ABOUT

This module allows you report and optimize for conversions, build audiences and get insights about how people use your website,
using [Facebook Pixel](https://developers.facebook.com/docs/marketing-api/facebook-pixel/v2.5) marketing api.

**At this moment it report fires when:**

* A product had been added to cart
* A product had been added to wishlist
* A product had been added to cart from wishlist
* Customer completed registration
* Customer subscribes on newsletter
* Customer subscribes on newsletter via account
* Payment info has been passed
* After adding/editing customer's billing info

#Installation

To install this extension you need to execute following commands:
* modman init
* modman clone git@github.com:thecvsi/magento-facebook-pixel.git

To update extension to latest version you need to run following command:

* modman update magento-facebook-pixel

#Close look

####Info which tracking on `AddToCart` event

| Facebook Pixel Value name | Passed Value |
| ------------------------- | :----------- |
| content_name              | *$_product->getName()* |
| content_type              | *'product'*            |
| content_ids               | *$_product->getSku()*  |
| value                     | *$_product->getPrice()*|
| currency                  | *Mage::app()->getStore()->getCurrentCurrencyCode()*|

####Info which tracking on `AddToWishlist` event

| Facebook Pixel Value name | Passed Value |
| ------------------------- | :----------- |
| content_name              | *$_product->getName()* |
| content_ids               | *$_product->getSku()*  |
| content_category          | *'product'*            |
| value                     | *$_product->getPrice()*|
| currency                  | *Mage::app()->getStore()->getCurrentCurrencyCode()*|


**Magento Support:**

* [Homepage](http://magento.com) (v.1.9.x)
* [Documentation](http://docs.magentocommerce.com)

