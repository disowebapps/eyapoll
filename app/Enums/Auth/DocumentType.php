<?php

namespace App\Enums\Auth;

enum DocumentType: string
{
    case NATIONAL_ID = 'national_id';
    case PASSPORT = 'passport';
    case INTERNATIONAL_PASSPORT = 'international_passport';
    case DRIVERS_LICENSE = 'drivers_license';
    case DRIVERS_LICENSE_PROVISIONAL = 'drivers_license_provisional';
    case DRIVERS_LICENSE_FULL = 'drivers_license_full';
    case VISA = 'visa';
    case RESIDENCE_PERMIT = 'residence_permit';
    case BIRTH_CERTIFICATE = 'birth_certificate';
    case MARRIAGE_CERTIFICATE = 'marriage_certificate';
    case UTILITY_BILL = 'utility_bill';
    case BANK_STATEMENT = 'bank_statement';

    public function label(): string
    {
        return match($this) {
            self::NATIONAL_ID => 'National ID Card',
            self::PASSPORT => 'International Passport',
            self::INTERNATIONAL_PASSPORT => 'International Passport',
            self::DRIVERS_LICENSE => 'Driver\'s License',
            self::DRIVERS_LICENSE_PROVISIONAL => 'Provisional Driver\'s License',
            self::DRIVERS_LICENSE_FULL => 'Full Driver\'s License',
            self::VISA => 'Visa',
            self::RESIDENCE_PERMIT => 'Residence Permit',
            self::BIRTH_CERTIFICATE => 'Birth Certificate',
            self::MARRIAGE_CERTIFICATE => 'Marriage Certificate',
            self::UTILITY_BILL => 'Utility Bill',
            self::BANK_STATEMENT => 'Bank Statement',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::NATIONAL_ID => 'Government-issued national identification card',
            self::PASSPORT => 'International passport with photo and personal details',
            self::INTERNATIONAL_PASSPORT => 'Official international passport document',
            self::DRIVERS_LICENSE => 'Valid driver\'s license with photo',
            self::DRIVERS_LICENSE_PROVISIONAL => 'Provisional or learner driver\'s license',
            self::DRIVERS_LICENSE_FULL => 'Full unrestricted driver\'s license',
            self::VISA => 'Official visa document for travel or residence',
            self::RESIDENCE_PERMIT => 'Official residence or work permit',
            self::BIRTH_CERTIFICATE => 'Official birth certificate document',
            self::MARRIAGE_CERTIFICATE => 'Official marriage certificate',
            self::UTILITY_BILL => 'Recent utility bill for address verification',
            self::BANK_STATEMENT => 'Recent bank statement for verification',
        };
    }

    public function acceptedFormats(): array
    {
        return ['jpg', 'jpeg', 'png', 'pdf'];
    }

    public function maxSizeKB(): int
    {
        return 5120; // 5MB
    }

    public function validationRules(): array
    {
        return [
            'required',
            'file',
            'mimes:' . implode(',', $this->acceptedFormats()),
            'max:' . $this->maxSizeKB(),
        ];
    }

    public function icon(): string
    {
        return match($this) {
            self::NATIONAL_ID => 'heroicon-o-identification',
            self::PASSPORT => 'heroicon-o-document-text',
            self::INTERNATIONAL_PASSPORT => 'heroicon-o-document-text',
            self::DRIVERS_LICENSE => 'heroicon-o-credit-card',
            self::DRIVERS_LICENSE_PROVISIONAL => 'heroicon-o-credit-card',
            self::DRIVERS_LICENSE_FULL => 'heroicon-o-credit-card',
            self::VISA => 'heroicon-o-ticket',
            self::RESIDENCE_PERMIT => 'heroicon-o-home',
            self::BIRTH_CERTIFICATE => 'heroicon-o-document',
            self::MARRIAGE_CERTIFICATE => 'heroicon-o-heart',
            self::UTILITY_BILL => 'heroicon-o-receipt-refund',
            self::BANK_STATEMENT => 'heroicon-o-banknotes',
        };
    }

    public static function getSelectOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getSelectOptionsWithDescriptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'label' => $case->label(),
                    'description' => $case->description(),
                    'icon' => $case->icon(),
                ]
            ])
            ->toArray();
    }
}