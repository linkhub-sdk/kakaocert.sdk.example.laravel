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
  }

  // HTTP Get Request URI -> 함수 라우팅 처리 함수
  public function RouteHandelerFunc(Request $request){
    $APIName = $request->route('APIName');
    return $this->$APIName();
  }

  public function RequestCMS(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 자동이체 출금동의 요청정보 객체
    $RequestCMS = new RequestCMS();

    // 고객센터 전화번호, 카카오톡 인증메시지 중 "고객센터" 항목에 표시
    $RequestCMS->CallCenterNum = '1600-8536';

    // 인증요청 만료시간(초), 인증요청 만료시간(초) 내에 미인증시, 만료 상태로 처리됨
  	$RequestCMS->Expires_in = 60;

    // 수신자 생년월일, 형식 : YYYYMMDD
  	$RequestCMS->ReceiverBirthDay = '19700101';

    // 수신자 휴대폰번호
  	$RequestCMS->ReceiverHP = '0101234';

    // 수신자 성명
  	$RequestCMS->ReceiverName = '테스트';

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
        $receiptID = $this->KakaocertService->requestCMS($clientCode, $RequestCMS);
    }
    catch(KakaocertException | LinkhubException $ke) {
        $code = $ke->getCode();
        $message = $ke->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }


    return view('ReturnValue', ['filedName' => "자동이체 출금동의 접수아이디", 'value' => $receiptID]);
  }

  public function GetCMSResult(){

    // Kakaocert 이용기관코드, Kakaocert 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 자동이체 출금동의 요청시 반환받은 접수아이디
    $receiptID = '020050612225500001';

    try {
        $result = $this->KakaocertService->getCMSResult($clientCode, $receiptID);
    }
    catch(KakaocertException | LinkhubException $ke) {
        $code = $ke->getCode();
        $message = $ke->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetCMSResult', ['result' => $result]);
  }
}
