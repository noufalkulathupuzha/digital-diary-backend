<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DiaryEntry;
use App\Models\User;

class DiaryEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DiaryEntry $diaryEntry): bool
    {
        return $this->ownsEntry($user, $diaryEntry);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DiaryEntry $diaryEntry): bool
    {
        return $this->ownsEntry($user, $diaryEntry);
    }

    public function delete(User $user, DiaryEntry $diaryEntry): bool
    {
        return $this->ownsEntry($user, $diaryEntry);
    }

    private function ownsEntry(User $user, DiaryEntry $diaryEntry): bool
    {
        return $user->id === $diaryEntry->user_id;
    }
}
