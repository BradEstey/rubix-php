<?php

namespace Estey\Rubix\Models;

class Pattern extends Model
{
    /**
     * Fields.
     * @var array
     */
    protected static $fields = [
        'id',
        'label',
        'file',
        'category_id'
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
