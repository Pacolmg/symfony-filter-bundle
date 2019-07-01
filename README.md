# Symfony Filter Bundle

This bundle provides your Symfony 4 app some functions to filter and make lists in an easy way.

License [MIT](https://raw.githubusercontent.com/Pacolmg/symfony-filter-bundle/master/LICENSE)

## Installation

### Add the repository to your composer.json
```
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:Pacolmg/symfony-filter-bundle.git"
    }
]
```

### Add the package to composer via console
```bash
$ composer  require  slx/filter-bundle:"*@dev"
```

### Extend the Repository of the entity that need to be filtered

For instance, in a entity called `Article`, in the repository, that should be in `src\Repository\ArticleRepository.php`:

```
<?php
namespace  App\Repository;

use  Pacolmg\SymfonyFilterBundle\Repository\BaseRepository;
...

class  ArticleRepository  extends  BaseRepository
{
...
```

Now, the installation is finished and the `Article` repository will have new methods in order to find objects filtered by different fields and in different ways (like, greater than, ...)

**Multiple repositories can be extended.**

## Start Filtering the Repository

### Method getAll
To filter the objects method `getAll` from the repository extended should be called:
```
$this->entityManager->getRepository('App:Article')->getAll($filters, $orderBy, $limit, $offset);
```
#### Parameters
- *offset*: The first result (for pagination).
- *limit*: The maximum number of results (for pagination).
- *orderby*: It's an array with the format:
  - `[field => 'direction']`.
  - Example: `['title' => 'ASC']`.
  - So, the results can be sorted by multiple fields.

##### The parameter $filter
It's the only mandatory parameter, and is composed by an array of different filters with the format:
```
[
    'type' => Constant that defines the behaviour,
    'field' => Field of the Entity (Or fields, separated by comma) where search the value,
    'value' => Value to compare
]
```
**The *field* is allow to have multiple values separated by a comma to search the same value in more than one field.


#### Type of filters
- `BaseRepository::FILTER_EXACT`: Compares that the field is equal to the value.
- `BaseRepository::FILTER_LIKE`: Compares that the field is like the value.
- `BaseRepository::FILTER_IN`: Search in array of values, the field.
- `BaseRepository::FILTER_GREATER`: Compares that the field is greater than the value.
- `BaseRepository::FILTER_GREATER_EQUAL`: Compares that the field is greater or equal than the value.
- `BaseRepository::FILTER_LESS`: Compares that the field is less than the value.
- `BaseRepository::FILTER_LESS_EQUAL`: Compares that the field is less or equal than the value.
- `BaseRepository::FILTER_DIFFERENT`: Compares that the field is different from the value.

### Method getAllCount
If the number of the results is needed, the method `getAllCount` will return that number, just the pass the filters to the method.
```
$this->entityManager->getRepository('App:Article')->getAllCount($filters);
```

### Examples
So, after this explanation of the filters that can be used, if we need the articles where has the word `tree` in its title, we should code:
```
$this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ]
]);
```

Easy? So, now, we are gonna find the articles with a `tree` in the title and a `cat` in the title or in the body:

```
$this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title,body',
        'value': 'cat'
    ]
]);
```

Too many results? If it's up to you, we will sort the results by `publishDate` and filter them, because we are only interested in the current year:
```
$this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title,body',
        'value': 'cat'
    ],
    [
        'type': BaseRepository::FILTER_GREATER_EQUAL,
        'field': 'publishDate',
        'value': date('Y-01-01 00:00')
    ],
    [
        'type': BaseRepository::FILTER_LESS_EQUAL,
        'field': 'publishDate',
        'value': date('Y-31-12 23:59')
    ]
], ['publishDate' => 'DESC']);
```

Are there still many results? We should paginate it, we want to see the second page, showing 10 results per page:

```
$this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title,body',
        'value': 'cat'
    ],
    [
        'type': BaseRepository::FILTER_GREATER_EQUAL,
        'field': 'publishDate',
        'value': date('Y-01-01 00:00')
    ],
    [
        'type': BaseRepository::FILTER_LESS_EQUAL,
        'field': 'publishDate',
        'value': date('Y-31-12 23:59')
    ]
], ['publishDate' => 'DESC'], 2, 10);
```
If your are showing the results on a website, it's probably that you need the total number or elements in order to show it or to make a proper pagination, easy:
```
$this->entityManager->getRepository('App:Article')->getAllCount([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title,body',
        'value': 'cat'
    ],
    [
        'type': BaseRepository::FILTER_GREATER_EQUAL,
        'field': 'publishDate',
        'value': date('Y-01-01 00:00')
    ],
    [
        'type': BaseRepository::FILTER_LESS_EQUAL,
        'field': 'publishDate',
        'value': date('Y-31-12 23:59')
    ]
]);
```

## Filter using the Service
The bundle provide you a service:
`Pacolmg\SymfonyFilterBundle\Service\FilterService;`

Within this service the methods of the repository `getAll` and `getAllCount` are joined, so the method `getFiltered` from the Service will return the Collection of Objects from the repository and the total number of elements to help the controller and view make the pagination.

**The sort parameter has a different format** in order to pass the field and the direction, avoiding code an array.

```
$this->filterService->getFiltered(
	$repository,
	$filters,
	$page,
	$limit,
	$sortBy,
	$sortDir	
);
```

### Examples
The last of the examples, coding from a Service or a Controller could be:
```
$this->filterService->getFiltered(
	$this->entityManager->getRepository('App:Article'),
	[
	    [
	        'type': BaseRepository::FILTER_LIKE,
	        'field': 'title',
	        'value': 'tree'
	    ],
	    [
	        'type': BaseRepository::FILTER_LIKE,
	        'field': 'title,body',
	        'value': 'cat'
	    ],
	    [
	        'type': BaseRepository::FILTER_GREATER_EQUAL,
	        'field': 'publishDate',
	        'value': date('Y-01-01 00:00')
	    ],
	    [
	        'type': BaseRepository::FILTER_LESS_EQUAL,
	        'field': 'publishDate',
	        'value': date('Y-31-12 23:59')
	    ]
	],
	2,
	10,
	'publishDate',
	'DESC'	
);
```

This example will return an array with two keys:
```
[
	'data' => colection of objects,
	'total' => Total number of elements filtered by $filters
]
```

## Filter from the Controller

To make a complete integration with a Website, the bundle provide another Service `Pacolmg\SymfonyFilterBundle\Service\ExternalParametersService` where there are some methods to get parameters from a `Request`.

### Get Page and Limit
The method `getPageAndLimit` returns in an array the page and the number of elements per page. It gets the information from the parameters `page` and `limit`.

#### Examples
In the Controller we can code this and the variable `$page` and `$limit` will have the page and the number of elements per page, the minimum page is 1 and the maximum limit is 500.

`list($page, $limit) = $this->externalParametersService->getPageAndLimit($request);`

### Get Filters
The method `getFilters` needs the `$request` and the `$filters` and will return the value for them, in order to make it work, we need to add a pair of fields to each filter:
```
[
    'type' => Constant that defines the behaviour,
    'field' => Field of the Entity (Or fields separated by comma) where find the value,
    'request_type' => Type of the value
    'request_name' => Name of the parameter
]
```

#### Request Types
The types of the *request_type* can be:
- int
- string *(default)*
- bool
- array


### Examples
We have have a website with a search input that send the controller a parameter `t` with the value of the input, and we want to use this to look for a title like the parameter `t`:
```http://mywebsite.com?t=tree```

In the Controller we should code:
```
$filters = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'request_type': 'string',
        'request_name': 't'
    ]
]);

list($data, $totalData) = $this->filterService->getFiltered($filters);
```

In the next example we have parameters for the pagination:

`http://mywebsite.com?t=tree&page=2&limit=10`

So, this will be the code in the Controller:
```
list($page, $limit) = $this->externalParametersService->getPageAndLimit($request);

$filters = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'request_type': 'string',
        'request_name': 't'
    ]
]);

list($data, $totalData) = $this->filterService->getFiltered($filters, $page, $limit);
```
