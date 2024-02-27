<?php

namespace Mile6\LaravelEBMS\Models;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mile6\LaravelEBMS\EBMS;

class Builder
{

    protected $operators = [
        '=' => 'eq',
        '!=' => 'ne',
        '<>' => 'ne',
        '>' => 'gt',
        '>=' => 'ge',
        '<' => 'lt',
        '<=' => 'le',
        'in' => 'in'
    ];

    protected $wheres, $count, $selects, $expands, $limit, $skip, $orders;
    protected $table, $model;

    public function __construct($data = [])
    {
        $this->wheres = new Collection();
        $wheres = Arr::get($data, '$filter', []);

        if (count($wheres)) {
            (new Collection([$wheres]))->each(function ($item) {
                $this->where(...$item);
            });
        }

        $this->selects = new Collection(Arr::get($data, '$select', []));
        $this->expands = new Collection(Arr::get($data, '$expand', []));
        $this->orders = new Collection(Arr::get($data, '$orderBy', []));
    }

    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function skip($skip)
    {
        $this->skip = $skip;

        return $this;
    }

    public function select($selects)
    {
        if (is_array($selects)) {
            $this->selects = new Collection(Arr::wrap($selects));
        } else {
            $this->selects = new Collection(explode(',', $selects));
        }

        return $this;
    }

    public function addSelect($select)
    {
        if (is_array($select)) {
            $this->selects->push(...$select);
        } else {
            $this->selects->push(...explode(',', $select));
        }

        return $this;
    }

    public function expand($expands)
    {
        $this->expands = new Collection(Arr::wrap($expands));

        return $this;
    }

    public function addExpand($field, $value = '')
    {
        if (is_array($field) && Arr::isAssoc($field)) {
            (new Collection($field))->each(function ($value, $key) {
                $this->expands->put($key, $value);
            });
        } else {
            $this->expands->put($field, $value);
        }

        return $this;
    }

    public function where($key, $comparison = '=', $value = '', $logical = 'and')
    {
        if (func_num_args() === 2 && !in_array($comparison, array_keys($this->operators))) {
            $value = $comparison;
            $comparison = '=';
        }

        if (!in_array($comparison, array_keys($this->operators))) {
            throw new Exception('Invalid Operator');
        }

        $this->wheres->push([
            'options' => [$key, $comparison, $value, $logical],
            'type' => 'basic'
        ]);

        return $this;
    }

    public function whereRaw($sql, $logical = 'and')
    {
        $this->wheres->push([
            'options' => [$sql, null, null, $logical],
            'type' => 'raw'
        ]);

        return $this;
    }

    public function orWhereRaw($sql)
    {
        $this->whereRaw($sql, 'or');

        return $this;
    }

    public function whereIn($key, $values, $logical = 'and')
    {
        $this->wheres->push([
            'options' => [$key, 'in', $values, $logical],
            'type' => 'basic'
        ]);

        return $this;
    }

    public function orWhere($key, $comparison = '=', $value = '')
    {
        if (func_num_args() === 2) {
            $value = $comparison;
            $comparison = '=';
        }

        $this->where($key, $comparison, $value, 'or');

        return $this;
    }

    public function orWhereIn($key, $values)
    {
        $this->whereIn($key, $values, 'or');

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orders->put($column, $direction);

        return $this;
    }

    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    public function get($columns = ['*'])
    {
        return $this->getModels($this->getRequest(), $columns);
    }

    public function getRequest()
    {
        $ebms = app(EBMS::class);

        $url = "{$this->table}/?{$this->getQueryUri()}";

        return $ebms->get($url);
    }

    public function createRequest($attributes)
    {
        $ebms = app(EBMS::class);

        $url = $this->table;

        return $ebms->post($url, $attributes);
    }

    public function updateRequest($key, $attributes)
    {
        $ebms = app(EBMS::class);

        $url = "{$this->table}('{$key}')";

        return $ebms->patch($url, $attributes);
    }

    public function all()
    {
        $ebms = app(EBMS::class);

        $url = "{$this->table}";

        return $this->getModels($ebms->get($url));
    }

    public function count()
    {
        $this->count = true;

        $this->limit(0);

        return Arr::get($this->getRequest(), '@odata.count');
    }

    public function getModels($data, $columns = ['*'])
    {
        $data = Arr::get($data, 'value', []);

        if (count($columns) > 1 || (count($columns) === 1 && Arr::first($columns) !== '*')) {
            $data = array_map(function ($item) use ($columns) {
                return Arr::only($item, $columns);
            }, $data);
        }

        return $this->model->newInstance([])->newCollection(array_map(function ($item) {
            return $this->model->newInstance($item);
        }, $data));
    }

    public function getQueryUri()
    {
        return http_build_query($this->getQueryUriParams());
    }

    public function getUnencodedQueryUri()
    {
        return (new Collection($this->getQueryUriParams()))->map(function ($values, $key) {
            return "{$key}={$values}";
        })->implode('&');
    }

    public function getQueryUriParams()
    {
        $params = new Collection();

        if ($this->selects->isNotEmpty()) {
            $params->put('$select', $this->selects->implode(','));
        }

        if ($this->expands->isNotEmpty()) {
            $params->put('$expand', $this->expands->map(function ($value, $key) {
                $subBuilder = new Builder();

                if (is_callable($value)) {
                    call_user_func($value, $subBuilder);
                } else if (is_array($value) && Arr::isAssoc($value)) {
                    $subBuilder = new Builder($value);
                } else if (is_array($value) && !Arr::isAssoc($value)) {
                    $subBuilder->select($value);
                } else if (is_string($value) && $value !== '') {
                    $subBuilder->select(explode(',', $value));
                }

                if ($value !== '') {
                    return "$key({$subBuilder->getUnencodedQueryUri()})";
                }

                return "$key";
            })->values()->implode(','));
        }

        if ($this->wheres->isNotEmpty()) {
            $params->put('$filter', $this->getWhereClause());
        }

        if ($this->orders->isNotEmpty()) {
            $params->put('$orderby', $this->orders->map(function ($value, $key) {
                return "$key $value";
            })->values()->implode(', '));
        }

        if ($this->limit !== null) {
            $params->put('$top', $this->limit);
        }

        if ($this->count) {
            $params->put('$count', $this->count ? 'true' : 'false');
        }

        if ($this->skip) {
            $params->put('$skip', $this->skip);
        }

        return $params->toArray();
    }

    public function getWhereClause()
    {
        return $this->wheres->reduce($this->constructFilter(), '');
    }

    protected function constructFilter()
    {
        return function ($carry, $where) {
            $clause = '';

//            if (is_array(Arr::first($where))) {
//                $clause .= '(' . (new Collection($where))->reduce($this->constructFilter(), '') . ')';
//            } else {
            [$key, $comparison, $value, $logical] = $where['options'];

            if ($carry !== '') {
                $clause .= " {$logical} ";
            }

            if ($where['type'] === 'raw') {
                $clause .= $key;
            } else if ($key instanceof Closure) {
                call_user_func($key, $subBuilder = new Builder());

                $clause .= "({$subBuilder->getWhereClause()})";
            } else {
                if ($comparison === 'in') {
                    if (is_array($key)) {
                        $key = '[' . implode(',', $key) . ']';
                    }

                    if (is_array($value) && Str::contains($key, '[')) {
                        $value = (new Collection($value))->map(function ($item) {
                            return "['" . implode("','", $item) . "']";
                        })->implode(',');

                        $value = "[{$value}]";
                    } else {
                        $value = "('" . implode("', '", Arr::wrap($value)) . "')";
                    }
                } else {
                    if (is_bool($value)) {
                        $value = $value ? 'true' : 'false';
                    } else {
                        $value = "'{$value}'";
                    }
                }

                $operator = Arr::get($this->operators, $comparison);

                $clause .= "{$key} {$operator} {$value}";
            }
//            }

            return $carry .= $clause;
        };
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param BaseModel $model
     * @return Builder
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->table = $model->getTable();

        return $this;
    }

    /**
     * Determine if the given model has a scope.
     *
     * @param string $scope
     * @return bool
     */
    public function hasNamedScope($scope)
    {
        return $this->model && $this->model->hasNamedScope($scope);
    }

    /**
     * Apply the given named scope on the current builder instance.
     *
     * @param string $scope
     * @param array $parameters
     * @return mixed
     */
    protected function callNamedScope($scope, array $parameters = [])
    {
        $this->model->callNamedScope($scope, Arr::prepend($parameters, $this));

        return $this;
    }

    public function __call($method, $parameters)
    {
        if ($this->hasNamedScope($method)) {
            return $this->callNamedScope($method, $parameters);
        }
    }

    public function useWritePdo()
    {
        return $this;
    }

    public function first($columns = ['*'])
    {
        return $this->limit(1)->get($columns)->first();
    }

    public function firstOrFail($columns = ['*'])
    {
        if (!is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }
}
