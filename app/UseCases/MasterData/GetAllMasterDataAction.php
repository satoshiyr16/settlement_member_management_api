<?php

namespace App\UseCases\MasterData;

use App\Enums\GenderEnum;

/**
 * マスタデータ取得
 *
 * @return array
 */
final class GetAllMasterDataAction
{
    public function __invoke(): array
    {
        return [
            'genders' => $this->getGenderEnum(),
        ];
    }

    /**
     * 性別enumを取得
     *
     * @return array
     */
    private function getGenderEnum(): array
    {
        $genders = [];
        foreach (GenderEnum::cases() as $gender) {
            $genders[] = [
                'value' => $gender->value,
                'label' => $gender->label(),
            ];
        }
        return $genders;
    }
}
