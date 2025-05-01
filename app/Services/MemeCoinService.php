<?php

namespace App\Services;

use App\Models\MemeCoin;
use App\Models\MemeCoinAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Events\MemeCoinAttempted;
use App\Models\User;
use Throwable;

class MemeCoinService
{
    public function generate(User $user, string $fullName): JsonResponse
    {
        $base = $this->buildBaseCoinName($fullName);

        for ($attempt = 1; $attempt <= 3; $attempt++) {

            // First try = base, subsequent tries get numeric suffix
            $name = $attempt === 1 ? $base : $base . $attempt;

            if (!MemeCoin::where('coin_name', $name)->exists()) {
                try {
                    DB::transaction(function () use ($user, $fullName, $name, $attempt) {
                        MemeCoin::create([
                            'user_id'   => $user->id,
                            'full_name' => $fullName,
                            'coin_name' => $name,
                            'attempts'  => $attempt,
                        ]);
                    });

                    // Log SUCCESS attempt
                    $this->logAttempt($user, $fullName, $name, $attempt, 'success');

                    return response()->json([
                        'status'    => 'success',
                        'coin_name' => $name,
                        'message'   => 'MemeCoin name generated!',
                    ]);
                } catch (Throwable $e) {
                    report($e);
                    break;
                }
            }

            // Duplicate – log and continue loop
            $this->logAttempt($user, $fullName, $name, $attempt, 'duplicate');
        }

        // If all 3 names were taken then exhausted
        $this->logAttempt($user, $fullName, $base . '4', 4, 'exhausted', 'No unique names available');

        return response()->json([
            'status'  => 'error',
            'message' => 'No unique names available',
        ], 409);
    }

    /* ---------------------------------------------------------- */
    /*  Helpers                                                   */
    /* ---------------------------------------------------------- */

    protected function logAttempt(
        User   $user,
        string $fullName,
        string $attemptedName,
        int    $attemptNumber,
        string $status,
        ?string $message = null
    ): void {
        event(new MemeCoinAttempted(
            $user,
            $fullName,
            $attemptedName,
            $attemptNumber,
            $status,
            $message
        ));
    }

    public function buildBaseCoinName(string $fullName): string
    {
        $words    = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
        $short    = collect($words)->map(
            fn($w) => ucfirst(mb_substr($w, 0, 2))
        )->implode('');

        $vowels   = preg_match_all('/[aeiou]/iu', $fullName);
        $modifier = ($vowels % 2 === 0) ? 'HODL' : 'Moon';

        $suffixes = ['Coin', 'Token', 'Rocket', 'Doge'];
        $length   = mb_strlen(str_replace(' ', '', $fullName));
        $suffix   = $suffixes[$length % 4];

        return $modifier . $short . $suffix;
    }
}
