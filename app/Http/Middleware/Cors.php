<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Facade\FlareClient\Http\Response;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $value = $next($request);
        if(method_exists($next($request), 'header' ))
        {
            $value = $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization');

        }
       
           
        
        return $value;
     
        
        
    }
}
