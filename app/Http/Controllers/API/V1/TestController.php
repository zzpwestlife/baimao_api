<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Shop\AccountMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function getIndex()
    {
        dd('test');
        // 将虚拟账号且没有绑定手机号的账号标记为user_type=1
        $rows = AccountMember::where('mobile', '<', 1)
            ->where('email', 'like', '%@virtualAccount.com')
            ->update(['user_type' => 1]);

        dd($rows);
    }
}
