<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class Talent extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_talent';
    protected $fillable = ['user_id', 'name'];

    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id');
    }
}
