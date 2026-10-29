<?php
/**
 * Validasi input sederhana dengan gaya fluent.
 *   $v = (new Validator())->required('nis', 'NIS', $nis)->max('nis', 'NIS', $nis, 30);
 *   if ($v->fails()) { $errors = $v->errors(); }
 */
class Validator
{
    private $errors = [];

    public function required(string $field, string $label, $value): self
    {
        if (trim((string)$value) === '') {
            $this->add($field, $label . ' wajib diisi.');
        }
        return $this;
    }

    public function max(string $field, string $label, $value, int $max): self
    {
        if (mb_strlen((string)$value) > $max) {
            $this->add($field, $label . ' maksimal ' . $max . ' karakter.');
        }
        return $this;
    }

    public function min(string $field, string $label, $value, int $min): self
    {
        if ((string)$value !== '' && mb_strlen((string)$value) < $min) {
            $this->add($field, $label . ' minimal ' . $min . ' karakter.');
        }
        return $this;
    }

    public function pattern(string $field, string $message, $value, string $regex): self
    {
        if ((string)$value !== '' && !preg_match($regex, (string)$value)) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function date(string $field, string $label, $value): self
    {
        $d = DateTime::createFromFormat('Y-m-d', (string)$value);
        if (!$d || $d->format('Y-m-d') !== (string)$value) {
            $this->add($field, $label . ' tidak valid.');
        }
        return $this;
    }

    public function integer(string $field, string $label, $value, int $min = 0, int $max = PHP_INT_MAX): self
    {
        if ((string)$value === '') {
            return $this; // kosong ditangani required()
        }
        if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]) === false) {
            $this->add($field, $label . ' harus berupa angka bulat antara ' . $min . ' dan ' . $max . '.');
        }
        return $this;
    }

    /** Tahun terbit opsional: jika diisi harus antara 1900 dan tahun depan. */
    public function year(string $field, string $label, $value): self
    {
        if ((string)$value === '') {
            return $this;
        }
        return $this->integer($field, $label, $value, 1900, (int)date('Y') + 1);
    }

    public function add(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(): string
    {
        return $this->errors ? (string)reset($this->errors) : '';
    }
}
