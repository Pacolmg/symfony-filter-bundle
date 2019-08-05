<?php

namespace Pacolmg\SymfonyFilterBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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

        // Avoid join twice the same entity
        $alreadyJoined = [];

        foreach ($filters as $filter) {
            //some fields has two filters, so the parameter can't be named like the field
            $filterFields[$filter['field']]++;
            $rnd = $filterFields[$filter['field']];

            // use the alias of the filter or the alias of class
            $alias = isset($filter['own_alias']) ? $filter['own_alias'] : $this->alias;

            // join
            if (isset($filter['join']) && isset($filter['own_alias']) && !in_array($this->alias . '.' . $filter['join'], $alreadyJoined)) {
                $qb->join($this->alias . '.' . $filter['join'], $filter['own_alias']);
                $alreadyJoined[] = $this->alias . '.' . $filter['join'];
            }

            // nested joins
            if (isset($filter['nested_joins']) && isset($filter['own_alias']) && is_array($filter['nested_joins'])) {
                $entityAlias = $this->alias;
                foreach ($filter['nested_joins'] as $entityName => $entityValue) {
                    if (!in_array($entityAlias . '.' . $entityName, $alreadyJoined)) {
                        $qb->join($entityAlias . '.' . $entityName, isset($entityValue['alias']) ? $entityValue['alias'] : $filter['own_alias']);
                        $alreadyJoined[] = $entityAlias . '.' . $entityName;
                        $entityAlias = isset($entityValue['alias']) ? $entityValue['alias'] : $filter['own_alias'];
                    }
                }
            }

            $fields = explode('|', $filter['field']);
            $sql = '';

            switch ($filter['type']) {
                case self::FILTER_EXACT:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' = :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                case self::FILTER_IN:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . 'IN (:' . $this->getParameterName($field, $rnd) . ')';
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                case self::FILTER_GREATER:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' > :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                case self::FILTER_GREATER_EQUAL:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' >= :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                case self::FILTER_LESS:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' < :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                case self::FILTER_LESS_EQUAL:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' <= :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                case self::FILTER_DIFFERENT:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' != :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, $filter['value'], $rnd);
                    break;
                default:
                    foreach ($fields as $field) {
                        $sql .= ($sql == '' ? '' : ' OR ') . $this->getFieldString($alias, $field) . ' = :' . $this->getParameterName($field, $rnd);
                    }
                    $qb->andWhere($sql);
                    $this->setParameters($qb, $fields, '%' . $filter['value'] . '%', $rnd);
            }
        }

        return $qb;
    }

    /**
     * Set parameters to $qb
     *
     * @param QueryBuilder $qb
     * @param array $fields
     * @param $value
     * @param $rnd
     * @return QueryBuilder
     */
    protected function setParameters(QueryBuilder $qb, array $fields, $value, $rnd)
    {
        foreach ($fields as $field) {
            $qb->setParameter($this->getParameterName($field, $rnd), $value);
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
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getAllCount(array $filters)
    {
        $qb = $this->createQueryBuilder($this->alias)->select('count(' . $this->alias . '.id)');

        $this->setFilters($qb, $filters);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get distinct values for a $field
     *
     * @param string $field
     * @return mixed
     */
    public function getDistinctField(string $field)
    {
        $result = $this->createQueryBuilder($this->alias)->select('DISTINCT(' . $this->alias . '.' . $field . ') as ' . $field)->getQuery()->execute();

        return array_map(function (array $distinctField) use ($field) {
            return $distinctField[$field];
        }, $result);
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
        return preg_replace('/[^A-Za-z0-9\-]/', '', $field) . '_filter_' . $ext;
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