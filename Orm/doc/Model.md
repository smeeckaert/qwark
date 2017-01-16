# Model

To describe a model, create a class that extends Model and add public properties.

```php
<?php

class Post extends \Qwark\Orm\Model
{
    public $id;
    public $title;
    public $content;
    public $date;
}
```

Every property that doesn't start with the `_` character refer to a database field.

## Configuration

You don't have to configure more, but you can override the default behaviour if you want.

A different number of property can be defined to change the default behaviour of the model.

### static protected $_id

The unprefixed name of the primary key column. By default `id`.

### static protected $_table

The name of the table. By default a lowercase version of the class name

### static protected $_prefix

The prefix used on each field. By default the first 4 characters of the table name

## Usage

### Create an entry

When creating a new instance of your Model you can either use the object properties or
set them directly as an associative array in the constructor.

The key for the array of properties can either be the name of the properties or the name of the column in the schema.

```php
<?php

$post = new Post();
$post->title = "Title";
$post->content = "Content";
$post->date = date('Y-m-d H:i:s');

// OR
$post = new Post(['title' => "Title", 'content' => "Content", 'date' => date('Y-m-d H:i:s')]);

// OR
$post = new Post(['post_title' => "Title", 'post_content' => "Content", 'post_date' => date('Y-m-d H:i:s')]);

```

### Manipulation

```php
<?php
$post->save(); // Save in schema

$post->delete(); // Delete this item
```

### Find items

The `find` static method on the model will help you find items.

It accepts either an id, an array of properties or a query.

The `findOne` method works the same but returns directly the item instead of a collection of items.

```
<?php
$postFound = Post::findOne(42); // Find the post with the primary key 42

$postList = Post::find(['title' => 'Title']); // Find all the posts that have this title

```

To perform more complex queries, you can user `Model::builder()` that will return a query builder already configured.

```php
<?php
$builder = Post::builder();
$query = $newBuilder->select();
$query->where()->equals('title', 'Title 2');
$listPosts = Post::find($query); // Will execute the query and return the posts
```