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

$post = Post::findOne(1);

$catePost = $post->category;

d($catePost);

$post->title = "Rel title";
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