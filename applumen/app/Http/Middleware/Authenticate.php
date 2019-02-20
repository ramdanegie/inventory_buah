<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
	    if ($this->auth->guard($guard)->guest()) {
		    if($request->header('authorization')){
			    $checkToken = json_encode(array_search($request->header('authorization'),array_column($this->getData_APIKey(),'token')));
			    if($checkToken == 'false' ){
				    $response['message']='Have not Login yet';
				    $response['response']=false;
				    $response['code']= 401;
				    return response()->json($response);
			    }

			    if(!$this->check_permission($request, $checkToken)){
				    $response['message']="You Don't have permission";
				    $response['status']=false;
				    $response['code']= 403;
				    return response()->json($response);
			    }
		    }else{
			    $response['message']='Have not Login yet';
			    $response['status']=false;
			    $response['code']= 401;
			    return response()->json($response);
		    }
	    }
	    return $next($request);
//        if ($this->auth->guard($guard)->guest()) {
//            return response('Unauthorized.', 401);
//        }
//
//        return $next($request);
    }
}
