<?php

namespace Pacolmg\SymfonyFilterBundle\Model;

use Exception;

/**
 * Class FilteredTableModel
 * @package Pacolmg\SymfonyFilterBundle\Model
 * @author Pacolmg <pacolmg@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class FilteredTableModel
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 500;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int 
     */
    private $total;

    /**
     * FilteredTableModel constructor.
     * @param int|null $page
     * @param int|null $limit
     * @param array $sort
     * @param array $filters
     * @param array $data
     * @param int|null $total
     */
    public function __construct(
        int $page = null,
        int $limit = null,
        array $sort = [],
        array $filters = [],
        array $data = [],
        int $total = null
    ) {
        $this->setPage($page ?? self::DEFAULT_PAGE);
        $this->setLimit($limit ?? self::DEFAULT_LIMIT);
        $this->sort = $sort;
        $this->filters = $filters;
        $this->data = $data;
        $this->total = $total ?? count($data);
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page): self
    {
        if ($page < 1 || !$page) {
            $page = self::DEFAULT_PAGE;
        }


        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        if ($limit < 1 || !$limit) {
            $limit = self::DEFAULT_LIMIT;
        }
        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     * @return $this
     */
    public function setSort(array $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return array_filter($this->filters, function ($value) {
            return !empty($value['value']) || $value['value'] === false;
        });
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total): self
    {
        if ($total < 1 || !$total) {
            $total = self::DEFAULT_PAGE;
        }


        $this->total = $total;
        return $this;
    }
    /**
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        try {
            return ceil($this->getTotal() / $this->getLimit());
        } catch (Exception $e) {
            return 1;
        }
    }
}