<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return ResponseFormatter::error($validateUser->errors(), 'validation error', 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return ResponseFormatter::error(null, 'Email atau Kata Sandi anda salah!', 401);
            }

            $user = User::where('email', $request->email)->first();
            return ResponseFormatter::success([
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email',
                    'password' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return ResponseFormatter::error($validateUser->errors(), 'validation error', 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return ResponseFormatter::success([
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return ResponseFormatter::success(['user' => $user]);
        } else {
            return ResponseFormatter::error();
        }
    }

    function updateUser(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'avatar' => 'file|between:0,2048|mimes:jpeg,jpg,png',
            ]);
            $user = $request->user();
            if ($request['avatar']) {
                if ($user->avatar != null) {
                    Storage::delete($user->avatar);
                }
                $filetype = $request->file('avatar')->extension();
                $text = Str::random(16) . '.' . $filetype;
                Storage::putFileAs('public', $request->file('avatar'), $text);
                $data['avatar'] = $text;
            }
            $user = $user->update($data);
            return ResponseFormatter::success();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // product
    public function home(Request $request)
    {
        $popular = Product::orderBy('view', 'DESC')->paginate($request->page);
        $news = Product::orderBy('created_at', 'DESC')->paginate($request->page);

        if ($popular && $news) {
            return ResponseFormatter::success([
                'popular' => $popular,
                'news' => $news,
            ]);
        } else {
            return ResponseFormatter::error();
        }
    }

    public function products(Request $request)
    {
        $products = [];
        if ($request->category) {
            $products = Product::where('category_id', $request->category)->orderBy('view', 'DESC')->paginate($request->page);
        } else {
            $products = Product::orderBy('view', 'DESC')->paginate($request->page);
        }
        if ($products) {
            return ResponseFormatter::success($products);
        } else {
            return ResponseFormatter::error();
        }
    }

    public function categories()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        if ($categories) {
            return ResponseFormatter::success($categories);
        } else {
            return ResponseFormatter::error();
        }
    }
}
