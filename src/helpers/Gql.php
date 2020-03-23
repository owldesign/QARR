<?php

namespace owldesign\qarr\helpers;

use Craft;
use craft\errors\GqlException;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;
use craft\helpers\StringHelper;
use GraphQL\Type\Definition\UnionType;

class Gql
{
    private static $cachedPairs = [];

    public static function canQueryReviews(): bool
    {
        // TODO: whats this?
        return isset(self::extractAllowedEntitiesFromSchema()['']);
    }

    /**
     * Extracts all the allowed entities from the active schema for the action.
     *
     * @param string $action The action for which the entities should be extracted. Defaults to "read"
     * @return array
     * @throws GqlException
     */
    public static function extractAllowedEntitiesFromSchema($action = 'read'): array
    {
        $activeSchema = Craft::$app->getGql()->getActiveSchema();

        if (empty(self::$cachedPairs[$activeSchema->id])) {
            try {
                $permissions = (array) $activeSchema->scope;
                $pairs = [];

                foreach ($permissions as $permission) {
                    // Check if this is for the requested action
                    if (StringHelper::endsWith($permission, ':' . $action)) {
                        $permission = StringHelper::removeRight($permission, ':' . $action);

                        $parts = explode('.', $permission);

                        if (count($parts) === 2) {
                            $pairs[$parts[0]][] = $parts[1];
                        }
                    }
                }

                self::$cachedPairs[$activeSchema->id] = $pairs;
            } catch (GqlException $exception) {
                Craft::$app->getErrorHandler()->logException($exception);
                return [];
            }
        }

        return self::$cachedPairs[$activeSchema->id];
    }
}