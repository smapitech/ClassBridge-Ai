<?php

namespace App\Enums;

enum LiveLessonMode: string
{
    case Whiteboard = 'whiteboard';
    case Coding = 'coding';
    case Text = 'text';
    case Mathematics = 'mathematics';
    case Presentation = 'presentation';

    public static function normalize(?string $mode): string
    {
        $value = strtolower(trim((string) $mode));

        return match ($value) {
            'english', 'text', 'text/english', 'text-english' => self::Text->value,
            'math', 'maths', 'mathematics' => self::Mathematics->value,
            'coding', 'code' => self::Coding->value,
            'presentation', 'slides' => self::Presentation->value,
            default => in_array($value, self::canonicalValues(), true)
                ? $value
                : self::Whiteboard->value,
        };
    }

    public static function canonicalValues(): array
    {
        return array_map(static fn (self $mode) => $mode->value, self::cases());
    }

    public static function acceptedValues(): array
    {
        return array_values(array_unique(array_merge(self::canonicalValues(), [
            'english',
            'math',
            'maths',
            'text/english',
            'text-english',
            'code',
            'slides',
        ])));
    }

    public static function label(string $mode): string
    {
        return match (self::normalize($mode)) {
            self::Whiteboard->value => 'Whiteboard',
            self::Coding->value => 'Coding Studio',
            self::Text->value => 'Text / English',
            self::Mathematics->value => 'Mathematics',
            self::Presentation->value => 'Presentation',
            default => ucfirst($mode),
        };
    }
}
