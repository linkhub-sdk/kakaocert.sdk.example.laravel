<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Linkhub\LinkhubException;
use Linkhub\Kakaocert\KakaocertException;
use Linkhub\Kakaocert\KakaocertService;
use Linkhub\Kakaocert\RequestESign;
use Linkhub\Kakaocert\ResultESign;
use Linkhub\Kakaocert\RequestVerifyAuth;
use Linkhub\Kakaocert\ResultVerifyAuth;
use Linkhub\Kakaocert\RequestCMS;
use Linkhub\Kakaocert\ResultCMS;

class KakaocertController extends Controller
{
  public function __construct() {

    // 통신방식 설정
    define('LINKHUB_COMM_MODE', config('kakaocert.LINKHUB_COMM_MODE'));

    // kakaocert 서비스 클래스 초기화
    $this->KakaocertService = new KakaocertService(config('kakaocert.LinkID'), config('kakaocert.SecretKey'));

    // 인증토큰의 IP제한기능 사용여부, 권장(true)
    $this->KakaocertService->IPRestrictOnOff(config('kakaocert.IPRestrictOnOff'));

    // 카카오써트 API 서비스 고정 IP 사용여부, true-사용, false-미사용, 기본값(false)
    $this->KakaocertService->UseStaticIP(config('kakaocert.UseStaticIP'));

    // 로컬시스템 시간 사용 여부 true(기본값) - 사용, false(미사용)
    $this->KakaocertService->UseLocalTimeYN(config('kakaocert.UseLocalTimeYN'));
  }

  // HTTP Get Request URI -> 함수 라우팅 처리 함수
  public function RouteHandelerFunc(Request $request){
    $APIName = $request->route('APIName');
    return $this->$APIName();
  }

  /*
  * 자동이체 출금동의 인증을 요청합니다.
  * - 해당 서비스는 전자서명을 하는 당사자와 출금계좌의 예금주가 동일한 경우에만 사용이 가능합니다.
  * - 전자서명 당사자와 출금계좌의 예금주가 동일인임을 체크하는 의무는 이용기관에 있습니다.
  * - 금융결제원에 증빙자료(전자서명 데이터) 제출은 이용기관 측 에서 진행해야 합니다.
  */
  public function RequestCMS(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 출금동의 AppToApp 인증 여부
    // true-App To App 방식, false-Talk Message 방식
    $isAppUseYN = false;

    // 자동이체 출금동의 요청정보 객체
    $RequestCMS = new RequestCMS();

    // 고객센터 전화번호, 카카오톡 인증메시지 중 "고객센터" 항목에 표시
    $RequestCMS->CallCenterNum = '1600-8536';

    // 고객센터명
    $RequestCMS->CallCenterName = '테스트';

    // 인증요청 만료시간(초), 최대값 1000, 인증요청 만료시간(초) 내에 미인증시 만료 상태로 처리됨
    $RequestCMS->Expires_in = 60;

    // 수신자 생년월일, 형식 : YYYYMMDD
    $RequestCMS->ReceiverBirthDay = '19700101';

    // 수신자 휴대폰번호
    $RequestCMS->ReceiverHP = '010111222';

    // 수신자 성명
    $RequestCMS->ReceiverName = '홍길동';

    // 예금주명
    $RequestCMS->BankAccountName = '예금주명';

    // 계좌번호, 이용기관은 사용자가 식별가능한 범위내에서 계좌번호의 일부를 마스킹 처리할 수 있음 (예시) 371-02-6***85
    $RequestCMS->BankAccountNum = '9-4324-5**7-58';

    // 은행코드
    $RequestCMS->BankCode = '004';

    // 납부자번호, 이용기관에서 부여한 고객식별번호
    $RequestCMS->ClientUserID = 'clientUserID-0423-01';

    // 별칭코드, 이용기관이 생성한 별칭코드 (파트너 사이트에서 확인가능)
    // 카카오톡 인증메시지 중 "요청기관" 항목에 표시
    // 별칭코드 미 기재시 이용기관의 이용기관명이 "요청기관" 항목에 표시
    $RequestCMS->SubClientID = '';

    // 인증요청 메시지 부가내용, 카카오톡 인증메시지 중 상단에 표시
    $RequestCMS->TMSMessage = 'TMSMessage0423';

    // 인증요청 메시지 제목, 카카오톡 인증메시지 중 "요청구분" 항목에 표시
    $RequestCMS->TMSTitle = 'TMSTitle 0423';

    // 전자서명할 토큰 원문
    $RequestCMS->Token = "TMS Token 0423 ";

    // 은행계좌 실명확인 생략여부
    // true : 은행계좌 실명확인 절차를 생략
    // false : 은행계좌 실명확인 절차를 진행
    // 카카오톡 인증메시지를 수신한 사용자가 카카오인증 비회원일 경우, 카카오인증 회원등록 절차를 거쳐 은행계좌 실명확인 절차를 밟은 다음 전자서명 가능
    $RequestCMS->isAllowSimpleRegistYN = true;

    // 수신자 실명확인 여부
    // true : 카카오페이가 본인인증을 통해 확보한 사용자 실명과 ReceiverName 값을 비교
    // false : 카카오페이가 본인인증을 통해 확보한 사용자 실명과 RecevierName 값을 비교하지 않음.
    $RequestCMS->isVerifyNameYN = true;

    // PayLoad, 이용기관이 생성한 payload(메모) 값
    $RequestCMS->PayLoad = 'Payload123';

    try {
        $receiptID = $this->KakaocertService->requestCMS($clientCode, $RequestCMS, $isAppUseYN);
    }
    catch(KakaocertException $ke) {
        $code = $ke->getCode();
        $message = $ke->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }


    return view('ReturnValue', ['filedName' => "자동이체 출금동의 접수아이디", 'value' => $receiptID]);
  }

  /*
  * 자동이체 출금동의 요청에 대한 서명 상태를 확인합니다.
  */
  public function GetCMSState(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 자동이체 출금동의 요청시 반환받은 접수아이디
    $receiptID = '020090816455000001';

    try {
      $result = $this->KakaocertService->getCMSState($clientCode, $receiptID);
    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetCMSState', ['result' => $result]);
  }

  /*
  * 자동이체 출금동의 서명을 검증합니다.
  * - 서명검증시 전자서명 데이터 전문(signedData)이 반환됩니다.
  * - 카카오페이 서비스 운영정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류처리됩니다.
  */
  public function VerifyCMS(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 자동이체 출금동의 요청시 반환받은 접수아이디
    $receiptID = '020090816455000001';

    try {
      $result = $this->KakaocertService->verifyCMS($clientCode, $receiptID);
    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('ResponseVerify', ['result' => $result]);
  }

  /*
  * 본인인증을 요청합니다.
  * - 본인인증 서비스에서 이용기관이 생성하는 Token은 사용자가 전자서명할 원문이 됩니다. 이는 보안을 위해 1회용으로 생성해야 합니다.
  * - 사용자는 이용기관이 생성한 1회용 토큰을 서명하고, 이용기관은 그 서명값을 검증함으로써 사용자에 대한 인증의 역할을 수행하게 됩니다.
  */
  public function RequestVerifyAuth(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 본인인증 요청정보 객체
    $RequestVerifyAuth = new RequestVerifyAuth();

    // 고객센터 전화번호, 카카오톡 인증메시지 중 "고객센터" 항목에 표시
    $RequestVerifyAuth->CallCenterNum = '1600-8536';

    // 고객센터명
    $RequestVerifyAuth->CallCenterName = '테스트';

    // 인증요청 만료시간(초), 최대값 1000, 인증요청 만료시간(초) 내에 미인증시 만료 상태로 처리됨
    $RequestVerifyAuth->Expires_in = 60;

    // 수신자 생년월일, 형식 : YYYYMMDD
    $RequestVerifyAuth->ReceiverBirthDay = '19700101';

    // 수신자 휴대폰번호
    $RequestVerifyAuth->ReceiverHP = '010111222';

    // 수신자 성명
    $RequestVerifyAuth->ReceiverName = '홍길동';

    // 별칭코드, 이용기관이 생성한 별칭코드 (파트너 사이트에서 확인가능)
    // 카카오톡 인증메시지 중 "요청기관" 항목에 표시
    // 별칭코드 미 기재시 이용기관의 이용기관명이 "요청기관" 항목에 표시
    $RequestVerifyAuth->SubClientID = '';

    // 인증요청 메시지 부가내용, 카카오톡 인증메시지 중 상단에 표시
    $RequestVerifyAuth->TMSMessage = 'TMSMessage0423';

    // 인증요청 메시지 제목, 카카오톡 인증메시지 중 "요청구분" 항목에 표시
    $RequestVerifyAuth->TMSTitle = 'TMSTitle 0423';

    // 토큰 원문
    $RequestVerifyAuth->Token = "TMS Token 0423 ";

    // 은행계좌 실명확인 생략여부
    // true : 은행계좌 실명확인 절차를 생략
    // false : 은행계좌 실명확인 절차를 진행
    // 카카오톡 인증메시지를 수신한 사용자가 카카오인증 비회원일 경우, 카카오인증 회원등록 절차를 거쳐 은행계좌 실명확인 절차를 밟은 다음 전자서명 가능
    $RequestVerifyAuth->isAllowSimpleRegistYN = false;

    // 수신자 실명확인 여부
    // true : 카카오페이가 본인인증을 통해 확보한 사용자 실명과 ReceiverName 값을 비교
    // false : 카카오페이가 본인인증을 통해 확보한 사용자 실명과 RecevierName 값을 비교하지 않음.
    $RequestVerifyAuth->isVerifyNameYN = false;

    // PayLoad, 이용기관이 생성한 payload(메모) 값
    $RequestVerifyAuth->PayLoad = 'Payload123';

    try {
      $receiptID = $this->KakaocertService->requestVerifyAuth($clientCode, $RequestVerifyAuth);

    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('ReturnValue', ['filedName' => "본인인증 요청 접수아이디", 'value' => $receiptID]);

  }

  /*
  * 본인인증 요청에 대한 서명 상태를 확인합니다.
  */
  public function GetVerifyAuthState(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 본인인증 요청시 반환받은 접수아이디
    $receiptID = '020090816475200001';

    try {
      $result = $this->KakaocertService->getVerifyAuthState($clientCode, $receiptID);
    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetVerifyAuthState', ['result' => $result]);
  }

  /*
  * 본인인증 서명을 검증합니다.
  * - 서명검증시 전자서명 데이터 전문(signedData)이 반환됩니다.
  * - 본인인증 요청시 기재한 Token과 서명 검증시 반환되는 signedData의 동일여부를 확인하여 본인인증 검증을 완료합니다.
  * - 카카오페이 서비스 운영정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류처리됩니다.
  */
  public function VerifyAuth(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 본인인증 요청시 반환받은 접수아이디
    $receiptID = '020090816475200001';

    try {
      $result = $this->KakaocertService->verifyAuth($clientCode, $receiptID);
    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('ResponseVerify', ['result' => $result]);
  }

  /*
  * 전자서명 서명을 요청합니다.
  */
  public function RequestESign(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 전자서명 AppToApp 호출여부
    $isAppUseYN = false;

    // 전자서명 요청정보 객체
    $RequestESign = new RequestESign();

    // 고객센터 전화번호, 카카오톡 인증메시지 중 "고객센터" 항목에 표시
    $RequestESign->CallCenterNum = '1600-8536';

    // 고객센터명
    $RequestESign->CallCenterName = '테스트';

    // 인증요청 만료시간(초), 최대값 1000, 인증요청 만료시간(초) 내에 미인증시 만료 상태로 처리됨
    $RequestESign->Expires_in = 60;

    // 수신자 생년월일, 형식 : YYYYMMDD
    $RequestESign->ReceiverBirthDay = '19700101';

    // 수신자 휴대폰번호
    $RequestESign->ReceiverHP = '010111222';

    // 수신자 성명
    $RequestESign->ReceiverName = '홍길동';

    // 별칭코드, 이용기관이 생성한 별칭코드 (파트너 사이트에서 확인가능)
    // 카카오톡 인증메시지 중 "요청기관" 항목에 표시
    // 별칭코드 미 기재시 이용기관의 이용기관명이 "요청기관" 항목에 표시
    // AppToApp 인증방식 이용시 적용되지 않음.
    $RequestESign->SubClientID = '';

    // 인증요청 메시지 부가내용, 카카오톡 인증메시지 중 상단에 표시
    // AppToApp 인증방식 이용시 적용되지 않음.
    $RequestESign->TMSMessage = 'TMSMessage0423';

    // 인증요청 메시지 제목, 카카오톡 인증메시지 중 "요청구분" 항목에 표시
    $RequestESign->TMSTitle = 'TMSTitle 0423';

    // 전자서명할 토큰 원문
    $RequestESign->Token = "TMS Token 0423 ";

    // 은행계좌 실명확인 생략여부
    // true : 은행계좌 실명확인 절차를 생략
    // false : 은행계좌 실명확인 절차를 진행
    // 카카오톡 인증메시지를 수신한 사용자가 카카오인증 비회원일 경우, 카카오인증 회원등록 절차를 거쳐 은행계좌 실명확인 절차를 밟은 다음 전자서명 가능
    $RequestESign->isAllowSimpleRegistYN = false;

    // 수신자 실명확인 여부
    // true : 카카오페이가 본인인증을 통해 확보한 사용자 실명과 ReceiverName 값을 비교
    // false : 카카오페이가 본인인증을 통해 확보한 사용자 실명과 RecevierName 값을 비교하지 않음.
    $RequestESign->isVerifyNameYN = true;

    // PayLoad, 이용기관이 생성한 payload(메모) 값
    $RequestESign->PayLoad = 'Payload123';

    try {
      $response = $this->KakaocertService->requestESign($clientCode, $RequestESign, $isAppUseYN);

    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('ReturnRequestESign', ['receiptId' => $response->receiptId, 'tx_id' => $response->tx_id]);

  }

  /*
  * 전자서명 서명 상태를 확인합니다.
  */
  public function GetESignState(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 전자서명 요청시 반환받은 접수아이디
    $receiptID = '020090816494800001';

    try {
      $result = $this->KakaocertService->getESignState($clientCode, $receiptID);
    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetESignState', ['result' => $result]);
  }

  /*
  * 전자서명 서명을 검증합니다.
  * - 서명검증시 전자서명 데이터 전문(signedData)이 반환됩니다.
  * - 카카오페이 서비스 운영정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류처리됩니다.
  */
  public function VerifyESign(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 전자서명 요청시 반환받은 접수아이디
    $receiptID = '020090816494800001';

    // [AppToApp 인증] 앱스킴 성공처리시 반환되는 서명값(iOS-sig, Android-signature) 기재
    // TalkToMessage 방식 이용시 null 기재
    $signature = null;

    try {
      $result = $this->KakaocertService->verifyESign($clientCode, $receiptID, $signature);
    }
    catch(KakaocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('ResponseVerify', ['result' => $result]);
  }


}
