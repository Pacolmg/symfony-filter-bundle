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
$ composer  require  pacolmg/symfony-filter-bundle:"*@dev"
```

### Extend the Repository of the entity that need to be filtered

For instance, in a entity called `Article`, with a repository that should be in `src\Repository\ArticleRepository.php`:

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
    'field' => Field of the Entity (Or fields, separated by pipe ("|")) where search the value,
    'value' => Value to compare
]
```
**The *field* is allow to have multiple values separated by a pipe "|" to search the same value in more than one field.


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


#### Examples
So, after this explanation of the filters that can be used, if we need the articles where has the word `tree` in its title, we should code:
```
$data = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ]
]);
```

Easy? So, now, we are gonna find the articles with a `tree` in the title and a `cat` in the title or in the body:

```
$data = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title|body',
        'value': 'cat'
    ]
]);
```

Too many results? If it's up to you, we will sort the results by `publishDate` and filter them, because we are only interested in the current year:
```
$data = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title|body',
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
$data = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title|body',
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
$totalResults = $this->entityManager->getRepository('App:Article')->getAllCount([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'value': 'tree'
    ],
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title|body',
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

### Method getDistinctField
Maybe the different values of a field are needed for filter a select, call the function with the name of the field as parameter.
```
$this->entityManager->getRepository('App:Article')->getAllCount($field);
```

#### Examples
We need all the different authors of the entity to put them on a select:
```
$authors = $this->entityManager->getRepository('App:Article')->getAllCount('author');
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
	        'field': 'title|body',
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
    'field' => Field of the Entity (Or fields separated by pipe ("|")) where find the value,
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


## Filter from the View

The bundle wants to help you with the form in the view too, so you can include some fields that are predefined, or extend your complete form  "@PacolmgSymfonyFilter/filters/layout.html.twig":

###Inputs
The predefined fields are:
- **Text Input**: @PacolmgSymfonyFilter/filters/input.html.twig
- **Numer Input**: @PacolmgSymfonyFilter/filters/number.html.twig
- **Datetime-local Input** (*Only works in chrome*): @PacolmgSymfonyFilter/filters/date.html.twig
- **Select**: @PacolmgSymfonyFilter/filters/select.html.twig

##### Parameters
Each input has some common parameters:
- **name** (*string, mandatory*): The attribute name for the input.
- **id** (*string, not mandatory*): The id of the input, if it's not defined the id will be: *symfony_filter_form_****+name***.
- **class** (*string, not mandatory*): The class of the input. If it's not defined will be *form-control*.
- **placeholder** (*string, not mandatory*): The placeholder for the input.
- **print_label** (*boolean, not mandatory*): Whether or not print the label of the field.
- **label** (*string, not mandatory*): The label of the field, if it's not defined and *print_label* is true, the label will be the placeholder. Label could be written in HTML.
- **defaultData** (*string|array|int, not mandatory*): The default value for the field.

The *select* field type will have another two parameters, one of them *mandatory*:
- **options** (*array, mandatory*): Array of options:
   - The format of the array options is: [ value => text, value => text, ...]
- **multiple** (*boolean, not mandatory*): Whether the select is multiple. False by default.

### Examples

We still want to find the articles which title has a certain word that we will collect from an input in the view. And we also want them filtered between two dates and by status too, so an example of form could be:

```
{% extends "@PacolmgSymfonyFilter/layout.html.twig" %}
{% block pacolmg_symfony_filter_bundle_form_filters %}
    <div class="col-sm-2">
        {{ include('@PacolmgSymfonyFilter/filters/text.html.twig', {placeholder: 'Title', name: 't'}, with_context = false) }}
    </div>
    <div class="col-sm-2">
        {{ include('@PacolmgSymfonyFilter/filters/date.html.twig', {placeholder: 'From', name: 'from'}, with_context = false) }}
    </div>
    <div class="col-sm-2">
        {{ include('@PacolmgSymfonyFilter/filters/date.html.twig', {placeholder: 'To', name: 'to'}, with_context = false) }}
    </div>
    <div class="col-sm-2">
        {{ include('@PacolmgSymfonyFilter/filters/select.html.twig', {placeholder: 'status', name: 's', options: {'1':'Created', '2':'Published', '3':'Deleted'} }, with_context = false) }}
    </div>
{% endblock %}
```

This form will send the parameters just to catch them coding this:

```
$filters = $this->entityManager->getRepository('App:Article')->getAll([
    [
        'type': BaseRepository::FILTER_LIKE,
        'field': 'title',
        'request_type': 'string',
        'request_name': 't'
    ],
    [
        'type': BaseRepository::FILTER_GREATER_EQUAL,
        'field': 'publishedAt',
        'request_type': 'date',
        'request_name': 'from'
    ],
    [
        'type': BaseRepository::FILTER_LESS_EQUAL,
        'field': 'publishedAt',
        'request_type': 'date',
        'request_name': 'to'
    ],
    [
        'type': BaseRepository::FILTER_EXACT,
         'field': 'status',
         'request_type': 'int',
         'request_name': 's'
    ]
]);

list($data, $totalData) = $this->filterService->getFiltered($filters);
```

Imagine you change your mind and prefer to get the articles that can be published or created, so we need to convert the status select to multiple:

 ```
 ...
 <div class="col-sm-2">
         {{ include('@PacolmgSymfonyFilter/filters/select.html.twig', {placeholder: 'status', name: 's', options: {'1':'Created', '2':'Published', '3':'Deleted'} }, with_context = false) }}
 </div>
 ...
 ```
 
 And on the controller, change the type of the filter:
 ```
 ...
     [
         'type': BaseRepository::FILTER_IN,
          'field': 'status',
          'request_type': 'int',
          'request_name': 's'
     ]
 ...
 ```

## Pagination

So, at the Controller, Service and Repository is possible to get the results paginated. The bundle has also a twig to make pagination easy for development. Just include the *'@PacolmgSymfonyFilter/components/pagination.html.twig'* view and see the pages.

### Parameters
The mandatory parameters are:
- **nbPages** (*int, mandatory*): The total number of pages.
- **currentPage** (*int, mandatory*): The current page.
- **url** (*string, mandatory*): The symfony route to redirect the pagination.
- **params** (*array, mandatory*): The parameters that the the route will need to work (id, q, ...).

The twig has some optional parameters too:
- **nearbyPagesLimit** (*int, not mandatory*): Number of pages around the current page.
    - The default value is 4.
- **align** (*string, not mandatory*): The value has to be: "end", "center" or "start" (based on bootstrap flex).
    - The default value is "end".
- **classPageItem** (*string, not mandatory*): Class for the "li" tag.
    - The default value is "page-item".
- **classPageLink** (*string, not mandatory*): Class for the links.
    - The default value is "page-link".
- **classDisabled** (*string, not mandatory*): Class for the disabled pages.
    - The default value is "disabled".
- **classActive** (*string, not mandatory*): Class for current page.
    - The default value is "active".
    
### Examples

We have our article index page where we have the filters defined in the previous examples, now we want to show the number of the pages:

To avoid too much logic on the twig, we construct the parameters in the Controller:

```
list($page, $limit) = $this->externalParametersService->getPageAndLimit($request);

$filters = $this->entityManager->getRepository('App:Article')->getAll([
    ...
]);

list($data, $totalData) = $this->filterService->getFiltered($filters);

$paginationData = [
            'nbPages' => ceil($data['total'] / $limit),
            'currentPage' => $page,
            'url' => $request->get('_route'),
            'params' => $request->query->all()
        ];

```

In the view:

```
{{ include('@PacolmgSymfonyFilter/components/pagination.html.twig', paginationData, with_context = false) }}
```

Hope that filter and paginate your entities with this bundle will not be a pain anymore.