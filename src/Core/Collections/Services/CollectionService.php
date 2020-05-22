<?php

namespace GetCandy\Api\Core\Collections\Services;

use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Scaffold\BaseService;

class CollectionService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Collection();
    }

    /**
     * Returns model by a given hashed id.
     * @param  string $id
     * @throws  Illuminate\Database\Eloquent\ModelNotFoundException
     * @return Collection
     */
    public function getByHashedId($id, $withDrafted = false)
    {
        $id = $this->model->decodeId($id);
        $collection = $this->model;

        if ($withDrafted) {
            $collection = $collection->withDrafted();
        }

        return $collection->findOrFail($id);
    }


    public function findById($id, array $includes = [], $draft = false)
    {
        $query = Collection::with(array_merge($includes, ['draft']));

        if ($draft) {
            $query->withDrafted();
        }

        return $query->find($id);
    }
    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return Collection
     */
    public function create(array $data)
    {
        $collection = $this->model;
        $collection->attribute_data = $data;
        $collection->save();

        $urls = $this->getUniqueUrl($data['url']);

        $collection->routes()->createMany($urls);

        // event(new AttributableSavedEvent($collection));

        return $collection;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return bool
     */
    public function delete($id)
    {

        $collection = $this->getByHashedId($id, true);

        $collection->customerGroups()->detach();
        $collection->channels()->detach();
        $collection->products()->detach();

        return $collection->delete();
    }

    /**
     * Gets paginated data for the record.
     * @param  int $length How many results per page
     * @param  int  $page   The page to start
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($searchTerm = null, $length = 50, $page = null)
    {
        if ($searchTerm) {
            $ids = app(SearchContract::class)->against(get_class($this->model))->with($searchTerm);
            $results = $this->model->whereIn('id', $ids);
        } else {
            $results = $this->model;
        }

        return $results->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Sync products to a collection.
     * @param  string $collectionId
     * @param  array  $products
     * @return Collection
     */
    public function syncProducts($collectionId, $products = [])
    {
        $collection = $this->getByHashedId($collectionId);
        $productIds = app('api')->products()->getDecodedIds($products);
        $collection->products()->withTimestamps()->sync($productIds);

        return $collection;
    }
}
