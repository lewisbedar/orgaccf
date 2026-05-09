<?php

namespace App\Services;

class PronoteCsvImporter
{
    public function parse(string $path): array
    {
        $raw = file_get_contents($path) ?: '';
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
        $firstLine = strtok($raw, "\n") ?: '';
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $raw);
        rewind($stream);

        $headers = fgetcsv($stream, 0, $delimiter) ?: [];
        $map = [];
        foreach ($headers as $index => $header) {
            $map[$this->key((string) $header)] = $index;
        }

        $students = [];
        while (($row = fgetcsv($stream, 0, $delimiter)) !== false) {
            if (trim(implode('', $row)) === '') {
                continue;
            }

            $last = $this->value($row, $map, ['nom']);
            $first = $this->value($row, $map, ['prenom']);
            if ($last === '' || $first === '') {
                [$lastFromFull, $firstFromFull] = $this->splitFullName($this->value($row, $map, ['eleves', 'eleve']));
                $last = $last ?: $lastFromFull;
                $first = $first ?: $firstFromFull;
            }

            if ($last === '' && $first === '') {
                continue;
            }

            $students[] = [
                'last_name' => mb_strtoupper($last, 'UTF-8'),
                'first_name' => mb_convert_case(mb_strtolower($first, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'birth_date' => $this->date($this->value($row, $map, ['nele', 'neele', 'datenaissance', 'naissance'])),
                'gender' => $this->value($row, $map, ['sexe']) ?: null,
                'email' => $this->value($row, $map, ['email', 'adressemail', 'courriel']) ?: null,
                'pronote_options' => $this->options($row, $map),
            ];
        }

        fclose($stream);
        return $students;
    }

    private function key(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        return preg_replace('/[^a-z0-9]/', '', strtolower($value)) ?: '';
    }

    private function value(array $row, array $map, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($map[$key])) {
                return trim((string) ($row[$map[$key]] ?? ''));
            }
        }
        return '';
    }

    private function splitFullName(string $full): array
    {
        $parts = preg_split('/\s+/', trim($full)) ?: [];
        $last = [];
        while ($parts && mb_strtoupper($parts[0], 'UTF-8') === $parts[0]) {
            $last[] = array_shift($parts);
        }
        if (!$last && $parts) {
            $last[] = array_shift($parts);
        }
        return [implode(' ', $last), implode(' ', $parts)];
    }

    private function date(string $value): ?string
    {
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }

    private function options(array $row, array $map): ?string
    {
        $options = [];
        foreach ($map as $header => $index) {
            if (str_starts_with($header, 'option') && trim((string) ($row[$index] ?? '')) !== '') {
                $options[] = trim((string) $row[$index]);
            }
        }
        return $options ? implode(', ', $options) : null;
    }
}
