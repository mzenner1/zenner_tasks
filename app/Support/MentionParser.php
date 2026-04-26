<?php

namespace App\Support;

class MentionParser
{
    /**
     * Extract all user IDs from @[Name](user:ID) mention syntax in a markdown string.
     *
     * @return string[]
     */
    public static function extractIds(string $markdown): array
    {
        preg_match_all('/@\[[^\]]+\]\(user:([^)]+)\)/', $markdown, $matches);

        return array_values(array_unique($matches[1]));
    }
}
