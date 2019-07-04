<?php

namespace owldesign\qarr\controllers\campaigns;

use Craft;
use craft\web\Controller;

use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use yii\web\Response;

class DirectController extends Controller
{

    /**
     * @var array
     */
    protected $allowAnonymous = true;

    // Public Properties
    // =========================================================================
    public $defaultTemplateExtensions = ['html', 'twig'];
    
    // Public Methods
    // =========================================================================
    public function actionReview(): Response
    {
        $request            = Craft::$app->getRequest();
        $customerEmail      = $request->getQueryParam('email');
        $customerOrderId    = $request->getQueryParam('orderId');

//        $orderService       craft\commerce\services\Orders
        Craft::dd(Commerce::getInstance()->getOrders()->getOrdersByEmail($customerEmail));


//        $order = Order::find()->id
        Craft::dd(Order::find());

        
        Craft::dd($customerOrderId);
    }
}