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
use Illuminate\Support\Facades\Validator;

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

    // product
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
