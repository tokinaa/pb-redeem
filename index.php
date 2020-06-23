<?php
/**
 * Created by tokinaa
**/
function isRedeemVoucher($session) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://pointblank.id/event/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:76.0) Gecko/20100101 Firefox/76.0';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
    $headers[] = 'Accept-Language: en-US,en;q=0.5';
    $headers[] = 'Referer: https://pointblank.id/login/process';
    $headers[] = 'Connection: close';
    $headers[] = 'Cookie: '.$session;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    if(strpos($result, '<div class="info">Jika kamu sudah melakukan verifikasi, <br>kamu cukup <span>klaim hadiahnya langsung.</span></div>')) {
        return false;
    } else {
        return true;
    }
}
echo "Enter your username|password list : "; $file = trim(fgets(STDIN));
if($file) {
    echo "\n";
    $f = explode(PHP_EOL, file_get_contents($file));
    $i = 1;
    foreach($f as $fuck) {
        if(strpos($fuck, "|")){
            $datas = explode("|", $fuck);
            $username = $datas[0];
            $password = $datas[1];

            echo "ID : $username => ";

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://pointblank.id/login/process');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "loginFail=0&userid=$username&password=$password");
            curl_setopt($ch, CURLOPT_HEADER, 1);
            
            $headers = array();
            $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:76.0) Gecko/20100101 Firefox/76.0';
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
            $headers[] = 'Accept-Language: en-US,en;q=0.5';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Referer: https://pointblank.id/login/form';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            curl_close($ch);

            if(strpos($body, '<a href="javascript:void(0);" class="my_account_btn">')) {
                echo "SUKSES LOGIN | CEK REDEEM VOUCHER : ";
                preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $outCookie);
                $cookies = '';
                foreach($outCookie[1] as $outCookies) {
                    $cookies .= $outCookies.'; ';
                }

                $check = isRedeemVoucher($cookies);
                if($check == false) {
                    echo "BELUM DI REDEEM | ";
                } else {
                    echo "SUDAH DI REDEEM\n";
                    continue;
                }

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://pointblank.id/event/email/process');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

                $headers = array();
                $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:76.0) Gecko/20100101 Firefox/76.0';
                $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
                $headers[] = 'Accept-Language: en-US,en;q=0.5';
                $headers[] = 'Content-Type: application/x-www-form-urlencoded;charset=UTF-8';
                $headers[] = 'X-Requested-With: XMLHttpRequest';
                $headers[] = 'Referer: https://pointblank.id/event/email';
                $headers[] = 'Cookie: '.$cookies;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $result_json = json_decode($result);
                if(isset($result_json->voucher)) {
                    echo $result_json->voucher."\n";
                    file_put_contents('voucher.txt', "{$result_json->voucher}\n", FILE_APPEND | LOCK_EX);
                } else {
                    echo "GAGAL MENGAMBIL ! $result\n";
                }
            } else {
                echo "GAGAL LOGIN\n";
            }
            $i++;   
        }
    }
}