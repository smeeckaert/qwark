<?php
require_once 'bootstrap.php';

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

    /** @var Image */
    public $images = [
        'to'    => Image::class,
        'assoc' => true,
    ];

    /** @var Image */
    public $customimages;

    public function customRelations()
    {
        $this->customimages = [
            'to'    => Image::class,
            'assoc' => true,
            'query' => function (\NilPortugues\Sql\QueryBuilder\Manipulation\Select $query, $item) {
                /** @var static $item */
                $query->where()->equals("post.post_id", $item->id)
                    ->equals('url', 'url1');
            },
        ];
    }
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

class Image extends \Qwark\Orm\Model
{
    public $id;
    public $url;
}

$post = Post::findOne(1);

$catePost = $post->category;

d($catePost);

d($post->images);
d("CUSTOM");
d($post->customimages);

$post->title = "Rel title3";
$post->save();
dd("stop");
$cate = Category::findOne(1);

$cateList = $cate->posts;
d($cateList);

// Chaining
d($post->category->posts->current()->category);
dd("stop");

$cate->order = 3;
d("cate");
$cate->save();
d("after cate");
$post->save();
d("after save");