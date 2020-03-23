<?php

namespace owldesign\qarr\gql\interfaces\elements;

use owldesign\qarr\elements\Review as ReviewElement;

use Craft;
use craft\gql\interfaces\Element;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

class Review extends Element
{
    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::class, new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all review elements',
            'resolveType' => function (ReviewElement $value) {
                 return GqlEntityRegistry::getEntity(self::class);
            }
        ]));

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'ReviewInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return array_merge(parent::getFieldDefinitions(), [
            'fullName' => [
                'name' => 'fullName',
                'type' => Type::string(),
                'description' => 'Review\'s author full name.'
            ],
//            'emailAddress' => [
//                'name' => 'emailAddress',
//                'type' => Type::string(),
//                'description' => 'Review\'s author email address.'
//            ],
//            'feedback' => [
//                'name' => 'feedback',
//                'type' => Type::string(),
//                'description' => 'Review feedback.'
//            ],
//            'rating' => [
//                'name' => 'rating',
//                'type' => Type::int(),
//                'description' => 'Review rating.'
//            ],
//            'status' => [
//                'name' => 'status',
//                'type' => Type::string(),
//                'description' => 'Review status.'
//            ],
//            'abuse' => [
//                'name' => 'abuse',
//                'type' => Type::id(),
//                'description' => 'Checks if review was reported as abuse.'
//            ],
//            'hasPurchased' => [
//                'name' => 'hasPurchased',
//                'type' => Type::id(),
//                'description' => 'Checks if author has purchased reviewed item.'
//            ],
//            'displayId' => [
//                'name' => 'displayId',
//                'type' => Type::id(),
//                'description' => 'Display ID for a given review element.'
//            ],
//            'elementId' => [
//                'name' => 'elementId',
//                'type' => Type::id(),
//                'description' => 'Element ID for a given review element.'
//            ],
//            'sectionId' => [
//                'name' => 'sectionId',
//                'type' => Type::id(),
//                'description' => 'Section ID for a given review element.'
//            ],
//            'structureId' => [
//                'name' => 'structureId',
//                'type' => Type::id(),
//                'description' => 'Structure ID for a given review element.'
//            ],
//            'productTypeId' => [
//                'name' => 'productTypeId',
//                'type' => Type::id(),
//                'description' => 'Product Type ID for a given review element.'
//            ],
//            'geolocation' => [
//                'name' => 'geolocation',
//                'type' => Type::string(),
//                'description' => 'Geolocation for the submitted review.'
//            ],
//            'ipAddress' => [
//                'name' => 'ipAddress',
//                'type' => Type::string(),
//                'description' => 'IP Address of the review submission.'
//            ],
//            'userAgent' => [
//                'name' => 'userAgent',
//                'type' => Type::string(),
//                'description' => 'User Agent of the review submission.'
//            ],
        ]);
    }
}