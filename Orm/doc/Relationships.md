# Relationships

Relationships are defined as other properties but as an array.

```
<?php
class Post extends \Qwark\Orm\Model
{
    public $id;
    public $title;
    public $content;
    public $date;
    /** @var Category */
    public $category = [
        'to' => Category::class,
    ];
}

class Category extends \Qwark\Orm\Model
{
    public $id;
    public $name;
    public $order;
    /** @var Post */
    public $posts = [
        'from' => Post::class,
        'many' => true,
    ];
}

```

## Configuration

By default any relationship is OneToOne (1...1).

A lot of options are optional to describe a relation but you can always override them.

### from/to

A relation come `from` a model `to` an other. Most of the time you can omit one of the two completely since it will be equal
to the current model by default.

The `from` model is called the _source_ model. It is the model with the foreign key in an (1...1/n) relationship.
In a (n...n) relationship it refers to the current model.

The `to` model is called the _destination_ model. It is the model without foreign key in an (1...1/n) relationship.
In a (n...n) relationship it refers to the model that will be returned by the relationship.

### from

Either the table name of the source table or the class name of the source model.

If a Model is given, the properties `fromKey` and `fromModel` will be automatically calculated.

By default `from` is equals to the model's class name.

#### fromKey

The unprefixed key used for the relationship. By default 'id'.

#### fromModel

The class name of the model.

### to

Either the table name of the destination table or the class name of the destination model.

If a Model is given, the properties `toKey` and `toModel` will be automatically calculated.

By default `to` is equals to the model's class name.

#### toKey

The unprefixed key used for the relationship. By default 'id'.

#### toModel

The class name of the model.

### many

If set to true the relationship will be a OneToMany (1...n) relationship. By default it is false.

### assoc

The name of the association table.

The assoc option will change the relationship into a ManyToMany (n...n) relationship.

If set, the following properties will be automatically calculated.

#### assocFrom

The name of the key in the association table refering to the _source_ model. By default the prefixed `fromKey`.

#### assocTo

The name of the key in the association table refering to the _destination_ model. By default the prefixed `toKey`.

#### assocPrefix

The prefix of the association table. By default the default prefix of the association table name.

## Conditional queries

By default relationships will match foreign ids and that's it. If you need a more complex query you can set a callback
in the `query` property of the relationship. The method `customRelations` is available for override in the Model class
for this usage.

```php
<?php
class Post extends \Qwark\Orm\Model
{
    public $id;
    public $title;
    public $content;
    public $date;
    /** @var Category */
    public $custom;

    public function customRelations()
    {
        $this->custom = [
            'to'    => Category::class,
            'assoc' => true,
            'query' => function (\NilPortugues\Sql\QueryBuilder\Manipulation\Select $query, $item) {
                /** @var static $item */
                $query->where()->equals("post.post_id", $item->id)
                    ->equals('name', 'Category Name');
            },
        ];
    }
}
```