<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\CartModel;
use App\Models\ProductModel;
use App\Models\AddressModel;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('ifAuthRedirect')->only(['login', 'register']);
        $this->middleware('checkLogin')->only(['mypage', 'verifyPassword']);
    }

    // 로그인 페이지
    function login()
    {
        return view('login');
    }

    // 회원가입 페이지
    function register()
    {
        return view('register');
    }

    // 회원 가입
    public function registerPost(Request $req)
    {
        Log::debug($req);
        // 유효성 검사
        $rules = [
            'name'               => 'required|regex:/^[가-힣]+$/u|min:2|max:30',
            'email'              => 'required|email|min:5|max:30',
            'password'           => 'required_with:password_confirm|same:password_confirm|regex:/^(?=.*[a-zA-Z])(?=.*[!@#$%^*-])(?=.*[0-9]).{8,20}$/u',
            'password_confirm'   => 'required_with:password|same:password|regex:/^(?=.*[a-zA-Z])(?=.*[!@#$%^*-])(?=.*[0-9]).{8,20}$/u',
        ];

        // seller가 존재할 경우에만 seller 유효성 검사 추가
        if (isset($req->seller)) {
            $rules['seller'] = 'in:on'; // seller가 존재할 경우 유효성 검사
        }

        // 유효성 검사 실행
        $req->validate($rules);
    
        $existingUser = User::where('email', $req->email)->whereNull('social')->first();
        if ($existingUser) {
            return redirect()->back()->with('alert', '이미 사용중인 이메일 입니다.')->withInput();
        }

        try {
            DB::beginTransaction();

            $data['name']   = $req->name;
            $data['email']  = $req->email;
            $data['pw']     = Hash::make($req->password);
            if($req->seller) {
                $data['admin_flg'] = 1;
            }

            $user = User::create($data);

            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            Log::error($e);
            return redirect()->back()->with('alert', '시스템 에러가 발생하여 회원가입에 실패했습니다.\n잠시 후에 다시 시도해주세요.');
        }

        Session::put('alert', '회원 가입이 완료되었습니다.');

        // 로그인
        auth()->login($user);

        return redirect()->route('main'); // 홈 페이지로 리다이렉트
    }

    // 로그인
    public function loginPost(Request $req) {
        // 유효성 검사
        $req->validate([
            'email' => 'required|email|max:100',
            'pw'    => 'required|regex:/^(?=.*[a-zA-Z])(?=.*[!@#$%^*-])(?=.*[0-9]).{8,20}$/'
        ]);
    
        $user = User::where('email', $req->email)->first();
    
        if (!$user || !Hash::check($req->pw, $user->pw)) {
            $error = '아이디와 비밀번호를 확인하세요';
            return redirect()->back()->with('alert', $error);
        }
    
        Auth::login($user);
    
        if (Auth::check()) {        
            // 세션에 저장된 URL이 있으면 해당 URL로 리다이렉트, 없으면 기본 경로로 리다이렉트
            return redirect()->intended(route('main')); // 기본 경로는 메인페이지
        } else {
            $errors = '로그인 에러';
            return redirect()->back()->with('error', $errors);
        }
    }

    // 소셜 로그인 리디렉션
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // 소셜 로그인 콜백
    public function handleCallback($provider)
    {
        $user = Socialite::driver($provider)->user();
        return $this->handleUserLogin($user, $provider);
    }

    // 소셜 사용자 로그인
    private function handleUserLogin($user, $provider)
    {
        // 이메일로 사용자 검색
        $existingUser = User::where('email', $user->getEmail())->where('social', $provider)->first();

        if ($existingUser) {
            // 기존 사용자 로그인
            Auth::login($existingUser, true);
        } else {
            // 신규 사용자 생성
            $newUser = User::create([
                'name'      => $user->getName(),
                'email'     => $user->getEmail(),
                'pw'        => bcrypt(Str::random(16)), // 랜덤 비밀번호 생성
                'social'    => $provider
            ]);
            Auth::login($newUser, true);
        }

        // 홈으로 리다이렉트
        return redirect()->intended(route('main'));
    }

    //로그아웃
    public function logout() 
    {
        Session::flush(); //세션 파기
        Auth::logout();
        return redirect()->route('main');
    }
    
    // 마이페이지
    public function mypage() 
    {
        // $addresses = AddressModel::where('user_id', Auth::id())->get();
        // $addresses = auth()->user()->addresses;
        // 기본 배송지부터 상단에 출력되게 가져옴
        $addresses = 
            auth()
            ->user()
            ->addresses()
            ->orderBy('default', 'desc')
            ->get();

        return view('mypage')->with('addresses', $addresses);
    }
    
    // 마이페이지 접근시 비밀번호 확인 페이지
    // public function verifyPassword(Request $req)
    // {
    //     $req->validate(['pw' => 'required']);

    //     if (Hash::check($req->pw, Auth::user()->pw)) {
    //         // 세션에 비밀번호 확인 상태 저장
    //         session(['pwConfirmed' => true]);
    //         return redirect()->route('users.mypage'); // 마이페이지로 리다이렉트
    //     } else {
    //         return back()->withErrors(['pw' => '비밀번호가 일치하지 않습니다.']);
    //     }
    // }

    // 배송지 유효성검사
    protected function validateAddress(Request $req)
    {
        return $req->validate([
            'postcode'      => 'required|digits:5',
            'address'       => 'required|string|max:100',
            'detailAddress' => 'nullable|string|max:50',
            'extraAddress'  => 'nullable|string|max:50',
            'recipient'     => 'required|string|max:30',
            'phone'         => 'required|string|regex:/^[0-9]{2,3}-[0-9]{3,4}-[0-9]{4}$/',
            'default'       => 'nullable'
        ]);
    }

    // 배송지 추가
    public function addAddress(Request $req) 
    {
        $validatedData = $this->validateAddress($req);

        // 체크박스가 선택된 경우, 다른 주소의 default 값을 0으로 설정
        if ($req->has('default')) {
            // 모든 주소의 default를 0으로 설정
            auth()->user()->addresses()->update(['default' => '0']);

            // 새로운 배송지의 default 값은 1로 설정
            $validatedData['default'] = '1';
        } else {
            // 체크박스가 선택되지 않았다면 0으로 설정
            $validatedData['default'] = '0';
        }

        // 새 배송지 생성
        $address = auth()->user()->addresses()->create($validatedData);

        // 성공 메시지와 함께 새 주소를 반환
        return response()->json([
            'msg' => '배송지가 성공적으로 추가되었습니다.',
            'addresses' => auth()->user()->addresses()->orderBy('default', 'desc')->get() // 갱신된 주소 목록 반환
        ]);
    }

    // 배송지 수정
    public function editAddress(Request $req)
    {
        $this->validateAddress($req);
        try {
            // 주소 ID로 주소를 찾아 업데이트
            $address = AddressModel::find($req->addId);
            Log::debug($address);
            $address->recipient = $req->recipient;
            $address->address = $req->address;
            $address->detailAddress = $req->detailAddress;
            $address->extraAddress = $req->extraAddress;
            $address->phone = $req->phone;

            // 체크박스가 선택된 경우, 다른 주소의 default 값을 0으로 설정
            if ($req->has('default')) {
                // 모든 주소의 default를 0으로 설정
                auth()->user()->addresses()->update(['default' => '0']);

                // 새로운 배송지의 default 값은 1로 설정
                $address->default = '1';
            } else {
                // 체크박스가 선택되지 않았다면 0으로 설정
                $address->default = '0';
            }

            $address->save();
        } catch (\Exception $e) {
            \Log::error('주소 업데이트 실패: ' . $e->getMessage());
            return response()->json(['msg' => '주소 업데이트에 실패했습니다.'], 500);
        }

        // 성공 메시지와 함께 새 주소를 반환
        return response()->json([
            'msg' => '수정되었습니다.',
            'addresses' => auth()->user()->addresses()->orderBy('default', 'desc')->get() // 갱신된 주소 목록 반환
        ]);
    }

    // 배송지 삭제
    public function deleteAddress(Request $req)
    {
        // Log::debug($req);
        $req->validate([
            'addId' => 'required|integer|exists:address,add_id',
        ]);
    
        try {
            // 주소 삭제
            $address = AddressModel::find($req->addId);
            if ($address) {
                $address->delete(); // 주소 삭제
            }
    
            return response()->json([
                'msg' => '배송지가 삭제되었습니다.',
                'addresses' => auth()->user()->addresses()->orderBy('default', 'desc')->get(),
            ]);
        } catch (Exception $e) {
            // 예외 발생 시 로그 기록 및 오류 메시지 반환
            Log::error('배송지 삭제 중 오류 발생: ' . $e->getMessage());
    
            return response()->json(['msg' => '배송지 삭제 중 오류가 발생했습니다.'], 500);
        }
    }

}
