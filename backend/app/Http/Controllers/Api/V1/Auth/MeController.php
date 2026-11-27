<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Resources\Auth\AuthUserResource;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;




class MeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request) : AuthUserResource
    {
        $user = $request->user();
        $user->loadMissing('studentProfile');

        return new AuthUserResource($user);
    }
}
