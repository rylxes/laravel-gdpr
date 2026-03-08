<?php

namespace Rylxes\Gdpr\Contracts;

interface Exportable
{
    /**
     * Return the user's data for export as an associative array.
     *
     * Keys become column headers in CSV or property names in JSON/XML.
     *
     * @return array<string, mixed>
     */
    public function exportData(): array;

    /**
     * Return a human-readable label for this data category.
     *
     * Examples: "Profile", "Orders", "Comments"
     */
    public function exportLabel(): string;
}
