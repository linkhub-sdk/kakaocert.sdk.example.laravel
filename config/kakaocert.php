<?php

return [

    // 파트너 신청시 발급받은 링크아이디
    'LinkID' => 'TESTER',

    // 파트너 신청시 발급받은 비밀키
    'SecretKey' => 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=',

    // 통신방식 기본은 CURL , PHP curl 모듈 사용에 문제가 있을 경우 STREAM 기재가능.
    // STREAM 사용시에는 php.ini의 allow_url_fopen = on 으로 설정해야함.
    'LINKHUB_COMM_MODE' => 'CURL',

    // 인증토큰의 IP제한기능 사용여부, 권장(true)
    'IPRestrictOnOff' => true,

    // 카카오써트 API 서비스 고정 IP 사용여부, true-사용, false-미사용, 기본값(false)
    'UseStaticIP' => false,

    // 로컬시스템 시간 사용 여부 true(기본값) - 사용, false(미사용)
    'UseLocalTimeYN' => true,

];
