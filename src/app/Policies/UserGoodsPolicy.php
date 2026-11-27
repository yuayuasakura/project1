<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserGoods;

class UserGoodsPolicy
{
    // 更新していいか：自分の所持状況なら OK
    public function update(User $user, UserGoods $userGoods): bool
    {
        return $user->id === $userGoods->user_id;
    }

    // 削除していいか：自分の所持状況なら OK
    public function delete(User $user, UserGoods $userGoods): bool
    {
        return $user->id === $userGoods->user_id;
    }
}
