<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Requires:
     * - Minimum 12 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
     * - At least one special character
     * - No common weak passwords
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 12) {
            $fail('The :attribute must be at least 12 characters.');
            return;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');
            return;
        }

        if (! preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');
            return;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');
            return;
        }

        if (! preg_match('/[@$!%*#?&]/', $value)) {
            $fail('The :attribute must contain at least one special character (@$!%*#?&).');
            return;
        }

        // Check against common weak passwords
        $commonPasswords = [
            'password123',
            'Password123',
            'Password123!',
            'Admin123!',
            'Welcome123!',
            'Qwerty123!',
            'Letmein123!',
        ];

        if (in_array($value, $commonPasswords)) {
            $fail('The :attribute is too common. Please choose a more unique password.');
            return;
        }

        // Check for repeating characters (e.g., "aaaa", "1111")
        if (preg_match('/(.)\1{3,}/', $value)) {
            $fail('The :attribute cannot contain more than 3 repeating characters.');
            return;
        }

        // Check for sequential characters (e.g., "1234", "abcd")
        $sequences = ['0123456789', 'abcdefghijklmnopqrstuvwxyz', 'qwertyuiop', 'asdfghjkl', 'zxcvbnm'];
        foreach ($sequences as $sequence) {
            if (stripos($value, substr($sequence, 0, 4)) !== false ||
                stripos($value, strrev(substr($sequence, 0, 4))) !== false) {
                $fail('The :attribute cannot contain sequential characters.');
                return;
            }
        }
    }

    /**
     * Get password strength score (0-100)
     */
    public static function getStrength(string $password): int
    {
        $strength = 0;

        // Length score (max 40 points)
        $length = strlen($password);
        if ($length >= 12) {
            $strength += 20;
        }
        if ($length >= 16) {
            $strength += 10;
        }
        if ($length >= 20) {
            $strength += 10;
        }

        // Character variety (max 40 points)
        if (preg_match('/[a-z]/', $password)) {
            $strength += 10;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $strength += 10;
        }
        if (preg_match('/[0-9]/', $password)) {
            $strength += 10;
        }
        if (preg_match('/[@$!%*#?&]/', $password)) {
            $strength += 10;
        }

        // Complexity bonus (max 20 points)
        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars >= 8) {
            $strength += 10;
        }
        if ($uniqueChars >= 12) {
            $strength += 10;
        }

        return min(100, $strength);
    }

    /**
     * Get strength label
     */
    public static function getStrengthLabel(int $strength): string
    {
        return match (true) {
            $strength >= 80 => 'Very Strong',
            $strength >= 60 => 'Strong',
            $strength >= 40 => 'Medium',
            $strength >= 20 => 'Weak',
            default => 'Very Weak',
        };
    }

    /**
     * Get strength color
     */
    public static function getStrengthColor(int $strength): string
    {
        return match (true) {
            $strength >= 80 => 'green',
            $strength >= 60 => 'blue',
            $strength >= 40 => 'yellow',
            $strength >= 20 => 'orange',
            default => 'red',
        };
    }
}
