<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
        parent::actingAs($user, $driver);

        // Untuk test suite fungsional lama (Surat, UserEdit, RoleAccess),
        // simulasikan bahwa user telah menyelesaikan 2FA jika bukan sedang menguji TwoFactorAuthenticationTest atau ForcePasswordResetTest.
        if (!($this instanceof \Tests\Feature\TwoFactorAuthenticationTest) && !($this instanceof \Tests\Feature\ForcePasswordResetTest)) {
            if ($user instanceof \App\Models\User && !$user->must_change_password) {
                if (!$user->two_factor_confirmed_at) {
                    $user->two_factor_confirmed_at = now();
                    $user->saveQuietly();
                }
                session(['2fa_passed' => true]);
            }
        }

        return $this;
    }
}
