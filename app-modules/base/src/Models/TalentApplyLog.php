<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class TalentApplyLog extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_talent_apply_log';
    protected $fillable = ['user_id', 'name', 'description', 'certificate_image', 'check_status', 'reject_reason'];

    public function user()
    {
        return $this->belongsTo('Modules\Base\Models\User', 'user_id');
    }

    public function getImageHtml()
    {
        $html = '';
        if ($this->certificate_image) {
            $images = json_decode($this->certificate_image, true);
            foreach ($images as $value) {
                $html .= sprintf('<img src="%s" height="100px">', cdn().$value);
            }
        }
        return $html;
    }
}
