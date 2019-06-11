<?php

namespace owldesign\qarr\controllers\backend;

use craft\db\Paginator;
use craft\helpers\Json;
use craft\web\twig\variables\Paginate;
use owldesign\qarr\elements\Display;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class ElementsController  extends Controller
{
    public function actionQueryElementsMeta()
    {
        // Fetch Reviews
        $reviews = [
            'total' => (int)Review::find()->count(),
            'pending' => (int)Review::find()->where(['status' => 'pending'])->count(),
            'approved' => (int)Review::find()->where(['status' => 'approved'])->count(),
            'rejected' => (int)Review::find()->where(['status' => 'rejected'])->count(),
        ];

        // Fetch Questions
        $questions = [
            'total' => (int)Question::find()->count(),
            'pending' => (int)Question::find()->where(['status' => 'pending'])->count(),
            'approved' => (int)Question::find()->where(['status' => 'approved'])->count(),
            'rejected' => (int)Question::find()->where(['status' => 'rejected'])->count(),
        ];

        // Fetch Displays
        $displays = [
            'total' => (int)Display::find()->count(),
        ];

        $data = [
            'reviews' => $reviews,
            'questions' => $questions,
            'displays' => $displays,
        ];
        
        return $this->asJson($data);
    }

    /**
     * Query builder for elements
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionQueryElements()
    {
        $this->requirePostRequest();

        $request        = Craft::$app->getRequest();
        $elementType    = $request->getBodyParam('elementType');
        $payload        = Json::decode($request->getRequiredBodyParam('query'));
        $currentPage    = $payload['currentPage'];
        $limit          = $payload['limit'];

        // Query
        $query = $this->_getElementClass($elementType);
        $query->limit($limit);
        
        // Sources
        $source = $this->_getSource($payload['source']);
        $this->_buildQuery($query, $source);

        // Get entries
        $paginated = $this->_paginateCriteria($query, $currentPage);

        return $this->asJson([
            'entries' => $paginated['entries'],
            'pager' => $paginated['pager'],
        ]);
    }

    private function _buildQuery(&$query, $source)
    {
        if ($source['type'] == 'single') {
            $sections = Craft::$app->getSections()->getAllSections();
            $sectionIds = [];

            foreach($sections as $section) {
                if ($section->type == 'single') {
                    $sectionIds[] = $section->id;
                }
            }

            $query->where(['sectionId' => $sectionIds]);
        } elseif ($source['type'] == 'channel') {
            $query->where(['sectionId' => $source['id']]);
        } elseif ($source['type'] == 'productType') {
            $query->where(['productTypeId' => $source['id']]);
        }
    }

    private function _paginateCriteria($query, $currentPage)
    {
        $paginator = new Paginator((clone $query)->limit(null), [
            'currentPage' => $currentPage,
            'pageSize' => $query->limit ?: 100,
        ]);

        $elements = $paginator->getPageResults();

        foreach($elements as $element) {
            $geolocation = Json::decode($element->geolocation);
            if (isset($geolocation['country_code'])) {
                $element->geolocation = $geolocation;
            } else {
                $element->geolocation = null;
            }
            $element['element'] = $element->getElement();
            $element['elementType'] = $element->getElementType();
            $element['elementSource'] = $element->getElementSource();
        }

        return [
            'pager' => Paginate::create($paginator),
            'entries' => $elements
        ];
    }
    
    private function _getSource($source)
    {
        $sourceArr      = explode(':', $source);
        $sourceType     = $sourceArr[0];
        if ($sourceType != '*') {
            $sourceId       = $sourceArr[1];
        } else {
            $sourceId       = '*';
        }
        
        return [
            'type'  => $sourceType,
            'id'    => $sourceId
        ];
    }

    private function _getElementClass($type)
    {
        if ($type === 'reviews') {
            return Review::find();
        } elseif ($type === 'questions') {
            return Question::find();
        }

        return false;
    }
}