<?php

namespace Estey\Rubix\Models;

class Category extends Model
{
    /**
     * Fields.
     * @var array
     */
    protected static $fields = [
        'id',
        'title',
        'created_at',
        'updated_at'
    ];

    /**
     * Fields.
     * @var array
     */
    protected static $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Create a new instance of this model.
     *
     * @param array|null $data
     * @return Estey\Rubix\Models\Model
     */
    public static function getInstance($data = null)
    {
        return new self($data);
    }
}
