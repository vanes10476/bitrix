<?php

$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID);

$basket->refreshData(array('PRICE', 'COUPONS'));

$discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true)));

$giftManager = \Bitrix\Sale\Discount\Gift\Manager::getInstance();
$collections = $giftManager->getCollectionsByBasket($basket);
if (is_array($collections) && !empty($collections))
{
	foreach ($collections as $collection) {
		$arGifts = $collection->getValues();
	}

	$giftId = $arGifts[array_rand($arGifts)]->getProductId();
}

if ($giftId)
{
	$product = array(

		'PRODUCT_ID' => $giftId,
	
		'QUANTITY' => 1,
	);
	
	$rewriteFields = array(
	
		'PRICE'=> 0.00,
	
		'CUSTOM_PRICE'=>'Y',
	
		'PRODUCT_PROVIDER_CLASS'=>'', // это поле необходимо при 'CUSTOM_PRICE'=>'Y', иначе будет отображаться скидка от цены товара c PRODUCT_ID
	
		'CURRENCY'=>'RUB', // это нужно когда не указан 'PRODUCT_PROVIDER_CLASS'
	
	);
	
	$basketResult = \Bitrix\Catalog\Product\Basket::addProduct($product, $rewriteFields/* $options */);

	if ($basketResult->isSuccess())
	
	{
	
		$basket = \Bitrix\Sale\Basket::loadItemsForFUser(
	
			\Bitrix\Sale\Fuser::getId(), 
	
			\Bitrix\Main\Context::getCurrent()->getSite()
	
		);

		$basket->refresh();    
		$basket->save();

	}

} else {
	$basketItems = $basket->getBasketItems();
	foreach ($basket as $basketItem) {
		if ($basketItem->getFinalPrice() == 0)
		{
			$basketItem->delete();
			$basket->save();
		}
	}
}