<?php


namespace owldesign\qarr\controllers;

use Craft;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\errors\InvalidTypeException;
use craft\web\Controller;
use craft\web\View;
use owldesign\qarr\QARR;
use owldesign\qarr\web\twig\Render;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class RenderElementsController extends Controller
{
    /**
     * @var array
     */
    protected $allowAnonymous = ['get-elements', 'count-elements'];

    protected $elementType;

    /**
     * @var ElementQueryInterface|ElementQuery|null
     */
    protected $elementQuery;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->elementType = $this->elementType();
        $this->elementQuery = $this->elementQuery();

        return true;
    }

    /**
     * @return ElementQueryInterface
     */
    public function getElementQuery(): ElementQueryInterface
    {
        return $this->elementQuery;
    }

    /**
     * Get elements
     *
     * @return Response
     */
    public function actionGetElements(): Response
    {
        $elementType = new $this->elementType;
        $responseData = [];

        $responseData['html'] = $elementType::renderElementsHtml(
            $this->elementQuery,
        );

        return $this->asJson($responseData);
    }

    /**
     * Count elements
     *
     * @return Response
     */
    public function actionCountElements(): Response
    {
        return $this->asJson([
            'resultSet' => $this->request->getParam('resultSet'),
            'count' => (int)$this->elementQuery
                ->select(new Expression('1'))
                ->count(),
        ]);
    }

    /**
     * Get modal content
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionGetModalContent(): Response
    {
        $this->requirePostRequest();

        $variables = [];
        $variables['elementType'] = Craft::$app->getRequest()->getBodyParam('elementType');
        $variables['displayHandle'] = Craft::$app->getRequest()->getBodyParam('displayHandle');
        $variables['elementId'] = Craft::$app->getRequest()->getBodyParam('elementId');

        // Display
        if ($variables['displayHandle']) {
            $variables['display'] = QARR::$plugin->displays->getDisplayByHandle($variables['displayHandle']);
        }

        $render = new Render();
        $customFile = $render->_resolveTemplate(Craft::$app->view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr', 'review-form');

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/review-form', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        return $this->asJson([
            'template' => $html
        ]);
    }

    /**
     * Return element type
     *
     * @return string
     * @throws BadRequestHttpException
     */
    protected function elementType(): string
    {
        $class = $this->request->getRequiredParam('elementType');

        try {
            if (!is_subclass_of($class, ElementInterface::class)) {
                throw new InvalidTypeException($class, ElementInterface::class);
            }
        } catch (InvalidTypeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return $class;
    }

    /**
     * Get element query
     *
     * @return ElementQueryInterface
     */
    protected function elementQuery(): ElementQueryInterface
    {
        /** @var string|ElementInterface $elementType */
        $elementType = $this->elementType;
        $query = $elementType::find();

        if ($criteria = $this->request->getBodyParam('criteria')) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }
}