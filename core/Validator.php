<?php

declare(strict_types=1);

namespace Core;

class Validator
{
    protected array $errors = [];

    public function required(string $field, mixed $value, string $message = null): self
    {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field][] = $message ?? "The {$field} field is required.";
        }

        return $this;
    }

    public function email(string $field, mixed $value, string $message = null): self
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "The {$field} field must be a valid email.";
        }

        return $this;
    }

    public function min(string $field, mixed $value, int $length, string $message = null): self
    {
        if ($value !== null && mb_strlen((string) $value) < $length) {
            $this->errors[$field][] = $message ?? "The {$field} field must be at least {$length} characters.";
        }

        return $this;
    }

    public function confirm(string $field, mixed $value, mixed $confirmValue, string $message = null): self
    {
        if ($value !== $confirmValue) {
            $this->errors[$field][] = $message ?? "The {$field} confirmation does not match.";
        }

        return $this;
    }

    public function regex(string $field, mixed $value, string $pattern, string $message): self
    {
        if ($value !== null && $value !== '' && !preg_match($pattern, (string) $value)) {
            $this->errors[$field][] = $message;
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
}