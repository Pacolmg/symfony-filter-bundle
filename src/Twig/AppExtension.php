<?php

namespace Pacolmg\SymfonyFilterBundle\Twig;

use Pacolmg\SymfonyFilterBundle\Model\FilteredTableModel;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('paginationData', [$this, 'paginationData']),
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
                $filterPath = sprintf('%s%s', $filterPath, sprintf('&%s=%s', $filter,
                    is_array($queryParams[$filter]) ? join(',', $queryParams[$filter]) : $queryParams[$filter]));
            }
        }

        return $filterPath;
    }

    /**
     * @param Request $request
     * @param FilteredTableModel $filteredTable
     * @return array
     */
    public function paginationData(Request $request, FilteredTableModel $filteredTable)
    {
        return [
            'currentPage' => $filteredTable->getPage(),
            'url' => $request->get('_route'),
            'params' => $request->query->all(),
            'nbPages' => $filteredTable->getTotalPages()
        ];
    }
}
