<?php

namespace Modules\Base\Libraries\Captcha\Image;

use Modules\Base\Libraries\Captcha\CaptchaInterface;

class ImageCaptcha implements CaptchaInterface
{
    const RETRY_MAX = 1;

    public function generate()
    {
        $ic = new ImageCode\ImageCode();
        $captcha_img  = $ic->make();
        $captcha_code = $ic->get();

        session()->put('img_captcha', $captcha_code);
        session()->put('img_captcha_try', 0);
        session()->put('img_captcha_end', time() + 180);

        return $captcha_img;
    }

    public function send($target = null, $code = null)
    {
        $captcha_img = $code ?: $this->generate();

        header("Content-type:image/png");
        imagepng($captcha_img);
        imagedestroy($captcha_img);
        exit;
    }

    public function validate($code)
    {
        if (
          !session()->has('img_captcha')
          || time() > session()->get('img_captcha_end')
          || session()->get('img_captcha_try' > self::RETRY_MAX)
        ) {
            $this->destroy();
            throw new \Exception('验证码已失效');
        }

        if ($code == session()->get('img_captcha')) {
            $this->destroy();
        } elseif (time() < session()->get('img_captcha_end') || session()->get('img_captcha_try') < self::RETRY_MAX) {
            $retry = session()->get('img_captcha_try');
            session()->put('img_captcha_try', $retry);

            throw new \Exception('验证码错误，请重新输入');
        } else {
            $this->destroy();
            throw new \Exception('验证码错误，请重新获取');
        }

        return true;
    }

    public function destroy()
    {
        session()->forget('img_captcha');
        session()->forget('img_captcha_try');
        session()->forget('img_captcha_end');
    }
}
