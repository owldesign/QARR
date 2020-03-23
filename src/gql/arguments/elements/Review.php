<?php

namespace owldesign\qarr\gql\arguments\elements;

use craft\gql\base\ElementArguments;

use GraphQL\Type\Definition\Type;

class Review extends ElementArguments
{
    public static function getArguments(): array
    {
        return array_merge(parent::getArguments(), [
            'rating' => [
                'name' => 'rating',
                'type' => Type::listOf(Type::int()),
                'description' => 'Narrows the query results based on the review rating.'
            ]
        ]);
    }
}