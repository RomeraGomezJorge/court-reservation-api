<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserResource;
use Auth;

class ProfileController
{
    public function show()
    {
        return new UserResource(Auth::user());
    }
}
