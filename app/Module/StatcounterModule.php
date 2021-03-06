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

namespace Fisharebest\Webtrees\Module;

/**
 * Class StatcounterModule - add support for statcounter.
 */
class StatcounterModule extends AbstractModule implements ModuleAnalyticsInterface, ModuleConfigInterface, ModuleExternalUrlInterface
{
    use ModuleAnalyticsTrait;
    use ModuleConfigTrait;
    use ModuleExternalUrlTrait;

    /**
     * How should this module be labelled on tabs, menus, etc.?
     *
     * @return string
     */
    public function title(): string
    {
        return 'Statcounter™';
    }

    /**
     * Form fields to edit the parameters.
     *
     * @return string
     */
    public function analyticsFormFields(): string
    {
        return view('modules/statcounter/form', $this->analyticsParameters());
    }

    /**
     * Home page for the service.
     *
     * @return string
     */
    public function externalUrl(): string
    {
        return 'https://statcounter.com';
    }

    /**
     * The parameters that need to be embedded in the snippet.
     *
     * @return string[]
     */
    public function analyticsParameters(): array
    {
        return [
            'STATCOUNTER_PROJECT_ID' => $this->getPreference('STATCOUNTER_PROJECT_ID'),
            'STATCOUNTER_SECURITY_ID' => $this->getPreference('STATCOUNTER_SECURITY_ID'),
        ];
    }

    /**
     * Embed placeholders in the snippet.
     *
     * @param string[] $parameters
     *
     * @return string
     */
    public function analyticsSnippet(array $parameters): string
    {
        return view('modules/statcounter/snippet', $parameters);
    }
}
