<?php

namespace Modules\Base\Libraries\Captcha;

interface CaptchaInterface
{
    public function generate();
    public function send($target, $code);
    public function validate($target, $code);
    public function destroy();
}
