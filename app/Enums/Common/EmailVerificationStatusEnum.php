<?php

namespace App\Enums\Common;

enum EmailVerificationStatusEnum: int
{
    case SEND_MAIL_REGISTER = 1;
    case COMPLETED_REGISTER = 2;
    case SEND_MAIL_FORGOT_PASSWORD = 3;
    case COMPLETED_FORGOT_PASSWORD = 4;
}
