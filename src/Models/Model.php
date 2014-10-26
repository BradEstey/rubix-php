<?php

namespace Estey\Rubix\Models;

use DateTime;
use Carbon\Carbon;

abstract class Model
{
    /**
     * Fields.
     * @var array
     */
    protected static $fields = [];

    /**
     * Fields that are dates and should be converted 
     * into Carbon objects.
     * @var array
     */
    protected static $dates = [];

    /**
     * Data store.
     * @var array
     */
    protected $data = [];

    /**
     * Create a new Model Instance.
     *
     * @param array|null $data
     * @return void
     */
    public function __construct($data = null)
    {
        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * Set data.
     * 
     * @param array|object $data
     * @return $this
     */
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Set a given attribute to the data array.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, static::$fields)) {
            if (in_array($key, static::$dates)) {
                $value = Carbon::instance(new DateTime($value));
                $value->timezone = 'UTC';
            }

            $this->data[$key] = $value;
        }
    }

    /**
     * Get an attribute from the data array.
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, static::$fields) and isset($this->data[$key])) {
            return $this->data[$key];
        }

        return false;
    }

    /**
     * Dynamically set attributes on a resource.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamically retrieve attributes on the resource.
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Convert this resource into it's JSON representation.
     * 
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Convert this resource into an array.
     * 
     * @return array
     */
    public function toArray()
    {
        $data = $this->data;
        foreach (static::$dates as &$field) {
            if (isset($data[$field])) {
                $data[$field] = $data[$field]->timestamp;
            }
        }
        return (array) $data;
    }

    /**
     * Convert this resource into it's string representation.
     * In this case, we'll just return JSON.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
