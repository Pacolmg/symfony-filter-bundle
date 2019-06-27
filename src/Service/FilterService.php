<?php

namespace Pacolmg\SymfonyFilterBundle\Service;

use Pacolmg\SymfonyFilterBundle\Repository\BaseRepository;

/**
 * Class FilterService
 *
 * Functions to get objects filtered from a repository
 *
 * @package Pacolmg\SymfonyFilterBundle\Service
 * @author Pacolmg <pacolmg@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class FilterService
{
    /**
     * Get all the objects filtered from the repository
     *
     * @param BaseRepository $repository
     * @param array $filters
     * @param int $page
     * @param int|null $limit
     * @param string|null $sort
     * @param string|null $dir
     * @return array
     */
    public function getFiltered(BaseRepository $repository, array $filters = [], int $page = 1, int $limit = null, string $sort = null, string $dir = null)
    {
        if (is_string($sort)) {
            $orderBy = ["$sort" => $dir ? strtoupper($dir) : 'ASC'];
        } else {
            $orderBy = [];
        }

        $filters = array_filter($filters, function ($value) {
            return !empty($value['value']) || $value['value'] === false;
        });

        $data = $repository->getAll($filters, $orderBy, $limit, ($page - 1) * $limit);
        $total = $repository->getAllCount($filters);

        return ['total' => $total, 'data' => $data];
    }
}
