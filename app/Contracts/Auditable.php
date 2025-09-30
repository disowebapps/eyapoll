<?php

namespace App\Contracts;

interface Auditable
{
    public function getAuditId(): int;
    public function getAuditName(): string;
    public function getAuditType(): string;
}