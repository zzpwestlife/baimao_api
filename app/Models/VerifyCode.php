<?php

namespace App\Models;

class VerifyCode extends BaseModel
{
    protected $table = 'phone_verify_code';

    protected $fillable = ['mobile', 'type', 'code', 'expire_time'];

}
