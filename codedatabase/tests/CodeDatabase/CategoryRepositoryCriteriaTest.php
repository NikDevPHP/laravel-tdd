<?php

namespace CodePress\CodeDatabase\Tests;

use CodePress\CodeDatabase\Contracts\CriteriaCollectionInterface;
use CodePress\CodeDatabase\Contracts\CriteriaInterface;
use CodePress\CodeDatabase\Criteria\FindByDescription;
use CodePress\CodeDatabase\Criteria\FindByName;
use CodePress\CodeDatabase\Criteria\FindByNameAndDescription;
use CodePress\CodeDatabase\Criteria\OrderDescById;
use CodePress\CodeDatabase\Criteria\OrderDescByName;
use CodePress\CodeDatabase\Models\Category;
use CodePress\CodeDatabase\Repository\CategoryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery as m;

class CategoryRepositoryCriteriaTest extends AbstractTestCase
{
    /**
     * @var CategoryRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->migrate();
        $this->repository = new CategoryRepository();
        $this->createCategory();
    }

    public function test_if_instanceof_criteriacollectioninterface()
    {
        $this->assertInstanceOf(CriteriaCollectionInterface::class, $this->repository);
    }

    public function test_can_get_criteria_collection()
    {
        $result = $this->repository->getCriteriaCollection();
        $this->assertCount(0, $result);
    }

    public function test_can_add_criteria()
    {
        $mockCriteria = m::mock(CriteriaInterface::class);
        $result = $this->repository->addCriteria($mockCriteria);
        $this->assertInstanceOf(CategoryRepository::class, $result);
        $this->assertCount(1, $this->repository->getCriteriaCollection());
    }

    public function test_can_get_by_criteria()
    {
        $criteria = new FindByNameAndDescription('Category 1', 'Description 1');
        $repository = $this->repository->getByCriteria($criteria);
        $this->assertInstanceOf(CategoryRepository::class, $repository);

        $result = $repository->all();
        $this->assertCount(1, $result);

        $result = $result->first();
        $this->assertEquals($result->name, 'Category 1');
        $this->assertEquals($result->description, 'Description 1');
    }

    public function test_apply_criteria()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByDescription('Description');
        $criteria2 = new OrderDescByName();

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);
        $repository = $this->repository->applyCriteria();
        $this->assertInstanceOf(CategoryRepository::class, $repository);

        $result = $repository->all();
        $this->assertCount(3, $result);
        $this->assertEquals($result[0]->name, 'Category Two');
        $this->assertEquals($result[1]->name, 'Category Two');
        $this->assertEquals($result[2]->name, 'Category One');
    }

    public function test_can_list_all_categories_with_criteria()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByDescription('Description');
        $criteria2 = new OrderDescByName();

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);

        $result = $this->repository->all();
        $this->assertCount(3, $result);
        $this->assertEquals($result[0]->name, 'Category Two');
        $this->assertEquals($result[1]->name, 'Category Two');
        $this->assertEquals($result[2]->name, 'Category One');
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function test_can_find_category_with_criteria_and_exception()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByDescription('Description');
        $criteria2 = new FindByName('Category Two');

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);

        $result = $this->repository->find(5);
        $this->assertEquals($result->name, 'Category Two');
    }

    public function test_can_find_category_with_criteria()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByDescription('Description');
        $criteria2 = new FindByName('Category One');

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);

        $result = $this->repository->find(5);
        $this->assertEquals($result->name, 'Category One');
    }

    public function test_can_find_by_categories_with_criteria()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByName('Category Two');
        $criteria2 = new OrderDescById();

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);

        $result = $this->repository->findBy('description', 'Description');
        $this->assertCount(2, $result);
        $this->assertEquals($result[0]->id, 6);
        $this->assertEquals($result[0]->name, 'Category Two');
        $this->assertEquals($result[1]->id, 4);
        $this->assertEquals($result[1]->name, 'Category Two');
    }

    public function test_can_ignore_criteria()
    {
        $reflectionClass = new \ReflectionClass($this->repository);
        $reflectionProperty = $reflectionClass->getProperty('isIgnoreCriteria');
        $reflectionProperty->setAccessible(true);
        $result = $reflectionProperty->getValue($this->repository);
        $this->assertFalse($result);

        $this->repository->ignoreCriteria();
        $result = $reflectionProperty->getValue($this->repository);
        $this->assertTrue($result);

        $this->repository->ignoreCriteria(true);
        $result = $reflectionProperty->getValue($this->repository);
        $this->assertTrue($result);

        $this->repository->ignoreCriteria(false);
        $result = $reflectionProperty->getValue($this->repository);
        $this->assertFalse($result);

        $this->assertInstanceOf(CategoryRepository::class, $this->repository->ignoreCriteria(true));
    }

    public function test_can_ignore_criteria_with_apply_criteria()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByDescription('Description');
        $criteria2 = new OrderDescByName();

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);
        $this->repository->ignoreCriteria();
        $this->repository->applyCriteria();

        $reflectionClass = new \ReflectionClass($this->repository);
        $reflectionProperty = $reflectionClass->getProperty('model');
        $reflectionProperty->setAccessible(true);
        $result = $reflectionProperty->getValue($this->repository);
        $this->assertInstanceOf(Category::class, $result);

        $this->repository->ignoreCriteria(false);
        $repository = $this->repository->applyCriteria();
        $this->assertInstanceOf(CategoryRepository::class, $repository);

        $result = $repository->all();
        $this->assertCount(3, $result);
        $this->assertEquals($result[0]->name, 'Category Two');
        $this->assertEquals($result[1]->name, 'Category Two');
        $this->assertEquals($result[2]->name, 'Category One');
    }

    public function test_can_clear_criterias()
    {
        $this->createCategoryDescription();

        $criteria1 = new FindByName('Category Two');
        $criteria2 = new OrderDescById();

        $this->repository->addCriteria($criteria1)->addCriteria($criteria2);
        $this->assertInstanceOf(CategoryRepository::class, $this->repository->clearCriteria());

        $result = $this->repository->findBy('description', 'Description');
        $this->assertCount(3, $result);

        $reflectionClass = new \ReflectionClass($this->repository);
        $reflectionProperty = $reflectionClass->getProperty('model');
        $reflectionProperty->setAccessible(true);
        $result = $reflectionProperty->getValue($this->repository);
        $this->assertInstanceOf(Category::class, $result);
    }

    private function createCategoryDescription()
    {
        Category::create([
            'name' => 'Category Two',
            'description' => 'Description'
        ]);

        Category::create([
            'name' => 'Category One',
            'description' => 'Description'
        ]);

        Category::create([
            'name' => 'Category Two',
            'description' => 'Description'
        ]);
    }

    private function createCategory()
    {
        Category::create([
            'name' => 'Category 1',
            'description' => 'Description 1'
        ]);

        Category::create([
            'name' => 'Category 2',
            'description' => 'Description 2'
        ]);

        Category::create([
            'name' => 'Category 3',
            'description' => 'Description 3'
        ]);
    }
}