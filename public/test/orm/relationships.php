<?php
require_once 'bootstrap.php';

class Post extends \Qwark\Orm\Model
{
    public $id;
    public $title;
    public $content;
    public $date;
}

class Category extends \Qwark\Orm\Model
{
    public $id;
    public $name;
    public $order;
}

$post = Post::find(1);
$post->title = "Rel title";

$cate = Category::find(1);

$cate->order = 3;
d("cate");
$cate->save();
d("after cate");
$post->save();