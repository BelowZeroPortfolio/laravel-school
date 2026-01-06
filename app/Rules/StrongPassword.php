<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Minimum password length.
     */
    protected int $minLength = 8;

    /**
     * Whether to require uppercase letters.
     */
    protected bool $requireUppercase = true;

    /**
     * Whether to require lowercase letters.
     */
    protected bool $requireLowercase = true;

    /**
     * Whether to require numbers.
     */
    protected bool $requireNumbers = true;

    /**
     * Whether to require special characters.
     */
    protected bool $requireSpecial = false;

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < $this->minLength) {
            $fail("The {$attribute} must be at least {$this->minLength} characters.");
            return;
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $fail("The {$attribute} must contain at least one uppercase letter.");
            return;
        }

        if ($this->requireLowercase && !preg_match('/[a-z]/', $value)) {
            $fail("The {$attribute} must contain at least one lowercase letter.");
            return;
        }

        if ($this->requireNumbers && !preg_match('/[0-9]/', $value)) {
            $fail("The {$attribute} must contain at least one number.");
            return;
        }

        if ($this->requireSpecial && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            $fail("The {$attribute} must contain at least one special character.");
            return;
        }

        // Check for common weak passwords
        $weakPasswords = [
            'password', 'password123', '12345678', 'qwerty123',
            'admin123', 'letmein', 'welcome', 'monkey123',
        ];

        if (in_array(strtolower($value), $weakPasswords)) {
            $fail("The {$attribute} is too common. Please choose a stronger password.");
            return;
        }
    }

    /**
     * Set minimum length.
     */
    public function min(int $length): self
    {
        $this->minLength = $length;
        return $this;
    }

    /**
     * Require special characters.
     */
    public function withSpecialCharacters(): self
    {
        $this->requireSpecial = true;
        return $this;
    }
}
