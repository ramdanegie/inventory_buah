<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 10/02/2019
 * Time: 14.05
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth2;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha512;


class Authenticate2
{

	public function __construct(Auth2 $auth)
	{
		$this->auth = $auth;
	}

	public function handle($request, Closure $next, $guard = null)
	{
		/*
		 * https://github.com/lcobucci/jwt/blob/3.2/README.md
		 */
		if ($this->auth->guard($guard)->guest()){
			if ($request->header('X-token'))
			{
				$signatur = 'RUdJRVJBTURBTg==';
				$token = (new Parser())->parse((string)$request->header('X-token'));
				$signer = new Sha256();
				$data = new ValidationData();
				if ($token->validate($data) && $token->verify($signer, $signatur))
				{
					$setheaders = $token->getClaim('data',$token);
					foreach ($setheaders as $obj => $value) {
						if($obj != 'X-token'){
							$request->headers->set($obj, $value);
						}
					}
					$userData = $this->unGenerateToken($token);
					$request->merge(compact('userData', $userData));

					return $next($request);
				} else {
					$results = array(
						'code' => 401,
						'message' => trans('auth.token_not_valid')
					);
					return response()->json($results,401);
				}
			}else{
				$data = array(
					'code' => 401,
					'message' => trans('auth.token_not_provided')
				);
				return response()->json($data,401);
			}
		}
	}
	protected function  unGenerateToken($token){
		/*
		 * https://github.com/lcobucci/jwt/blob/3.2/README.md
		 */
		$user = $token->getClaims(); // will print "1"
		if($user){
			$userData = array(
				"namaUser" => $user['namaUser'],
				'kdPegawai'  => $user['kdPegawai'],
				'kdUser' => $user['kdUser']
			);
		}

//		$this->setUserData($userData);
//		\Session::put('userData',$this->getUserData());
//		\Session::save();
		return $userData;
	}
}
