<?php

namespace Fisharebest\Localization\Territory;

/**
 * Class AbstractTerritory - Representation of the territory IE - Ireland.
 *
 * @author    Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2018 Greg Roach
 * @license   GPLv3+
 */
class TerritoryIe extends AbstractTerritory implements TerritoryInterface
{
    public function code()
    {
        return 'IE';
    }

    public function firstDay()
    {
        return 0;
    }
}
