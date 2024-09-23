<?php

namespace ComposableQueryBuilder\Utils;

use Illuminate\Support\Str;

class FullTextSearch
{
    public static function normalizeFullTextStatement(string $typeResolver, string $value): string
    {
        $typeResolver    = str_replace('fulltext:', '', $typeResolver);
        $fullTextColumns = explode(',', trim($typeResolver));

        return self::getMatchAgainstStatement($fullTextColumns, $value);
    }

    private static function getMatchAgainstStatement($columns, $term): string
    {
        $columns = implode(',', Normalizer::array($columns));

        return sprintf("MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE)",
            $columns,
            self::fullTextWildcards($term)
        );
    }

    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    private static function fullTextWildcards($term): string
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term            = str_replace($reservedSymbols, '', (string)Str::of($term)->trim());

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 2) {
                $words[$key] = '+' . $word . '*';
            }
        }

        return implode(' ', $words);
    }
}