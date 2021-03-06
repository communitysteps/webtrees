<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2019 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Fisharebest\Webtrees\Http\Controllers\Admin;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Site;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for maps and geographic data.
 */
class MapProviderController extends AbstractAdminController
{
    /**
     * @return Response
     */
    public function mapProviderEdit(): Response
    {
        return $this->viewResponse('admin/map-provider', [
            'title'    => I18N::translate('Map provider'),
            'provider' => Site::getPreference('map-provider'),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function mapProviderSave(Request $request): RedirectResponse
    {
        $map_provider = $request->get('provider', '');

        Site::setPreference('map-provider', $map_provider);

        return new RedirectResponse(route('admin-control-panel'));
    }
}
