<?php

namespace owldesign\qarr\gql\resolver\elements;

use owldesign\qarr\elements\Review as ReviewElement;

use craft\gql\base\ElementResolver;

class Review extends ElementResolver
{
    public static function prepareQuery($source, array $arguments, $fieldName = null)
    {
        if ($source === null) {
            $query = ReviewElement::find();
        } else {
            $query = $source->fieldName;
        }

        if (is_array($query)) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        return $query;
    }
}