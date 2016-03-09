<?php

namespace CodePress\CodeDatabase\Criteria;

use CodePress\CodeDatabase\Contracts\CriteriaInterface;
use CodePress\CodeDatabase\Contracts\RepositoryInterface;

class OrderDescById implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repositoryInterface)
    {
        return $model->orderBy('id', 'desc');
    }
}