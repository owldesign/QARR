<?php

namespace owldesign\qarr\controllers\backend;

use craft\db\Paginator;
use craft\helpers\Json;
use craft\web\twig\variables\Paginate;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class ElementsController  extends Controller
{
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
        $currentPage    = $request->getBodyParam('currentPage');
        $limit          = $request->getBodyParam('limit');
        $offset         = $request->getBodyParam('offset');

        // Query
        $query = $this->_getElementClass($elementType);
        $query->limit($limit);

        // Sources
        $source = $this->_getSource($request->getBodyParam('source'));
        $this->_buildQuery($query, $source);

        // Get entries
        $paginated = $this->_paginateCriteria($query, $currentPage);

        // Extra data
        $meta = [
            'unread' => $query->where(['isNew' => 1])->count()
        ];

        return $this->asJson([
            'entries' => $paginated['entries'],
            'pager' => $paginated['pager'],
            'meta' => $meta
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