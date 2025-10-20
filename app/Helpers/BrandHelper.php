<?php

namespace App\Helpers;

use App\Models\BrandGroup;

class BrandHelper
{
    protected static $brandMap = null;

    public static function normalize($brandName)
    {
        if (!$brandName) return null;

        if (self::$brandMap === null) {
            self::$brandMap = [];
            foreach (BrandGroup::all() as $group) {
                foreach ($group->aliases ?? [] as $alias) {
                    self::$brandMap[mb_strtolower($alias)] = $group->display_name;
                }
                // Include the normalized name itself as an alias
                self::$brandMap[mb_strtolower($group->display_name)] = $group->display_name;
            }
        }

        return self::$brandMap[mb_strtolower($brandName)] ?? $brandName;
    }
}
