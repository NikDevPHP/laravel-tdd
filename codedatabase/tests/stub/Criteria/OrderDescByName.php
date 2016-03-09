<?php

namespace CodePress\CodeDatabase\Criteria;

use CodePress\CodeDatabase\Contracts\CriteriaInterface;
use CodePress\CodeDatabase\Contracts\RepositoryInterface;

class OrderDescByName implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repositoryInterface)
    {
        return $model->orderBy('name', 'desc');
    }
}