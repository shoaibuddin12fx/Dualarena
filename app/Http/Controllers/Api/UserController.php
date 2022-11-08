<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Validator;

class UserController extends Controller
{
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'            
        ]);

        if ($validator->fails()) {
            return self::failure($validator->errors()->first());
        }


        if ( Auth::attempt(['email' => $data['email'], 'password' => $data['password'] ] ) ) {
            $user = Auth::user();
            $token['token'] = $user->createToken('MyLaravelApp')->accessToken;

            return self::success('User login', ['data' => [
                'user' => $user,
                'token' => $token,
            ]]);

        } else {
            return self::failure('User login failed');
        } 

        

    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return self::failure($validator->errors()->first(), ['data' => []]);
        }

        $data = $request->all();
        $data['password'] = bcrypt($data['password']);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->save();

        $token = $user->createToken('MyLaravelApp')->accessToken;

        return self::success('User created', ['data' => [
            'user' => $user,
            'token' => $token,
        ]]);

    }

    public function logout(Request $request)
    {

        $data = $request->user()->tokens()->delete();
        return self::success('User logout', ['data' => []]);

    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function userDetails()
    {
        $user = Auth::user();
        return self::success('User Detaild', ['data' => [
            'user' => $user,

        ]]);

    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status),
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'message' => 'Password reset successfully',
            ]);
        }

        return response([
            'message' => __($status),
        ], 500);

    }
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Already Verified',
            ];
        }

        $request->user()->sendEmailVerificationNotification();

        return ['status' => 'verification-link-sent'];
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified',
            ];
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return [
            'message' => 'Email has been verified',
        ];
    }

    public function requestOtp(Request $request)
    {

        /* $data = $user = Auth::user();

        $otp = rand(100000, 999999);
        Log::info("otp = " . $otp);
        $user = User::where('email', '=', $request->email)->update(['otp' => $otp]);

        if ($user) {

        $mail_details = [
        'subject' => 'Testing Application OTP',
        'body' => 'Your OTP is : ' . $otp,
        ];

        Mail::to($request->email)->send(new mail($mail_details));

        return self::success('otp send successfully', ['data' => [
        'user' => $user,
        'Otp' => $otp,
        'mail' => $mail_details,
        ]]);
        } else {
        return self::failure("otp unsuccessful");

        }
         */
        // if a user as for otp
        // first check if user is authentiated via routes
        // which means this function should come after middleware of auth

        // if user authenticated already
        // then find the user information ... i.e
        // if user is already verified, then return false

        // if user is pending verifiction then generate an OTP of 6 digits
        // save that OTP value in user table but check if that number is already exist in user table or not

        // means if 123445 is already applied to some user, then generate a new code and save it
        // save the code to respective user

        // then send code to email

        // then return a success response to user say please check your email

    }

    public function sendOtp(Request $request)
    {

        $user = Auth::user();

        // check if user is already verified or not
        $isUserVerified = $user->email_verified_at != null;

        if ($isUserVerified) {
            return self::failure('email failed', ['data' => []]);

        }

        $otp = rand(100000, 999999);
        Log::info("otp = " . $otp);
        $user = User::where('email', '=', $request->email)->update(['otp' => $otp]);

        if ($user) {

            $mail_details = [
                'subject' => 'Testing Application OTP',
                'body' => 'Your OTP is : ' . $otp,
            ];
            

            return self::success('otp send successfully', ['data' => [
                'user' => $user,
                'Otp' => $otp,
                'mail' => $mail_details,
            ]]);
        } else {
            return self::failure("otp unsuccessful");

        }


    }

    public function verifyOtp(Request $request)
    {

        $data = $user = Auth::user();
        $user = User::where(['email' => $request->email, 'otp' => $request->otp])->first();
        if ($user) {
            auth()->login($user, true);

            User::where('email', '=', $request->email)->update(['otp' => null]);
            $token = $user->createToken('MyLaravelApp')->accessToken;
            return self::success('Otp verified', ['data' => [
                'user' => $user,
                'token' => $token,
            ]]);
        } else {
            return self::failure('Otp failed', ['data' => []]);
        }

    }

}

//         $otp = rand(1000, 9999);
//         Log::info("otp = " . $otp);
//         $user = User::where('email', '=', $request->email)->update(['otp' => $otp]);

//         if ($user) {
// // send otp in the email
//             $mail_details = [
//                 'subject' => 'Testing Application OTP',
//                 'body' => 'Your OTP is : ' . $otp,
//             ];

//             \Mail::to($request->email)->send(new sendemail($mail_details));

//             return response(["status" => 200, "message" => "OTP sent successfully"]);
//         } else {
//             return response(["status" => 401, 'message' => 'Invalid']);
//         }

// }

// public function verifyOtp(Request $request)
//  {

//      $user = User::where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
//     if ($user) {
//          auth()->login($user, true);
//          User::where('email', '=', $request->email)->update(['otp' => null]);
//          $accessToken = auth()->user()->createToken('authToken')->accessToken;

//         return response(["status" => 200, "message" => "Success", 'user' => auth()->user(), 'access_token' => $accessToken]);
//     } else {
//         return response(["status" => 401, 'message' => 'Invalid']);
//     }
