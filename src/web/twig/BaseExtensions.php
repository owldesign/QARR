<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\web\twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class BaseExtensions extends AbstractExtension implements GlobalsInterface
{
    // Public Methods
    // =========================================================================

    public function getName()
    {
        return 'QARR Base';
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return [
            'qarr' => new BaseVariables()
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('getClass', [$this, 'getClass']),
        ];
    }

    /**
     * Get clean element class name
     *
     * @param $object
     * @return string
     * @throws \ReflectionException
     */
    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}
