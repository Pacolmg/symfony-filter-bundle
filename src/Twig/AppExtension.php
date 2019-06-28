<?php

namespace Pacolmg\SymfonyFilterBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtension
 *
 * @package Pacolmg\SymfonyFilterBundle\Twig
 * @author Pacolmg <pacolmg@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class AppExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('filters_to_path', [$this, 'filtersToPath'])
        ];
    }

    /**
     * Encode the filters in order to concatenate them after a url
     *
     * @param $queryParams
     * @param $filters
     * @return string
     */
    public function filtersToPath($queryParams, $filters)
    {
        $filters = array_merge($filters, ['page', 'limit']);
        $filterPath = '';
        foreach ($filters as $filter) {
            if (isset($queryParams[$filter])) {
                $filterPath = sprintf('%s%s', $filterPath, sprintf('&%s=%s', $filter, is_array($queryParams[$filter]) ? join(',', $queryParams[$filter]) : $queryParams[$filter]));
            }
        }

        return $filterPath;
    }
}
