<?php

namespace Pacolmg\SymfonyFilterBundle\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class externalParametersService
 *
 * Functions to get the parameters from the Request
 *
 * @package Pacolmg\SymfonyFilterBundle\Service
 * @author Pacolmg <pacolmg@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class externalParametersService
{
    /**
     * @var int
     */
    protected $defaultLimit;

    /**
     * externalParametersFunction constructor.
     *
     * @param int|null $defaultLimit
     */
    public function __construct(int $defaultLimit = null)
    {
        $this->defaultLimit = $defaultLimit;

        if ($this->defaultLimit === null) {
            $this->defaultLimit = 10;
        }
    }

    /**
     * @return int
     */
    public function getDefaultLimit()
    {
        return $this->defaultLimit;
    }

    /**
     * Get the page and limit from the $request
     *  - Maximum limit is 500
     *  - Minimum page is 1
     *  - You can use other parameters for 'page' and 'limit', pass them as $keyPage and $keyLimit
     *
     * @param Request $request
     * @param string|null $keyPage
     * @param string|null $keyLimit
     * @return array
     */
    public function getPageAndLimit(Request $request, string $keyPage = null, string $keyLimit = null)
    {
        $page = (int)$request->get($keyPage ?? 'page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $limit = (int)$request->get($keyLimit ?? 'limit', $this->defaultLimit);

        if ($limit > 500) {
            $limit = 500;
        }

        if ($limit < 1) {
            $limit = 1;
        }

        return [$page, $limit];
    }

    /**
     * Take an array of filters an convert the value to the proper type
     *
     * @param Request $request
     * @param array $arrayFilters
     * @return array
     */
    public function getFilters(Request $request, array $arrayFilters = [])
    {
        foreach ($arrayFilters as $k => $filter) {
            switch ($filter['request_type']) {
                case 'int':
                    $arrayFilters[$k]['value'] = (int)$request->get($filter['request_name']);
                    break;
                case 'bool':
                    $arrayFilters[$k]['value'] = ($request->get($filter['request_name']) === 'true' ? true : ($request->get($filter['request_name']) === 'false' ? false : ''));
                    break;
                case 'array':
                    $arrayFilters[$k]['value'] = array_filter((array)$request->get($filter['request_name']), function ($value) {
                        return !empty($value);
                    });

                    break;
                default:
                    $arrayFilters[$k]['value'] = trim((string)$request->get($filter['request_name']));
            }
        }

        return $arrayFilters;
    }
}
