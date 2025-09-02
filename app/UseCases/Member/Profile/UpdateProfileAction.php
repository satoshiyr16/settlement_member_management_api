<?php

namespace App\UseCases\Member\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MemberProfile;

/**
 * メンバーの情報更新を行う
 *
 * @param array $values
 * @return MemberProfile
 */
final class UpdateProfileAction
{
    public function __invoke(array $values): MemberProfile
    {
        $userId = Auth::guard('member')->user()->id;
        $memberProfile = MemberProfile::where('user_id', $userId)->firstOrFail();

        DB::transaction(function () use ($values, $memberProfile) {
            $memberProfile->update($values);
        });

        return $memberProfile;
    }
}
