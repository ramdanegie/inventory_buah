<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 10/02/2019
 * Time: 14.06
 */

namespace App\Http\Middleware;
class OptionMiddleware {
	public function handle($request, \Closure $next)
	{

		$request_ = app('request');
		//  return response($request->getMethod('OPTIONS'));
		if ($request_->isMethod('OPTIONS'))
		{
//			app()->options($request_->path(), function() {
				//   echo('<script>console.log("woo");</script>');
				return response('', 200);
//			});
		}
		return $next($request);
	}
}