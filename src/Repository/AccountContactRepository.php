<?php

declare(strict_types=1);

namespace PswGroup\Api\Repository;

use BinSoul\Net\Hal\Client\HalResource;
use PswGroup\Api\Model\AbstractResource;
use PswGroup\Api\Model\Collection;
use PswGroup\Api\Model\PaginatedCollection;
use PswGroup\Api\Model\Resource\AccountContact;
use Throwable;

class AccountContactRepository extends AbstractRepository
{
    /**
     * Loads a contact resource.
     */
    public function load(string $number): ?AccountContact
    {
        try {
            $resource = $this->client->get($this->buildItemUrl($number));
        } catch (Throwable $e) {
            return null;
        }

        return $this->entityFromResource($resource);
    }

    /**
     * Saves contact data and returns the resource.
     *
     * @param AccountContact $data the data to be saved
     *
     * @return AccountContact the created contact resource
     */
    public function save(AccountContact $data): AccountContact
    {
        if ($data->getNumber() !== null) {
            $resource = $this->client->put($this->buildItemUrl($data->getNumber()), $data);
        } else {
            $resource = $this->client->post($this->getBaseUrl(), $data);
        }

        return $this->entityFromResource($resource);
    }

    /**
     * Deletes a contact resource.
     */
    public function delete(AccountContact $resource): void
    {
        if ($resource->getNumber() === null) {
            return;
        }

        try {
            $this->client->delete($this->buildItemUrl($resource->getNumber()));
        } catch (Throwable $e) {
        }
    }

    /**
     * Loads all contacts.
     *
     * @return Collection|AccountContact[]
     */
    public function loadAll(): Collection
    {
        try {
            $resource = $this->client->get($this->getBaseUrl(), ['pagination' => 'false']);
            $items = [];

            foreach ($resource->getResource('item') as $item) {
                $items[] = $this->entityFromResource($item);
            }

            return new Collection($items);
        } catch (Throwable $e) {
            return new Collection([]);
        }
    }

    /**
     * Loads a paginated list of contacts.
     *
     * @param mixed[]  $filters
     * @param string[] $orders
     *
     * @return PaginatedCollection|AccountContact[]
     */
    public function loadCollection(int $page, array $filters = [], array $orders = [], ?int $itemsPerPage = null): PaginatedCollection
    {
        $query = $this->prepareQuery($page, $filters, $orders, $itemsPerPage);

        try {
            $resource = $this->client->get($this->getBaseUrl(), $query);

            return $this->buildPaginatedCollection($resource, $page);
        } catch (Throwable $e) {
            return new PaginatedCollection([], 0, $page, 0);
        }
    }

    protected function getBaseUrl(): string
    {
        return '/contacts';
    }

    /**
     * @return AccountContact
     */
    protected function entityFromResource(HalResource $resource): AbstractResource
    {
        return AccountContact::fromResource($resource);
    }
}
