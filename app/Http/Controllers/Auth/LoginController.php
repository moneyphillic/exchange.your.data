<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Consumer;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Login action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $parameters = $request->all();

        if (!$parameters) {
            return new JsonResponse([
                'error' => 'ERROR! There are no parameters passed.',
                'code' => 417,
            ]);
        }

        $userType = $parameters['user_type'];

        $this->hasCredentials($parameters);
        $this->hasUserType($parameters);
        $this->rightUserType($parameters);

        $user = $this->checkUserType($parameters, $userType);

        $password = $parameters['password'];
        $hashedPassword = hash('sha256', $password);

        if ($user->password === $hashedPassword) {
            $request->session()->put('user', $user);

            return new JsonResponse([
                'message' => 'Successful authorization!',
                'code' => 200,
            ]);
        }

        return new JsonResponse([
            'message' => 'Wrong password, try again!',
            'code' => 417,
        ]);
    }

    /**
     * Logout action
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Check if there is a user
        if ($request->session()->has('user')) {

            $request->session()->forget('user');

            return new JsonResponse([
                'error' => 'Come again',
                'cose' => 200,
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Check if user passed it's credentials when login
     *
     * @param array $credentials
     * @return JsonResponse
     */
    public function hasCredentials(array $credentials)
    {
        $check = Arr::has($credentials, ['username', 'password']);

        if (!$check) {
            return new JsonResponse([
                'error' => 'ERROR! Please pass username and password.',
                'code' => 417,
            ]);
        }
    }

    /**
     * Check if user type is passed
     *
     * @param array $parameters
     * @return JsonResponse
     */
    public function hasUserType(array $parameters)
    {
        $check = Arr::has($parameters, ['user_type']);

        if (!$check) {
            return new JsonResponse([
                'error' => 'ERROR! There is no user_type passed.',
                'code' => 417,
            ]);
        }
    }

    public function rightUserType(array $parameters)
    {
        // Get user types to check with existing types
        $userTypes = UserType::all();
        $check = $userTypes->contains($parameters['user_type']);
        if (!$check) {
            return new JsonResponse([
                'error' => 'ERROR! There are no such type of users.',
                'code' => 417,
            ]);
        }
    }

    /**
     * Check if user is from exist in existed user types
     *
     * @param array $parameters
     * @param string $userType
     * @return mixed
     */
    public function checkUserType(array $parameters, string $userType)
    {
        if (1 == $userType) {
            $user = User::where('username', $parameters['username'])->first();
        } else if (2 == $userType) {
            $user = Consumer::where('username', $parameters['username'])->first();
        }

        if (!$user) {
            return new JsonResponse([
                'error' => 'ERROR! There is no user with that username or usertype is not right.',
                'code' => 417,
            ]);
        }

        return $user;
    }
}
