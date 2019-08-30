<?php

namespace owldesign\qarr\controllers\campaigns;

use Craft;
use craft\web\Controller;

use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\web\View;
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
        $variables              = [];
        $formVariables          = [];
        $view                   = Craft::$app->getView();
        $siteUrl                = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
        $request                = Craft::$app->getRequest();
        $customerEmail          = $request->getQueryParam('email');
        $orderNumber            = $request->getQueryParam('orderNumber');
        $productId              = $request->getQueryParam('productId');

        $isReady             = false;
        $hasMultipleProducts = false;
        $variables['orders'] = Commerce::getInstance()->getOrders()->getOrdersByEmail($customerEmail);

        if (count($variables['orders']) === 0) {
            return $this->redirect($siteUrl);
        }

        if ($customerEmail && $orderNumber && $productId) {
            $isReady = true;
            $order = Commerce::getInstance()->getOrders()->getOrderByNumber($orderNumber);
            $formVariables = [
                'email' => $customerEmail,
                'name' => $order->shippingAddress->fullName,
                'order' => $order,
                'product' => Commerce::getInstance()->getProducts()->getProductById($productId),
                'customer' => $order->getCustomer()
            ];
        }

        if (count($variables['orders']) >= 1 && !$isReady) {
            $hasMultipleProducts = true;

            foreach($variables['orders'] as $order) {
                foreach($order->lineItems as $key => $item) {
                    $variables['items'][$order->id]['order'] = $order;
                    $variables['items'][$order->id]['links'][$key] = [
                        'details' => $item,
                        'url' => $siteUrl . 'c/r?email=' . $customerEmail . '&orderNumber=' . $order->number . '&productId=' .$item->snapshot['productId']
                    ];
                }
            }
        }


        $path           = $view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr/direct/commerce';
        $customFile     = $this->_resolveTemplate($path, 'review');

        if ($customFile) {
            if ($hasMultipleProducts) {
                $view->setTemplateMode(View::TEMPLATE_MODE_CP);
                return $this->renderTemplate('qarr/campaigns/direct/commerce/_links', $variables);
            } else {
                return $this->renderTemplate($customFile, $formVariables);
            }
        } else {
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);
            if ($hasMultipleProducts) {
                return $this->renderTemplate('qarr/campaigns/direct/commerce/_links', $variables);
            } else {
                return $this->renderTemplate('qarr/campaigns/direct/commerce/review', $formVariables);
            }
        }
    }

    /**
     * Function to get custom templates path
     *
     * @param string $path
     * @param string $name
     * @return string
     */
    private function _resolveTemplate(string $path, string $name)
    {
        foreach ($this->defaultTemplateExtensions as $extension) {
            $testPath = $path . DIRECTORY_SEPARATOR . $name . '.' . $extension;

            if (is_file($testPath)) {
                return 'qarr' . DIRECTORY_SEPARATOR . 'direct/commerce' . DIRECTORY_SEPARATOR . $name . '.' . $extension;
            }
        }
    }
}