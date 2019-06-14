<?php

namespace owldesign\qarr\controllers\backend;

use craft\db\Paginator;
use craft\helpers\Json;
use craft\web\twig\variables\Paginate;
use owldesign\qarr\elements\Display;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class ElementsController  extends Controller
{
    public function actionQueryElementsMeta()
    {
        // Fetch Reviews
        $reviews = [
            'total'     => (int)Review::find()->count(),
            'pending'   => (int)Review::find()->where(['status' => 'pending'])->count(),
            'approved'  => (int)Review::find()->where(['status' => 'approved'])->count(),
            'rejected'  => (int)Review::find()->where(['status' => 'rejected'])->count(),
            'entries'   => Review::find()->all(),
        ];

        // Fetch Questions
        $questions = [
            'total'     => (int)Question::find()->count(),
            'pending'   => (int)Question::find()->where(['status' => 'pending'])->count(),
            'approved'  => (int)Question::find()->where(['status' => 'approved'])->count(),
            'rejected'  => (int)Question::find()->where(['status' => 'rejected'])->count(),
//            'entries'   => Question::find()->all(),
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

        // Status
        if ($payload['status'] !== '*') {
            $query->where(['status' => $payload['status']]);
        }

        // Sources
        $source = $this->_getSource($payload['source']);
        $this->_buildQuery($query, $source);

        // Sort
         $query->orderBy($payload['sort']);

        // Get entries
        $paginated = $this->_paginateCriteria($query, $currentPage);

        return $this->asJson([
            'entries' => $paginated['entries'],
            'pager' => $paginated['pager'],
        ]);
    }


    public function actionUpdateElementsStatus()
    {
        $this->requirePostRequest();
        $request        = Craft::$app->getRequest();
        $elementType    = $request->getBodyParam('elementType');
        $status         = $request->getBodyParam('status');
        $elements       = JSON::decode($request->getBodyParam('elements'));

        $elementService = Craft::$app->getElements();

        $savedElements = [];

        foreach ($elements as $element) {
            $entry = $elementService->getElementById($element['id']);

            if ($entry) {
                $entry->status = $status;
                $elementService->saveElement($entry, false);

                $savedElements[] = $entry;
            }
        }

        return $this->asJson([
            'elements' => $savedElements
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

            $query->andWhere(['sectionId' => $sectionIds]);
        } elseif ($source['type'] == 'channel') {
            $query->andWhere(['sectionId' => $source['id']]);
        } elseif ($source['type'] == 'productType') {
            $query->andWhere(['productTypeId' => $source['id']]);
        }
    }

    private function _paginateCriteria($query, $currentPage)
    {
        $pager = new Paginator((clone $query)->limit(null), [
            'currentPage' => $currentPage,
            'pageSize' => $query->limit ?: 100
        ]);

        $elements = $pager->getPageResults();

        foreach($elements as $element) {
            // Add geolocation
            $geolocation = Json::decode($element->geolocation);
            if (isset($geolocation['country_code'])) {
                $element->geolocation = $geolocation;
            } else {
                $element->geolocation = null;
            }

            // Add Element
            $element['element'] = $element->getElement();

            // Add Replies
            $element['response'] = $element->reply;
        }

        return [
            'pager' => Paginate::create($pager),
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