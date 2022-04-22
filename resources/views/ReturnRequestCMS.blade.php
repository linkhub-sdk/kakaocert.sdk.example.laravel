<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="/css/example.css" media="screen"/>
    <title>Kakaocert SDK PHP Laravel Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>{{\Request::fullUrl()}}</legend>
        <ul>
            <li>receiptId (접수아이디)  :  {{ $receiptId }}</li>
            <li>tx_id (카카오톡 트랜잭션아이디) [AppToApp-앱스킴 호출시 기재] :  {{ $tx_id }}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
