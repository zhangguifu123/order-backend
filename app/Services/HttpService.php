<?php

namespace App\Services;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class HttpService
{

//测试
    public function pushWcChat(Request $request){
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://www.baidu.com',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $response = $client->request('POST', 'http://httpbin.org/post', [
            'form_params' => [
                'field_name' => 'abc',
                'other_field' => '123',
                'nested_field' => [
                    'nested' => 'hello'
                ]
            ]
        ]);
        $response     = Htt::get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx95aba4fd9b40e13d&secret=bcd16232a8be911b8bfdabf4fbf77e5c');
        $responseData = json_decode($response->body(),true);
        $accessToken  =  $responseData['access_token'];
        $response     = Http::post('https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$accessToken, [
            'media' => $request->file('image'),
        ]);

        return $response->body();

    }
}
