<?php

namespace App\Services\Auth;

use App\Models\MFASetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class MFAService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function enableMFA(User $user, string $secret, string $code): bool
    {
        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        $user->mfaSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $secret,
                'enabled' => true,
                'backup_codes' => $this->generateBackupCodes(),
            ]
        );

        return true;
    }

    public function disableMFA(User $user): void
    {
        $user->mfaSetting()->update(['enabled' => false]);
    }

    public function isMFAEnabled(User $user): bool
    {
        return $user->mfaSetting?->enabled ?? false;
    }

    public function getMFASetting(User $user): ?MFASetting
    {
        return $user->mfaSetting;
    }

    public function verifyMFACode(User $user, string $code): bool
    {
        $setting = $this->getMFASetting($user);
        if (!$setting || !$setting->enabled) {
            return false;
        }

        return $this->verifyCode($setting->secret, $code);
    }

    public function useBackupCode(User $user, string $code): bool
    {
        $setting = $this->getMFASetting($user);
        if (!$setting || !$setting->backup_codes) {
            return false;
        }

        $codes = $setting->backup_codes;
        $index = array_search($code, $codes);

        if ($index !== false) {
            unset($codes[$index]);
            $setting->update(['backup_codes' => array_values($codes)]);
            return true;
        }

        return false;
    }

    protected function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }
        return $codes;
    }
}