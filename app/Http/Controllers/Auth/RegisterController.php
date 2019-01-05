<?php

namespace App\Http\Controllers\Auth;

use App\Models\Consumer;
use App\Models\User;
use App\Models\UserType;
use App\UserDefault;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Registration process method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $parameters = $request->all();

        if (!$parameters) {
            return new JsonResponse([
                'error' => 'ERROR! There are no passed parameters.',
                'code' => 417,
            ]);
        }

        // Get user types to check with existing types
        $userTypes = UserType::all();
        if (!$userTypes->contains($parameters['user_type'])) {
            return new JsonResponse([
                'error' => 'ERROR! There are no such type of users.',
                'code' => 417,
            ]);
        }

        $userType = $parameters['user_type'];
        $userNameExist = $this->isUsernameExist($userType, $parameters['username']);

        // All types of user has same fields in database
        if (1 == $userType && !$userNameExist) {
            $newUser = new User;
        } else if (2 == $userType && !$userNameExist) {
            $newUser = new Consumer;
        } else {
            return new JsonResponse([
                'error' => 'ERROR! Already has a user with that username or usertype is not found.',
                'code' => 417,
            ]);
        }

        $newUser->username = $parameters['username'];
        $newUser->password = hash('sha256', $parameters['password']);
        $newUser->user_type = $userType;
        $newUser->save();

        return new JsonResponse([
            'message' => 'Registration is passed successful.',
            'code' => 200,
        ]);
    }

    /**
     * Get user_type and username and checks for username existance
     *
     * @param int $userType
     * @return bool
     */
    public function isUsernameExist(int $userType, string $username): ?bool
    {
        switch ($userType) {
            case 1:
                $user = new User;
                break;
            case 2:
                $user = new Consumer;
                break;
        }

        return is_object($user->where('username', $username)->first());
    }
}
