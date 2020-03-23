<?php

namespace owldesign\qarr\gql\types\elements;

use owldesign\qarr\elements\Review as ReviewElement;
use owldesign\qarr\gql\interfaces\elements\Review as ReviewInterface;

use craft\gql\interfaces\Element as ElementInterface;
use craft\gql\base\ObjectType;

use GraphQL\Type\Definition\ResolveInfo;

class Review extends ObjectType
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            ReviewInterface::getType(),
            ElementInterface::getType()
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        /** @var ReviewElement */
        $fieldName = $resolveInfo->fieldName;

        return $source->fieldName;
    }
}