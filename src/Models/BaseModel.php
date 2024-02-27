<?php

namespace Mile6\LaravelEBMS\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;

class BaseModel extends Model
{
    use ForwardsCalls;

    protected $primaryKey = 'AUTOID';
    protected $keyType = 'string';

    protected static $unguarded = true;

    protected $guarded = [
        'AUTOID'
    ];

    protected $fieldMapping = [
        // Odata attribute => Laravel attribute
    ];

    /**
     * Fields that can only be read but never updated
     */
    protected $readOnly = [];

    /**
     * Get a new query builder for the model's table.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return (new Builder())->setModel($this);
    }

    public function newModelQuery()
    {
        return $this->newQuery();
    }

    public function newQueryForRestoration($ids)
    {
        $query = $this->newModelQuery();

        $query->where(function ($query) use ($ids) {
            $ids = Arr::wrap($ids);

            foreach ($ids as $key => $id) {
                if ($key === 0) {
                    $query->where('AUTOID', $id);
                } else {
                    $query->orWhere('AUTOID', $id);
                }
            }
        });

        return $query;
    }

    public function getQueueableId()
    {
        return $this->getKey();
    }

    public function __construct(array $attributes = [])
    {
        $this->readOnly = array_merge($this->readOnly, [
            'AUTOID', 'IsDeleted', 'IsNewEntity', 'CreateWithGuide', 'Subtitle', 'Title'
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? strtoupper(class_basename($this));
    }

    public function getAttribute($key)
    {
        if (in_array($key, array_keys($this->fieldMapping))) {
            return parent::getAttribute(Arr::get($this->fieldMapping, $key));
        }

        if (!Arr::has($this->attributes, $key) && Arr::has($this->attributes, strtoupper($key))) {
            return parent::getAttribute(strtoupper($key));
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, array_keys($this->fieldMapping))) {
            return parent::setAttribute(Arr::get($this->fieldMapping, $key), $value);
        }

        if (!Arr::has($this->attributes, $key) && Arr::has($this->attributes, strtoupper($key))) {
            return parent::setAttribute(strtoupper($key), $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttributesForInsert()
    {
        return array_filter($this->getAttributes(), function ($value, $key) {
            return !in_array($key, $this->readOnly);
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function performInsert(EloquentBuilder $query)
    {
        $query->createRequest($this->getAttributesForInsert());

        return true;
    }

    public function performUpdate(EloquentBuilder $query)
    {
        $query->updateRequest($this->getKey(), $this->getAttributesForInsert());

        return true;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->readOnly) ||
            (in_array($key, array_keys($this->fieldMapping)) && in_array(Arr::get($this->fieldMapping, $key), $this->readOnly)) ||
            (!Arr::has($this->attributes, $key) && in_array(strtoupper($key), $this->readOnly))) {
            throw new \Exception("{$key} is a read only field");
        }

        $this->setAttribute($key, $value);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Prepare the object for serialization.
     *
     * @return array
     */
    public function __serialize()
    {
        return [
            'attributes' => $this->attributes
        ];
    }

    /**
     * When a model is being unserialized, check if it needs to be booted.
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->fill(Arr::get($data, 'attributes', []));
    }

    public function load($relations)
    {
        return $this;
    }
}
