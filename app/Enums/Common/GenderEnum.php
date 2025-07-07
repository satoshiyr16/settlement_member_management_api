<?php

namespace App\Enums\Common;

enum GenderEnum: int
{
    case MALE = 1;
    case FEMALE = 2;
    case OTHER = 3;
    case PREFER_NOT_TO_SAY = 4;

    /**
     * 性別のラベルを取得
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::MALE => '男性',
            self::FEMALE => '女性',
            self::OTHER => 'その他',
            self::PREFER_NOT_TO_SAY => '回答しない',
        };
    }
}
