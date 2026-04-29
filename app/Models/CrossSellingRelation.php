<?php

namespace App\Models;

use App\Models\Traits\CascadeDeletes;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Jorenvh\Share\ShareFacade;

class CrossSellingRelation extends Model
{
    protected $guarded = [];
    protected $table = 'cross_selling_relations';

    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    public function target()
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }
    
}