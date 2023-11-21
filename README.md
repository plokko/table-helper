# Table helper
A Laravel heper ( based on [plokko/resource-query](https://github.com/plokko/resource-query) ) for creating dynamic (orderable and fiterable) AJAX tables.

## Installation
Install throught composer `composer require plokko/querable-resource`

...WIP
TODO: JS
## Usage
Initialyze the TableHelper by passing the database table name to use
```php
$table = TableHelper::table('users');
```
or the base query to use (filters and condition will be added to your query, field selection will be overridden)
```php
$table = TableHelper::table(User::where('id','>',0));
```
or a ResourceQuery object (the base query will be used as it is, filters and order will be added or replaced )
```php
$table = TableHelper::table(new \plokko\ResourceQuery\ResourceQueryBuilder());
```

You can then start defining table and column proprieties using the fluent setters.
The fluent setters will allow to customize your table and columns in a readable way and will allow to access base table methods even while you're defining a column.
```php
// TableHelper definition via fluent setters
$table
    ->column('id')// "id" column scope
    ->column('name')// "name" column scope
        ->label('User name')
        ->sort(true)
        ->filter(true)

    ->column('name')
        ->label('User name')
        ->sort(true)
        ->filter(true)

    ->column('email')
        ->label('E-mail')
        ->align('right')
        ->sort(true)
        ->filter(true)
        ->type('email')
        //->class('primary white--text')
        //->cellClass('primary lighten-5')

    ->column('created_age')
        ->label('Creation age')
        ->sort(true)
        ->field(\DB::raw('TIMESTAMPDIFF(HOUR,created_at,CURRENT_TIMESTAMP )'))

    ->column('actions')// No associated table field
        ->virtual()
        ->columnView('<v-btn @click="">Test {{item.id}}</v-btn>')

    ->setPageSize(30) /// Sets the number of elements per page
    
    ->autoselect(true)/// Automatically applies it to the table even if in field scope
    ;


```
In your controller returning the TableBuilder instance will automatically execute the query and return the data;
to enable AJAX functionality in the same page return it if the request is AJAX like below
```php
if($request->ajax()){
    return $table;
}
```
Pass the instance on your view
```php
//Use it in view
return view('your-view.example',compact('table'));
```
And render the table using the *render()* method
```php
<!-- ...your view code... -->

    <!-- Renders the table in your view -->
    {{ $table->render() }}

<!-- ... -->
```

## Table options

* **formAction(string $action)** - Set the form action, defaults to ''
* **column(string $name)** - Declares or retrieve a column by name
* **removeColumn(string $name)** - Removes a column by name
* **setDefaultSortBy(array $attr)** - Array of default sorting order (Ex. ['field1',['field2','desc'],]
* **setBaseLangFile(string $attr)** - Set the translation file for label, the file (or field) should contain an array with field name as key and labels as values
* **selectFields(array|null $fields)** - Explicitly set selected fields (ex. ['id',DB::raw('count(id)'),...])
* **autoSelect(boolean $enabled)** - Enables or disable auto select field generation

### Methods

* **render()** - Returns the table (render in view)
* **renderAttr()** - Returns the table attributes (render in view)
* **renderBody()** - Returns the table body (render in view)
* **getHeaders()** - Returns the headers as an array

## Column options

Column fields are set by calling a function with the value:
* **label(string|null $value)** - Text to use in the headers, if none is specified it will be used the label from global translations or field name
* **field(string|null|\Illuminate\Database\Query\Expression $value)** - Set the table field used for select (and filtering or sorting if not explicit), defaults to table name if not set.
* **type(string|null type)** - Set field type (ex. boolean, email, etc.) for formatting
* **align(string|null type)** - Set header alignment (left|center|right)
* **component(string|null $component)** - Set field component (in render)
* **rowClass(string|null $class)** - Set row (CSS) class
* **cellClass(string|null $class)** - Set cell (CSS) class
* **visible(bool $visible)** - Sets the column visibility
* **virtual(bool $virtual)** - Sets the column as virtual (no corresponding table field, ex. table actions)
* **columnView(null|string|\Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure $view)** - Sets the column view
* **virtual(bool $virtual)** - Sets the column as virtua (no corresponding table field, ex. table actions)
* **attr(string $key,mixed $value)** - Set one field attribute as key-value
* **setAttrs(array $attributes)** - Set all attributes as a key-value array
* **sort(boolean|null|string|\Illuminate\Database\Query\Expression $field,[boolean $reverse])** - Makes the column sortable, first argument is table field (null or false to disable sorting, true to use base field or string or Expression to specify sorted field). If $reverse is set to true the sorting will be reversed (asc. when desc and vice versa)
* **filter(string|boolean|Closure $condition='=',null|string|\Illuminate\Database\Query\Expression $field=null)** - Makes the column filterable, $condition specifies the [condition](https://github.com/plokko/resource-query/wiki/Filtering#defining-filters) to use and $field specifies the field to use (defaults to base field if null)

### Methods
* **remove()** - Alias of *removeColumn('\<column-name>')*, removes the column
