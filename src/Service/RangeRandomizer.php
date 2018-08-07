<?php

namespace App\Service;

class RangeRandomizer
{
    CONST MIN_VALUE = 1;
    CONST MAX_VALUE = 65535;

    private $ranges = array();

    public function getRanges(int $tranches = 10, bool $randomize = true) : ?array
    {
        $maxSubrange = ceil(SELF::MAX_VALUE / $tranches); // standard: 65535 / 10 = 6553.5 -> 6554

        $i = 1;

        while($i <= $tranches)
        {
            if (!isset($minTranche)) $minTranche = SELF::MIN_VALUE;

            if ($i < $tranches)
            {
                array_push($this->ranges, array(
                    'min'   => $minTranche,
                    'max'   => $minTranche + $maxSubrange - 1,
                    'count' => $maxSubrange
                ));

            }else{

                array_push($this->ranges, array(
                    'min'   => $minTranche,
                    'max'   => SELF::MAX_VALUE,
                    'count' => SELF::MAX_VALUE - $minTranche
                ));
            }

            $minTranche = $minTranche + $maxSubrange;

            $i++;
        }

        if ($randomize) shuffle($this->ranges);

        return $this->ranges;
    }
}