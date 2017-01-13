<?php
require_once 'bootstrap.php';

class Post extends \Qwark\Orm\Model
{
    public $id;
    public $title;
    public $content;
    public $date;
}

/*
try {
    $model = new Post();
    $model->title = "Test title";
    $model->content = "Test content";
    $model->date = date('Y-m-d H:i:s');
    $model->save();
    echo "saved";
} catch (Exception $e) {
    d($e->getMessage());
}*/
$item = Post::findOne(2);
$item->title = "change3";
$item->save('replica');
$item = Post::findOne(3);
//$item->delete();

$builder = \Qwark\Orm\DB::instance()->builder();
$query = $builder->select()->setTable('post');
$query->where()->equals('post_title', 'change');

$listPosts = Post::find($query);
var_dump($listPosts);

d("use builder");
$newBuilder = Post::builder();
$query = $newBuilder->select();
$query->where()->equals('title', 'change2');
$listPosts = Post::find($query);
var_dump($listPosts);
d("inline");
$listPosts = Post::find(Post::builder()->select()->where()->equals('title', 'change2')->end());
d($listPosts);