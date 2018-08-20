<?php

namespace Apk\Auth\Controllers;

use Config;
use Session;

class CaptchaController extends \BaseController
{
    public function apiGet()
    {
        // 读取配置文件
        $config = Config::get('captcha.lyric');

        // 随机取一首歌
        $song = array_rand($config);
        $lyricList = $config[$song];

        // 随机取一句词
        $rand_key = array_rand($lyricList);
        $lyric = $lyricList[$rand_key];

        // 随机生成问题和答案
        $rand_len = 2;//rand(2, 3);■■
        $rand_pos = rand(0, mb_strlen($lyric) - 1 - $rand_len);
        $answer   = mb_substr($lyric, $rand_pos, $rand_len);
        $question = str_replace($answer, ' _ _ ', $lyric);

        // 把答案存到Session的闪存里
        Session::flash('captcha', $answer);

        $result = [
            'result'   => true,
            'question' => "<strong>《{$song}》</strong><br>".$question,
        ];

        return $result;
    }
}
