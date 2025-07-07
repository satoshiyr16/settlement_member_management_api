<?php

namespace App\Enums\Common;

enum UserRoleEnum: int
{
    case MEMBER = 10;
    case ADMIN = 99;

    /**
     * ユーザーのラベルを取得
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::MEMBER => '一般ユーザー',
            self::ADMIN => '管理者',
        };
    }
}
