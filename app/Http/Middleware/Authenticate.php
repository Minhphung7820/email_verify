<?php

namespace App\Http\Middleware;

use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            throw new HttpException(401, "Không có quyền truy cập !", null, [], 0);
        }
    }
}
