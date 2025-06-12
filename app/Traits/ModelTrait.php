<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use ReflectionException;

trait ModelTrait
{
    /**
     * 셀렉트 first
     * @param array $field
     * @param array $where
     * @return array
     */
    public function getFirst(array $field, array $where = []): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'row' => $this->select($this->setDbRaw($field))
                        ->where($where)
                        ->first()
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 셀렉트 Count
     * @param array $where
     * @return array
     */
    public function getCount(array $where = []): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'count' => $this
                        ->where($where)
                        ->count()
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 셀렉트 Sum
     * @param string $field
     * @param array $where
     * @return array
     */
    public function getSum(string $field, array $where = []): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'sum' => $this
                        ->where($where)
                        ->sum($this->setDbRaw($field))
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 셀렉트 Max
     * @param string $field
     * @param array $where
     * @return array
     */
    public function getMax(string $field, array $where = []): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'max' => $this
                        ->where($where)
                        ->max($field)
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 셀렉트 Min
     * @param string $field
     * @param array $where
     * @return array
     */
    public function getMin(string $field, array $where = []): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'min' => $this
                        ->where($where)
                        ->min($field)
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * exists
     * @param array $where
     * @param string $operator
     * @return array
     */
    public function getExists(array $where = [], string $operator = 'where'): array
    {
        try {
            $query = $this;
            switch($operator) {
                case 'whereIn':
                    [$key] = array_keys($where);
                    $query = $query->whereIn($key, $where[$key]);
                    break;
                default:
                    $query = $query->where($where);
                    break;
            }

            return [
                'success' => true,
                'data' => [
                    'exists' => $query->exists()
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 셀렉트 get
     * @param array $field
     * @param array $where
     * @param string $operator
     * @return array
     */
    public function getRows(array $field, array $where = [], string $operator = 'where'): array
    {
        try {
            $query = $this;
            switch($operator) {
                case 'whereIn':
                    [$key] = array_keys($where);
                    $query = $query->select($field)->whereIn($key, $where[$key]);
                    break;
                default:
                    $query = $query->select($field)->where($where);
                    break;
            }

            return [
                'success' => true,
                'data' => [
                    'rows' => $query->get()
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 업데이트
     * @param array $set
     * @param array $where
     * @param string $operator
     * @return array|true[]
     */
    public function setUpdate(array $set, array $where = [], string $operator = 'where'): array
    {
        try {
            $update = $this->setDbRaw($set);
            switch($operator) {
                case 'whereIn':
                    [$key] = array_keys($where);
                    $this->whereIn($key, $where[$key])->update($update);
                    break;
                default:
                    $this->where($where)->update($update);
                    break;
            }
            return ['success' => true];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param array $values
     * @return array|bool[]
     */
    public function setInsert(array $values): array
    {
        try {
            $values = $this->setDbRaw($values);
            $this->insert($values);
            return ['success' => true];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * update or insert
     * @param array $insert
     * @param array $update
     * @return array|bool[]
     */
    public function setUpdateOrInsert(array $insert, array $update): array
    {
        try {
            $update = $this->setDbRaw($update);
            $this->updateOrInsert($insert, $update);
            return ['success' => true];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 삭제
     * @param array $where
     * @param string $operator
     * @return array|bool[]
     */
    public function delRows(array $where = [], string $operator = 'where'): array
    {
        try {
            switch($operator) {
                case 'whereIn':
                    [$key] = array_keys($where);
                    $this->whereIn($key, $where[$key])->delete();
                    break;
                default:
                    $this->where($where)->delete();
                    break;
            }

            return ['success' => true];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * insertGetId
     * @param array $values
     * @return array | string
     */
    public function setInsertGetId(array $values): array|string
    {
        try {
            $values = $this->setDbRaw($values);
            return [
                'success' => true,
                'data' => [
                    'id' => $this->insertGetId($values)
                ]
            ];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @brief 공통 DB::raw 처리
     * @param $params
     * @return mixed
     */
    public function setDbRaw($params): mixed
    {
        // null 값처리
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                if (is_string($val) && strpos($val, 'DB::raw|') !== false) {
                    $params[$key] = DB::raw(str_replace('DB::raw|', '', $val));
                }
            }
        } else if (str_contains($params, 'DB::raw|')) {
            $params = DB::raw(str_replace('DB::raw|', '', $params));
        }

        return $params;
    }

    /**
     * @return void
     */
    public function beginTransaction(): void
    {
        try {
            DB::beginTransaction();
        } catch (QueryException $e) {

        }
    }

    /**
     * @return void
     */
    public function rollBack() :void
    {
        try {
            DB::rollBack();
        } catch (QueryException $e) {

        }
    }

    /**
     * @return void
     */
    public function commit() :void
    {
        try {
            DB::commit();
        } catch (QueryException $e) {
        }
    }

    /**
     * @return int
     */
    public function transactionLevel()
    {
        return DB::transactionLevel();
    }

    public function setTruncate()
    {
        return $this->truncate();
    }

    /**
     * @param array $params
     * @param object $query
     * @return object
     */
    public function defaultSort(array $params, object $query): object
    {
        if (isset($params['order']) && is_array($params['order'])) {
            foreach($params['order'] as $orderby) {
                $query = $query->orderBy($orderby[0], $orderby[1]);
            }
        } else if (isset($params['order']) && isset($params['by'])) {
            if (!empty($params['order']) && !empty($params['by'])) {
                $query = $query->orderBy($params['order'], $params['by']);
            }
        } else if (isset($params['order']) && $params['order'] === 'random') {
            $query = $query->inRandomOrder();
        }

        return $query;
    }

    /**
     * @param $params
     * @param $paginate
     * @param $query
     * @return array
     */
    public function setReturnFormat($params, $paginate, $query) :array
    {
        try {
            // 리턴 종류
            if (isset($params['return_first']) && $params['return_first']) {
                return [
                    'success' => true,
                    'data' => [
                        'row' => $query->get()->first()
                    ]
                ];
            }

            if ($paginate) {
                if (isset($params['page'])) {
                    $res = $query->Paginate($params['limit'], null, 'page', $params['page']);
                } else {
                    $res = $query->Paginate($params['limit'] ?? config('page.limit'));
                }

                // 데이터 정보만 담는다.
                $rows = $res->items();

                // 데이터 정보를 제거한다.
                $reflection = new \ReflectionObject($res);
                $items = $reflection->getProperty('items');
                $items->setAccessible(true);
                $items->setValue($res, collect([]));

                return [
                    'success' => true,
                    'data' => [
                        'rows' => $rows,
                        'paginate' => $res
                    ]
                ];
            }

            if (isset($params['offset'])) {
                $query = $query->offset($params['offset']);
            }

            if (isset($params['limit'])) {
                $query = $query->limit($params['limit']);
            }

            return [
                'success' => true,
                'data' => [
                    'rows' => $query->get()
                ]
            ];
        } catch (ReflectionException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

}
