<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 10/02/2019
 * Time: 13.57
 */

namespace App\Http\Middleware;
class CorsMiddleware {
	public function handle($request, \Closure $next)
	{
		$response = $next($request);

		$response->headers->set('Access-Control-Allow-Origin' , '*');
		$response->headers->set('Access-Control-Allow-Credentials', 'true');
		$response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
		$response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application, '
			.$request->header('Access-Control-Request-Headers'));

		return $response;
	}
}