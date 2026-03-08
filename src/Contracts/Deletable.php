<?php

namespace Rylxes\Gdpr\Contracts;

interface Deletable
{
    /**
     * Erase (anonymize or delete) this model's personal data.
     *
     * The implementation decides which fields to null/anonymize
     * or whether to delete the record entirely.
     */
    public function eraseData(): void;

    /**
     * Return the erasure strategy for this model.
     *
     * Supported values: 'anonymize' or 'delete'.
     * Falls back to config('gdpr.erasure.strategy') if not overridden.
     */
    public function erasureStrategy(): string;

    /**
     * Return an integer priority for FK-safe deletion ordering.
     *
     * Lower numbers are erased first (e.g., child records before parents).
     * Default should be 100. Override to control order.
     */
    public function erasurePriority(): int;
}
