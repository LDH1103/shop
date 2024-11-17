<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\CartModel;
use App\Models\OrderModel;
use Illuminate\Support\Str;
use App\Models\AddressModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{
    // -----------------------------------------------------------------------------------
    // 함수명   : __construct()
    // 설명     : UserController 생성자
    //            특정 경로에 대한 미들웨어 적용으로 접근 권한 관리
    //            ifAuthRedirect 미들웨어는 로그인/회원가입 시 인증된 사용자 접근을 차단
    //            checkLogin 미들웨어는 마이페이지 접근 시 로그인 상태를 필수화
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    public function __construct()
    {
        $this->middleware('ifAuthRedirect')->only(['login', 'register']);
        $this->middleware('checkLogin')->only(['mypage', 'verifyPassword']);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : login()
    // 설명     : 사용자 로그인 페이지를 반환
    //
    // param    : 없음
    //
    // return   : View - 로그인 페이지
    // -----------------------------------------------------------------------------------
    function login()
    {
        return view('login');
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : register()
    // 설명     : 사용자 회원가입 페이지를 반환
    //
    // param    : 없음
    //
    // return   : View - 회원가입 페이지
    // -----------------------------------------------------------------------------------
    function register()
    {
        return view('register');
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : registerPost()
    // 설명     : 사용자의 회원가입을 처리하는 함수
    //            회원가입 정보 유효성 검사 후 회원을 생성하고 자동으로 로그인 처리
    //
    // param    : Request $req - 사용자 요청 객체 (회원가입 정보 포함)
    //
    // return   : RedirectResponse - 메인 페이지로 리다이렉트
    // -----------------------------------------------------------------------------------
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

    // -----------------------------------------------------------------------------------
    // 함수명   : loginPost()
    // 설명     : 사용자 로그인을 처리하는 함수
    //            입력된 이메일과 비밀번호를 확인하고 로그인, 로그인 후 메인 페이지로 리다이렉트
    //
    // param    : Request $req - 사용자 요청 객체 (로그인 정보 포함)
    //
    // return   : RedirectResponse - 메인 페이지로 리다이렉트 또는 오류 메시지 반환
    // -----------------------------------------------------------------------------------
    public function loginPost(Request $req) {
        // 유효성 검사
        $req->validate([
            'email' => 'required',
            'pw'    => 'required'
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

    // -----------------------------------------------------------------------------------
    // 함수명   : redirect()
    // 설명     : 소셜 로그인 리디렉션을 처리하는 함수
    //
    // param    : string $provider - 소셜 로그인 제공자 (예: 'kakao', 'google', 'naver')
    //
    // return   : RedirectResponse - 소셜 로그인 페이지로 리디렉션
    // -----------------------------------------------------------------------------------
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : handleCallback()
    // 설명     : 소셜 로그인 콜백을 처리하는 함수
    //
    // param    : string $provider - 소셜 로그인 제공자
    //
    // return   : RedirectResponse - 소셜 로그인 후 메인 페이지로 리디렉트
    // -----------------------------------------------------------------------------------
    public function handleCallback($provider)
    {
        $user = Socialite::driver($provider)->user();
        return $this->handleUserLogin($user, $provider);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : handleUserLogin()
    // 설명     : 소셜 사용자 로그인 처리를 담당하는 함수
    //            기존 소셜 사용자라면 로그인, 새로운 사용자라면 회원 생성 후 로그인
    //
    // param    : object $user - 소셜 로그인 사용자 객체
    //            string $provider - 소셜 로그인 제공자
    //
    // return   : RedirectResponse - 메인 페이지로 리디렉트
    // -----------------------------------------------------------------------------------
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

    // -----------------------------------------------------------------------------------
    // 함수명   : logout()
    // 설명     : 사용자 로그아웃을 처리하는 함수
    //            세션 데이터 삭제 후 메인 페이지로 리다이렉트
    //
    // param    : 없음
    //
    // return   : RedirectResponse - 메인 페이지로 리다이렉트
    // -----------------------------------------------------------------------------------
    public function logout() 
    {
        Session::flush(); //세션 파기
        Auth::logout();
        return redirect()->route('main');
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : mypage()
    // 설명     : 마이페이지를 표시하는 함수
    //            로그인된 사용자의 배송지 목록을 기본 배송지부터 정렬하여 반환
    //            로그인된 사용자의 주문 내역을 함께 반환
    //
    // param    : 없음
    //
    // return   : View - 마이페이지 (배송지 목록 및 주문 내역 포함)
    // -----------------------------------------------------------------------------------
    public function mypage() 
    {
        $user = Auth::user();

        // 기본 배송지부터 상단에 출력되게 가져옴
        $addresses = auth()
            ->user()
            ->addresses()
            ->orderBy('default', 'desc')
            ->get();
    
        // 로그인된 사용자의 주문 내역 조회
        $orders = OrderModel::with(['orderItems.product', 'payment'])
            ->with('address')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    
        return view('mypage')->with([
            'user'      => $user,
            'addresses' => $addresses,
            'orders'    => $orders,
        ]);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : checkPassword()
    // 설명     : 마이페이지 내정보 접근시 비밀번호 재확인
    //
    // param    : Request $req - 사용자 요청 객체 (비밀번호 포함)
    //
    // return   : JsonResponse - 성공 메시지와 사용자 정보 반환 또는 오류 메시지
    // -----------------------------------------------------------------------------------
    public function checkPassword(Request $req)
    {
        $req->validate([
            'password' => 'required|string',
        ]);
    
        $user = Auth::user();

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => '비회원은 접근할 수 없습니다.',
            ], 401);
        }

        if (Hash::check($req->password, $user->pw)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('Y-m-d H:i'),
                ],
            ], 200);
        }
    
        return response()->json([
            'success' => false,
            'message' => '비밀번호가 일치하지 않습니다.',
        ], 401);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : changePassword()
    // 설명     : 비밀번호 변경을 처리하는 함수
    //
    // param    : Request $req - 사용자 요청 객체 (새 비밀번호 포함)
    //
    // return   : JsonResponse - 성공 메시지 반환
    // -----------------------------------------------------------------------------------
    public function changePassword(Request $req)
    {
        $req->validate([
            'new_password'      => 'required|regex:/^(?=.*[a-zA-Z])(?=.*[!@#$%^*-])(?=.*[0-9]).{8,20}$/',
            'confirm_password'  => 'required|same:new_password',
        ], [
            'new_password.required'     => '새 비밀번호를 입력해주세요.',
            'new_password.regex'        => '비밀번호는 영문, 숫자, 특수문자(!@#$%^*-)를 포함하여 8자 이상, 20자 이하로 입력해주세요.',
            'confirm_password.required' => '비밀번호 확인을 입력해주세요.',
            'confirm_password.same'     => '비밀번호와 비밀번호 확인이 일치하지 않습니다.',
        ]);

        $user = Auth::user();

        Log::debug(Hash::check($req->new_password, $user->pw));
        if (Hash::check($req->new_password, $user->pw)) {
            return response()->json([
                'success' => false,
                'message' => '기존 비밀번호와 동일합니다.',
            ]);
        }

        $user->pw = Hash::make($req->new_password);
        $user->save();

        // 로그아웃 처리
        Auth::logout();

        return response()->json([
            'success'       => true,
            'message'       => '비밀번호가 성공적으로 변경되었습니다.',
            'redirect_url'  => route('users.login'),
        ]);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : validateAddress()
    // 설명     : 배송지 정보 유효성 검사를 처리하는 함수
    //
    // param    : Request $req - 사용자 요청 객체 (배송지 정보)
    //
    // return   : array - 유효성 검사를 통과한 데이터
    // -----------------------------------------------------------------------------------
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
    
    // -----------------------------------------------------------------------------------
    // 함수명   : getAddress()
    // 설명     : 로그인된 사용자의 모든 배송지를 불러오는 함수
    //
    // param    : 없음
    //
    // return   : JsonResponse - 배송지 목록 또는 비회원 메시지 반환
    // -----------------------------------------------------------------------------------
    public function getAddress() {
        if (Auth::check()) {
            $addresses = auth()->user()->addresses()->orderBy('default', 'desc')->get();
            return response()->json([
                'msg' => '배송지를 불러왔습니다.',
                'addresses' => $addresses,
            ], 200);
        }
    
        // 비회원인 경우
        return response()->json([
            'msg' => '비회원입니다.',
            'addresses' => [],
        ], 401);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : addAddress()
    // 설명     : 배송지를 추가하는 함수
    //            기본 배송지 선택 시 다른 주소의 기본 설정을 해제하고, 새 주소를 추가
    //
    // param    : Request $req - 사용자 요청 객체 (배송지 정보)
    //
    // return   : JsonResponse - 새로 추가된 주소와 모든 배송지 목록 반환
    // -----------------------------------------------------------------------------------
    public function addAddress(Request $req) 
    {
        $validatedData = $this->validateAddress($req);

        // 체크박스가 선택된 경우, 다른 주소의 default 값을 0으로 설정
        if ($req->has('default')) {
            // 모든 주소의 default를 0으로 설정
            auth()->user()->addresses()->update(['default' => '0']);

            // 새로운 배송지의 default 값을 1로 설정
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
            'addresses' => auth()->user()->addresses()->orderBy('default', 'desc')->get(), // 갱신된 주소 목록 반환
            'newAddress' => $address, // 새로 생성된 주소 반환
        ]);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : editAddress()
    // 설명     : 배송지 정보를 수정하는 함수
    //            기본 배송지로 설정한 경우 다른 주소의 기본 설정을 해제하고 저장
    //
    // param    : Request $req - 사용자 요청 객체 (수정된 배송지 정보)
    //
    // return   : JsonResponse - 성공 메시지와 갱신된 배송지 목록 반환
    // -----------------------------------------------------------------------------------
    public function editAddress(Request $req)
    {
        $this->validateAddress($req);
        Log::debug($req->all());
        try {
            // 주소 ID로 주소를 찾아 업데이트
            $address = AddressModel::find($req->address_id);

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => '주소를 찾을 수 없습니다.',
                ]);
            }

            $address->recipient     = $req->recipient;
            $address->address       = $req->address;
            $address->detailAddress = $req->detailAddress;
            $address->extraAddress  = $req->extraAddress;
            $address->phone         = $req->phone;

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

            // 성공 메시지와 함께 새 주소를 반환
            return response()->json([
                'success' => true,
                'message' => '수정되었습니다.',
                'addresses' => auth()->user()->addresses()->orderBy('default', 'desc')->get() // 갱신된 주소 목록 반환
        ]);
        } catch (\Exception $e) {
            Log::error('주소 업데이트 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '주소 수정중 오류가 발생했습니다.',
            ]);
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : deleteAddress()
    // 설명     : 배송지 삭제를 처리하는 함수
    //
    // param    : Request $req - 사용자 요청 객체 (삭제할 주소 ID 포함)
    //
    // return   : JsonResponse - 성공 메시지와 갱신된 배송지 목록 반환 또는 오류 메시지
    // -----------------------------------------------------------------------------------
    public function deleteAddress(Request $req)
    {
        Log::debug($req);
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

    // -----------------------------------------------------------------------------------
    // 함수명   : getOrderByUuid()
    // 설명     : 비회원 주문 내역을 조회하는 함수
    //
    // param    : Request $req - 사용자 요청 객체 (비회원 UUID 포함)
    //
    // return   : JsonResponse - 성공 메시지와 비회원 주문 내역 반환 또는 오류 메시지
    // -----------------------------------------------------------------------------------
    public function getOrderByUuid($uuid) {
        return OrderModel::where('guest_uuid', $uuid)->get();
    }
}
