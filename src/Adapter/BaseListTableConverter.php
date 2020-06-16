<?php

namespace Pacolmg\SymfonyFilterBundle;

use Pacolmg\SymfonyFilterBundle\Model\FilteredTableModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseListTableConverter
 * @package Pacolmg\SymfonyFilterBundle
 * @author Pacolmg <pacolmg@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class BaseListTableConverter
{
    protected const PARAM_TYPE_STRING = 'string';
    protected const PARAM_TYPE_INT = 'int';
    protected const PARAM_TYPE_BOOL = 'bool';

    /**
     * @param Request $request
     * @param array $arrayFilters
     * @return FilteredTableModel
     */
    protected function baseApply(Request $request, array $arrayFilters)
    {
        // page
        $page = (int)$request->get('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        // limit
        $limit = (int)$request->get('limit', 0);
        if ($limit < 1) {
            $limit = 1;
        }

        // sort
        $sort = $request->get('sort');
        $dir = $request->get('dir');
        if (is_string($sort)) {
            $orderBy = ["$sort" => $dir ? strtoupper($dir) : 'ASC'];
        } else {
            $orderBy = [];
        }

        // filters
        foreach ($arrayFilters as $k => $filter) {
            switch ($filter['request_type']) {
                case self::PARAM_TYPE_INT:
                    $arrayFilters[$k]['value'] = (int)$request->get($filter['request_name']);
                    break;
                case self::PARAM_TYPE_BOOL:
                    $arrayFilters[$k]['value'] = ($request->get($filter['request_name']) === 'true' ? true : ($request->get($filter['request_name']) === 'false' ? false : ''));
                    break;
                default:
                    $arrayFilters[$k]['value'] = (string)$request->get($filter['request_name']);
            }
        }

        return new FilteredTableModel($page, $limit, $orderBy, $arrayFilters);
    }
}