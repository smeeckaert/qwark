<?php

namespace Qwark\Orm\Model\Relationship;

interface IFace
{
    public function load($dbName);

    public function save($dbName);
}