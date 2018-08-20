<?php

namespace Modules\Mall\Models;

use Modules\Base\Models;
use Backpack\CRUD\CrudTrait;

class Logistics extends \BaseModel
{
    use CrudTrait;

    protected $table = 'mall_logistics';
}
