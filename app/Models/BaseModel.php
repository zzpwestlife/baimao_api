<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    //  有效
    const STATUS_AVAILABLE = 1;
    // 无效
    const STATUS_INVALID = 0;

    const AD_CACHE_STORE = 'ad';

    protected $fillable = ['*'];
    protected $displayable = ['*'];

}
