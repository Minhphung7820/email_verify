<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailActiveAccount;
use App\Jobs\SendMailVerifiOTP;
use App\Models\OTP;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                if (isset($request->username) && $request->username) {
                    $username = $request->username;
                    $user = User::where(function ($query) use ($username) {
                        $query->where('username', $username);
                        $query->orWhere('email', $username);
                    })->first();
                    if ($user) {
                        $password = $request->password;
                        if (Hash::check($password, $user->password)) {
                            $OTP = substr(rand(), 0, 6);
                            OTP::updateOrCreate([
                                "user_id" => $user->id
                            ], [
                                "user_id" => $user->id,
                                "otp_code" => $OTP,
                                "expired" => now()->addMinutes(1),
                                "created_at" => now()
                            ]);
                            SendMailVerifiOTP::dispatch($user, $OTP);
                            return $user;
                        } else {
                            return "Mật khẩu không đúng !";
                        }
                    } else {
                        return "Người dùng không tồn tại !";
                    }
                }
            });
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function verifyOTP(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::where('id', $request->user_id)->first();
                if (!$user) {
                    return "Tài khoản không tồn tại !";
                }
                $check = OTP::where('user_id', $request->user_id)->first();
                if ($check) {
                    if ($check->otp_code == $request->otp_code) {
                        if (now() > $check->expired) {
                            return "Mã OTP đã hết hạn !";
                        } else {
                            if ($user->is_active == 1) {
                                $token = $user->createToken("Token Authentication Laravel");
                                return array_merge($user->toArray(), ['token' => $token->accessToken]);
                            } else {
                                return "Tài khoản chưa được kích hoạt !";
                            }
                        }
                    } else {
                        return "Mã OTP Không chính xác !";
                    }
                }
            });
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function register(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $checkUsername = User::where('username', $request->username)->first();
                $checkEmail = User::where('email', $request->email)->first();
                if ($checkUsername) {
                    return "Tên đăng nhập đã tồn tại !";
                }

                if ($checkEmail) {
                    return "Email đã tồn tại !";
                }
                $tokenActive = generateToken(64);
                $data = $request->all();
                $data['password'] = bcrypt($data['password']);
                $data['activation_token'] = $tokenActive;
                $user = User::create($data);
                $url = url("/api/auth/active-account?token=" . $tokenActive);
                SendMailActiveAccount::dispatch($user, $url);
                return $user;
            });
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function activeAccount(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $token = $request->token;
                if (!$token) {
                    return "Kích hoạt thất bại !";
                }
                $user = User::where('activation_token', $token)->first();
                if (!$user) {
                    return "Kích hoạt thất bại !";
                }
                $user->update(['is_active' => 1, 'activation_token' => null]);
                return "Kích hoạt thành công !";
            });
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
