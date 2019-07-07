<?php

namespace Pacolmg\SymfonyFilterBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BaseRepository
 *
 * Extends your Symfony 4 Repositories from this class and set the filters
 *
 * @package Pacolmg\SymfonyFilterBundle\Repository
 * @author Pacolmg <pacolmg@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class BaseRepository extends ServiceEntityRepository
{
    /**
     * @var string $alias
     */
    protected $alias = 'a';

    /**
     * Different type of filters
     */
    const FILTER_EXACT = 'exact';
    const FILTER_EXACT_MULTIPLE = 'exact_multiple';
    const FILTER_LIKE = 'like';
    const FILTER_IN = 'in';
    const FILTER_GREATER = 'greater';
    const FILTER_GREATER_EQUAL = 'greaterOrEqual';
    const FILTER_LESS = 'less';
    const FILTER_LESS_EQUAL = 'lessOrEqual';
    const FILTER_DIFFERENT = 'different';

    /**
     * BaseRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param string|null $class
     */
    public function __construct(RegistryInterface $registry, string $class = null)
    {
        if ($class !== null) {
            parent::__construct($registry, $class);
        }
    }

    /**
     * Method that apply the different filters to the query
     *
     * @param QueryBuilder $qb
     * @param array $filters
     * @return QueryBuilder
     */
    protected function setFilters(QueryBuilder $qb, array $filters)
    {
        // Create an array with the keys of the fields that will be filtered
        $filterFields = array_combine(array_map(function ($filter) {
            return $filter['field'];
        }, $filters), array_map(function () {
            return 0;
        }, $filters));

        foreach ($filters as $filter) {
            //some fields has two filters, so the parameter can't be named like the field
            $filterFields[$filter['field']]++;
            $rnd = $filterFields[$filter['field']];

            // use the alias of the filter or the alias of class
            $alias = isset($filter['own_alias']) ? $filter['own_alias'] : $this->alias;

            // join
            if (isset($filter['join']) && isset($filter['own_alias'])) {
                $qb->join($this->alias . '.' . $filter['join'], $filter['own_alias']);
            }

            switch ($filter['type']) {
                case self::FILTER_EXACT:
                    $fields = explode('|', $filter['field']);
                    $sql = '';
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' = :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    foreach ($fields as $field) {
                        $qb->setParameter($this->getParameterName($field, $rnd), $filter['value']);
                    }
                    break;
                case self::FILTER_EXACT_MULTIPLE:
                    $sql = '';
                    $countFields = 0;
                    // Join the rest of the fields
                    foreach ($filter['value'] as $value) {
                        $countFields++;
                        if ($countFields == 1) {
                            continue;
                        }
                        $qb->join($this->alias . '.' . $filter['join'], $filter['own_alias'].$countFields);
                    }

                    // Make the conditions
                    $countFields = 1;
                    foreach ($filter['value'] as $value) {
                        $sql .= ($sql == '' ? '' : ' AND ') . $alias . (($countFields == 1) ? '' : $countFields) . '.' . $filter['field'] . ' = :' . $filter['field'] . '_filter_' . $rnd . $countFields;
                        $countFields++;
                    }
                    $qb->andWhere($sql);

                    // Set the parameters
                    $countFields = 1;
                    foreach ($filter['value'] as $value) {
                        $qb->setParameter($filter['field'] . '_filter_' . $rnd . $countFields, $value);
                        $countFields++;
                    }
                    break;
                case self::FILTER_IN:
                    $qb->andWhere($this->getFieldString($alias, $filter['field']) . ' IN (:' . $this->getParameterName($filter['field'], $rnd) . ')');
                    $qb->setParameter($this->getParameterName($filter['field'], $rnd), $filter['value']);
                    break;
                case self::FILTER_GREATER:
                    $qb->andWhere($this->getFieldString($alias, $filter['field']) . ' > :' . $this->getParameterName($filter['field'], $rnd));
                    $qb->setParameter($this->getParameterName($filter['field'], $rnd), $filter['value']);
                    break;
                case self::FILTER_GREATER_EQUAL:
                    $qb->andWhere($this->getFieldString($alias, $filter['field']) . ' >= :' . $this->getParameterName($filter['field'], $rnd));
                    $qb->setParameter($this->getParameterName($filter['field'], $rnd), $filter['value']);
                    break;
                case self::FILTER_LESS:
                    $qb->andWhere($this->getFieldString($alias, $filter['field']) . ' < :' . $this->getParameterName($filter['field'], $rnd));
                    $qb->setParameter($this->getParameterName($filter['field'], $rnd), $filter['value']);
                    break;
                case self::FILTER_LESS_EQUAL:
                    $qb->andWhere($this->getFieldString($alias, $filter['field']) . ' <= :' . $this->getParameterName($filter['field'], $rnd));
                    $qb->setParameter($this->getParameterName($filter['field'], $rnd), $filter['value']);
                    break;
                case self::FILTER_DIFFERENT:
                    $qb->andWhere($this->getFieldString($alias, $filter['field']) . ' != :' . $this->getParameterName($filter['field'], $rnd));
                    $qb->setParameter($this->getParameterName($filter['field'], $rnd), $filter['value']);
                    break;
                default:
                    $fields = explode('|', $filter['field']);
                    $sql = '';
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' LIKE :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    foreach ($fields as $field) {
                        $qb->setParameter($this->getParameterName($field, $rnd), '%' . $filter['value'] . '%');
                    }
            }
        }

        return $qb;
    }

    /**
     * Method that set the order to the query
     *
     * @param QueryBuilder $qb
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return QueryBuilder
     */
    protected function setOrderBy(QueryBuilder $qb, array $orderBy = null, int $limit = null, int $offset = null)
    {
        if (is_array($orderBy) && count($orderBy)) {
            foreach ($orderBy as $field => $dir) {
                if (!empty($field)) {
                    if (strpos($field, '.') !== false) {
                        // has its own alias
                        $qb->orderBy($field, $dir);
                    } else {
                        $qb->orderBy($this->alias . '.' . $field, $dir);
                    }
                }
            }
        }

        if (!is_null($limit) && !is_null($offset)) {
            $qb->setFirstResult($offset);
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Get a Collection of Objects in the repository that match the filters
     *
     * @param array $filters
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getAll(array $filters, array $orderBy = null, int $limit = null, int $offset = null)
    {
        $qb = $this->createQueryBuilder($this->alias);

        $this->setFilters($qb, $filters);
        $this->setOrderBy($qb, $orderBy, $limit, $offset);

        return $qb->getQuery()->execute();
    }

    /**
     * Get the num of Objects in the repository that match the filters
     *
     * @param array $filters
     *
     * @return int
     */
    public function getAllCount(array $filters)
    {
        $qb = $this->createQueryBuilder($this->alias)->select('count('.$this->alias.'.id)');

        $this->setFilters($qb, $filters);

        return $qb->getQuery()->getSingleScalarResult();
    }
    
    /**
     * Get the Alias of the repository
     * 
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Clean the string of the parameterName
     * 
     * @param string $field
     * @param string $ext
     * @return mixed
     */
    private function getParameterName(string $field, string $ext)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $field).'_filter_'.$ext;
    }

    /**
     * Concat the alias to the field in case is needed
     *
     * @param string $alias
     * @param string $field
     * @return string
     */
    private function getFieldString(string $alias, string $field)
    {
        if ($alias === '') {
            return $field;
        }

        return $alias . '.' . $field;
    }    
}
