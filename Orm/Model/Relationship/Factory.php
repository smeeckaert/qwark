<?php

namespace Qwark\Orm\Model\Relationship;

class Factory
{
    /**
     * @param $params
     * @return IFace
     */
    public static function build($params, $model)
    {
        $target = OneToOne::class;
        if (!empty($params['many'])) {
            $target = OneToMany::class;
        }
        if (!empty($params['assoc'])) {
            $target = ManyToMany::class;
        }
        return new $target($params, $model);
    }
}