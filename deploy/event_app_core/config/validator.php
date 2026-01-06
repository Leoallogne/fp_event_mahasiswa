<?php

class Validator {
    
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateRequired($value) {
        return !empty(trim($value));
    }
    
    public static function validateLength($value, $min = 0, $max = null) {
        $length = mb_strlen($value);
        if ($length < $min) return false;
        if ($max !== null && $length > $max) return false;
        return true;
    }
    
    public static function validateInt($value, $min = null, $max = null) {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if ($int === false) return false;
        if ($min !== null && $int < $min) return false;
        if ($max !== null && $int > $max) return false;
        return true;
    }
    
    public static function validateDateTime($datetime) {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $d && $d->format('Y-m-d H:i:s') === $datetime;
    }
}

