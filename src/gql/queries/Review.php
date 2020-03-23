<?php

namespace owldesign\qarr\gql\queries;

use owldesign\qarr\helpers\Gql as GqlHelper;
use owldesign\qarr\gql\arguments\elements\Review as ReviewArguments;
use owldesign\qarr\gql\interfaces\elements\Review as ReviewInterface;
use owldesign\qarr\gql\resolver\elements\Review as ReviewResolver;
use craft\gql\base\Query;

use GraphQL\Type\Definition\Type;

class Review extends Query
{
    public static function getQueries($checkToken = true): array
    {
        // TODO: Updated this
//        if ($checkToken && !GqlHelper::canQueryReviews()) {
//            return [];
//        }

        return [
            'reviews' => [
                'type' => Type::listOf(ReviewInterface::getType()),
                'args' => ReviewArguments::getArguments(),
                'resolve' => ReviewResolver::class . '::resolve',
                'description' => 'This query is ued to query for reviews'
            ]
        ];
    }
}