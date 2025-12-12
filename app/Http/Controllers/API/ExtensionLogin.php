<?php


namespace App\Http\Controllers\API;


use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

/**
 * @deprecated
 */
class ExtensionLogin extends AccessTokenController
{
    use AuthenticatesUsers;

    public int $maxAttempts = 5;

    /**
     * Authenticate a google extension
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $this->maxAttempts = config('auth.max_login_attempts');
        if ($this->hasTooManyLoginAttempts($request))
        {
            $this->fireLockoutEvent($request);
            try {
                $user = User::where('email', $request->get('email'))
                    ->first();
                $user->block();
                $this->sendLockoutResponse($request);
            } catch (\Exception $exception) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }
        }
        $credentials = $request->only(['email', 'password']);
        if(Auth::attempt($credentials)) {
            $request->merge(['username' => $request->get('email')]);
            $this->clearLoginAttempts($request);
            $psr7Factory = new Psr17Factory();
            $psr = new PsrHttpFactory($psr7Factory, $psr7Factory, $psr7Factory, $psr7Factory);
            $psrRequest = $psr->createRequest($request);

            $tokenResponse = parent::issueToken($psrRequest);

            return response()->json(
                json_decode($tokenResponse->content(), true),
                $tokenResponse->getStatusCode()
            );

        } else {
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
        }
    }
}
