<?php

namespace App\Services;

use App\Models\PincodeServiceability;

class PincodeCsvImporter
{
    public const MAX_ROWS = 5000;

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(string $path): array
    {
        $parsed = $this->parse($path);
        $created = 0;
        $updated = 0;

        foreach ($parsed['rows'] as $row) {
            $existing = PincodeServiceability::query()->where('pincode', $row['pincode'])->first();
            if ($existing) {
                $existing->update($row);
                $updated++;
            } else {
                PincodeServiceability::create($row);
                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => count($parsed['errors']),
            'errors' => $parsed['errors'],
        ];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, errors: list<string>}
     */
    public function parse(string $path): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            return ['rows' => [], 'errors' => ['Could not read the CSV file.']];
        }

        $header = fgetcsv($fh);
        if (! is_array($header) || $header === []) {
            fclose($fh);

            return ['rows' => [], 'errors' => ['CSV is missing a header row.']];
        }

        $map = $this->headerMap($header);
        if (! isset($map['pincode'], $map['city'], $map['state'])) {
            fclose($fh);

            return ['rows' => [], 'errors' => ['CSV must include pincode, city, and state columns.']];
        }

        $rows = [];
        $errors = [];
        $line = 1;

        while (($raw = fgetcsv($fh)) !== false) {
            $line++;
            if ($this->isEmptyRow($raw)) {
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                $errors[] = 'Stopped after '.self::MAX_ROWS.' rows.';
                break;
            }

            $pincode = PincodeServiceability::normalizePincode((string) ($raw[$map['pincode']] ?? ''));
            $city = trim((string) ($raw[$map['city']] ?? ''));
            $state = trim((string) ($raw[$map['state']] ?? ''));

            if (strlen($pincode) !== 6 || $city === '' || $state === '') {
                $errors[] = 'Row '.$line.': pincode, city, and state are required (pincode must be 6 digits).';
                continue;
            }

            $dayOffset = isset($map['day_offset']) ? (int) ($raw[$map['day_offset']] ?? 3) : 3;
            $dayOffset = max(0, min(30, $dayOffset));
            $courier = isset($map['courier_name']) ? trim((string) ($raw[$map['courier_name']] ?? '')) : '';
            $status = isset($map['status']) ? $this->parseStatus($raw[$map['status']] ?? 1) : true;

            $rows[$pincode] = [
                'pincode' => $pincode,
                'city' => mb_substr($city, 0, 120),
                'state' => mb_substr($state, 0, 120),
                'day_offset' => $dayOffset,
                'courier_name' => $courier !== '' ? mb_substr($courier, 0, 120) : null,
                'status' => $status,
            ];
        }

        fclose($fh);

        return ['rows' => array_values($rows), 'errors' => $errors];
    }

    /**
     * @param  list<string|null>  $header
     * @return array<string, int>
     */
    protected function headerMap(array $header): array
    {
        $aliases = [
            'pincode' => ['pincode', 'pin', 'pin_code', 'pincode_code'],
            'city' => ['city'],
            'state' => ['state'],
            'day_offset' => ['day_offset', 'days', 'eta_days', 'offset'],
            'courier_name' => ['courier_name', 'courier', 'partner'],
            'status' => ['status', 'enabled', 'active'],
        ];

        $map = [];
        foreach ($header as $index => $label) {
            $key = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $label) ?? ''));
            foreach ($aliases as $field => $names) {
                if (in_array($key, $names, true)) {
                    $map[$field] = (int) $index;
                }
            }
        }

        return $map;
    }

    /**
     * @param  list<mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  mixed  $value
     */
    protected function parseStatus($value): bool
    {
        $raw = strtolower(trim((string) $value));

        return ! in_array($raw, ['0', 'false', 'no', 'disabled', 'inactive', 'off'], true);
    }
}
