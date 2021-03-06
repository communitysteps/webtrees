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

namespace Fisharebest\Webtrees\Services;

use Closure;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AhnentafelReportModule;
use Fisharebest\Webtrees\Module\AlbumModule;
use Fisharebest\Webtrees\Module\AncestorsChartModule;
use Fisharebest\Webtrees\Module\BatchUpdateModule;
use Fisharebest\Webtrees\Module\BingWebmasterToolsModule;
use Fisharebest\Webtrees\Module\BirthDeathMarriageReportModule;
use Fisharebest\Webtrees\Module\BirthReportModule;
use Fisharebest\Webtrees\Module\BranchesListModule;
use Fisharebest\Webtrees\Module\CalendarMenuModule;
use Fisharebest\Webtrees\Module\CemeteryReportModule;
use Fisharebest\Webtrees\Module\CensusAssistantModule;
use Fisharebest\Webtrees\Module\ChangeReportModule;
use Fisharebest\Webtrees\Module\ChartsBlockModule;
use Fisharebest\Webtrees\Module\ChartsMenuModule;
use Fisharebest\Webtrees\Module\CkeditorModule;
use Fisharebest\Webtrees\Module\ClippingsCartModule;
use Fisharebest\Webtrees\Module\CloudsTheme;
use Fisharebest\Webtrees\Module\ColorsTheme;
use Fisharebest\Webtrees\Module\CompactTreeChartModule;
use Fisharebest\Webtrees\Module\ContactsFooterModule;
use Fisharebest\Webtrees\Module\CookieWarningModule;
use Fisharebest\Webtrees\Module\DeathReportModule;
use Fisharebest\Webtrees\Module\DescendancyChartModule;
use Fisharebest\Webtrees\Module\DescendancyModule;
use Fisharebest\Webtrees\Module\DescendancyReportModule;
use Fisharebest\Webtrees\Module\IndividualMetadataModule;
use Fisharebest\Webtrees\Module\FabTheme;
use Fisharebest\Webtrees\Module\FactSourcesReportModule;
use Fisharebest\Webtrees\Module\FamilyBookChartModule;
use Fisharebest\Webtrees\Module\FamilyGroupReportModule;
use Fisharebest\Webtrees\Module\FamilyListModule;
use Fisharebest\Webtrees\Module\FamilyNavigatorModule;
use Fisharebest\Webtrees\Module\FamilyTreeFavoritesModule;
use Fisharebest\Webtrees\Module\FamilyTreeNewsModule;
use Fisharebest\Webtrees\Module\FamilyTreeStatisticsModule;
use Fisharebest\Webtrees\Module\FanChartModule;
use Fisharebest\Webtrees\Module\FrequentlyAskedQuestionsModule;
use Fisharebest\Webtrees\Module\GoogleAnalyticsModule;
use Fisharebest\Webtrees\Module\GoogleWebmasterToolsModule;
use Fisharebest\Webtrees\Module\HitCountFooterModule;
use Fisharebest\Webtrees\Module\HourglassChartModule;
use Fisharebest\Webtrees\Module\HtmlBlockModule;
use Fisharebest\Webtrees\Module\IndividualFactsTabModule;
use Fisharebest\Webtrees\Module\IndividualFamiliesReportModule;
use Fisharebest\Webtrees\Module\IndividualListModule;
use Fisharebest\Webtrees\Module\IndividualReportModule;
use Fisharebest\Webtrees\Module\InteractiveTreeModule;
use Fisharebest\Webtrees\Module\LifespansChartModule;
use Fisharebest\Webtrees\Module\ListsMenuModule;
use Fisharebest\Webtrees\Module\LoggedInUsersModule;
use Fisharebest\Webtrees\Module\LoginBlockModule;
use Fisharebest\Webtrees\Module\MarriageReportModule;
use Fisharebest\Webtrees\Module\MatomoAnalyticsModule;
use Fisharebest\Webtrees\Module\MediaListModule;
use Fisharebest\Webtrees\Module\MediaTabModule;
use Fisharebest\Webtrees\Module\MinimalTheme;
use Fisharebest\Webtrees\Module\MissingFactsReportModule;
use Fisharebest\Webtrees\Module\ModuleAnalyticsInterface;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleFooterInterface;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Module\ModuleLanguageInterface;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Module\ModuleReportInterface;
use Fisharebest\Webtrees\Module\ModuleSidebarInterface;
use Fisharebest\Webtrees\Module\ModuleTabInterface;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Module\NoteListModule;
use Fisharebest\Webtrees\Module\NotesTabModule;
use Fisharebest\Webtrees\Module\OccupationReportModule;
use Fisharebest\Webtrees\Module\OnThisDayModule;
use Fisharebest\Webtrees\Module\PedigreeChartModule;
use Fisharebest\Webtrees\Module\PedigreeMapModule;
use Fisharebest\Webtrees\Module\PedigreeReportModule;
use Fisharebest\Webtrees\Module\PlaceHierarchyListModule;
use Fisharebest\Webtrees\Module\PlacesModule;
use Fisharebest\Webtrees\Module\PoweredByWebtreesModule;
use Fisharebest\Webtrees\Module\RecentChangesModule;
use Fisharebest\Webtrees\Module\RelatedIndividualsReportModule;
use Fisharebest\Webtrees\Module\RelationshipsChartModule;
use Fisharebest\Webtrees\Module\RelativesTabModule;
use Fisharebest\Webtrees\Module\ReportsMenuModule;
use Fisharebest\Webtrees\Module\RepositoryListModule;
use Fisharebest\Webtrees\Module\ResearchTaskModule;
use Fisharebest\Webtrees\Module\ReviewChangesModule;
use Fisharebest\Webtrees\Module\SearchMenuModule;
use Fisharebest\Webtrees\Module\SiteMapModule;
use Fisharebest\Webtrees\Module\SlideShowModule;
use Fisharebest\Webtrees\Module\SourceListModule;
use Fisharebest\Webtrees\Module\SourcesTabModule;
use Fisharebest\Webtrees\Module\StatcounterModule;
use Fisharebest\Webtrees\Module\StatisticsChartModule;
use Fisharebest\Webtrees\Module\StoriesModule;
use Fisharebest\Webtrees\Module\ThemeSelectModule;
use Fisharebest\Webtrees\Module\TimelineChartModule;
use Fisharebest\Webtrees\Module\TopGivenNamesModule;
use Fisharebest\Webtrees\Module\TopPageViewsModule;
use Fisharebest\Webtrees\Module\TopSurnamesModule;
use Fisharebest\Webtrees\Module\TreesMenuModule;
use Fisharebest\Webtrees\Module\UpcomingAnniversariesModule;
use Fisharebest\Webtrees\Module\UserFavoritesModule;
use Fisharebest\Webtrees\Module\UserJournalModule;
use Fisharebest\Webtrees\Module\UserMessagesModule;
use Fisharebest\Webtrees\Module\UserWelcomeModule;
use Fisharebest\Webtrees\Module\WebtreesTheme;
use Fisharebest\Webtrees\Module\WelcomeBlockModule;
use Fisharebest\Webtrees\Module\XeneaTheme;
use Fisharebest\Webtrees\Module\YahrzeitModule;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Webtrees;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use stdClass;
use Throwable;

/**
 * Functions for managing and maintaining modules.
 */
class ModuleService
{
    // Components are managed together in the control panel.
    private const COMPONENTS = [
        ModuleAnalyticsInterface::class,
        ModuleBlockInterface::class,
        ModuleChartInterface::class,
        ModuleFooterInterface::class,
        ModuleHistoricEventsInterface::class,
        ModuleLanguageInterface::class,
        ModuleListInterface::class,
        ModuleMenuInterface::class,
        ModuleReportInterface::class,
        ModuleSidebarInterface::class,
        ModuleTabInterface::class,
        ModuleThemeInterface::class,
    ];

    // Array keys are module names, and should match module names from earlier versions of webtrees.
    private const CORE_MODULES = [
        'GEDFact_assistant'      => CensusAssistantModule::class,
        'ahnentafel_report'      => AhnentafelReportModule::class,
        'ancestors_chart'        => AncestorsChartModule::class,
        'batch_update'           => BatchUpdateModule::class,
        'bdm_report'             => BirthDeathMarriageReportModule::class,
        'bing-webmaster-tools'   => BingWebmasterToolsModule::class,
        'birth_report'           => BirthReportModule::class,
        'branches_list'          => BranchesListModule::class,
        'calendar-menu'          => CalendarMenuModule::class,
        'cemetery_report'        => CemeteryReportModule::class,
        'change_report'          => ChangeReportModule::class,
        'charts'                 => ChartsBlockModule::class,
        'charts-menu'            => ChartsMenuModule::class,
        'ckeditor'               => CkeditorModule::class,
        'clippings'              => ClippingsCartModule::class,
        'clouds'                 => CloudsTheme::class,
        'colors'                 => ColorsTheme::class,
        'compact-chart'          => CompactTreeChartModule::class,
        'contact-links'          => ContactsFooterModule::class,
        'cookie-warning'         => CookieWarningModule::class,
        'death_report'           => DeathReportModule::class,
        'descendancy'            => DescendancyModule::class,
        'descendancy_chart'      => DescendancyChartModule::class,
        'descendancy_report'     => DescendancyReportModule::class,
        'extra_info'             => IndividualMetadataModule::class,
        'fab'                    => FabTheme::class,
        'fact_sources'           => FactSourcesReportModule::class,
        'family_book_chart'      => FamilyBookChartModule::class,
        'family_group_report'    => FamilyGroupReportModule::class,
        'family_list'            => FamilyListModule::class,
        'family_nav'             => FamilyNavigatorModule::class,
        'fan_chart'              => FanChartModule::class,
        'faq'                    => FrequentlyAskedQuestionsModule::class,
        'gedcom_block'           => WelcomeBlockModule::class,
        'gedcom_favorites'       => FamilyTreeFavoritesModule::class,
        'gedcom_news'            => FamilyTreeNewsModule::class,
        'gedcom_stats'           => FamilyTreeStatisticsModule::class,
        'google-analytics'       => GoogleAnalyticsModule::class,
        'google-webmaster-tools' => GoogleWebmasterToolsModule::class,
        'hit-counter'            => HitCountFooterModule::class,
        'hourglass_chart'        => HourglassChartModule::class,
        'html'                   => HtmlBlockModule::class,
        'individual_ext_report'  => IndividualFamiliesReportModule::class,
        'individual_list'        => IndividualListModule::class,
        'individual_report'      => IndividualReportModule::class,
        'lifespans_chart'        => LifespansChartModule::class,
        'lightbox'               => AlbumModule::class,
        'lists-menu'             => ListsMenuModule::class,
        'logged_in'              => LoggedInUsersModule::class,
        'login_block'            => LoginBlockModule::class,
        'marriage_report'        => MarriageReportModule::class,
        'matomo-analytics'       => MatomoAnalyticsModule::class,
        'media'                  => MediaTabModule::class,
        'media_list'             => MediaListModule::class,
        'minimal'                => MinimalTheme::class,
        'missing_facts_report'   => MissingFactsReportModule::class,
        'notes'                  => NotesTabModule::class,
        'note_list'              => NoteListModule::class,
        'occupation_report'      => OccupationReportModule::class,
        'pedigree-map'           => PedigreeMapModule::class,
        'pedigree_chart'         => PedigreeChartModule::class,
        'pedigree_report'        => PedigreeReportModule::class,
        'personal_facts'         => IndividualFactsTabModule::class,
        'places'                 => PlacesModule::class,
        'places_list'            => PlaceHierarchyListModule::class,
        'powered-by-webtrees'    => PoweredByWebtreesModule::class,
        'random_media'           => SlideShowModule::class,
        'recent_changes'         => RecentChangesModule::class,
        'relationships_chart'    => RelationshipsChartModule::class,
        'relative_ext_report'    => RelatedIndividualsReportModule::class,
        'relatives'              => RelativesTabModule::class,
        'reports-menu'           => ReportsMenuModule::class,
        'repository_list'        => RepositoryListModule::class,
        'review_changes'         => ReviewChangesModule::class,
        'search-menu'            => SearchMenuModule::class,
        'sitemap'                => SiteMapModule::class,
        'source_list'            => SourceListModule::class,
        'sources_tab'            => SourcesTabModule::class,
        'statcounter'            => StatcounterModule::class,
        'statistics_chart'       => StatisticsChartModule::class,
        'stories'                => StoriesModule::class,
        'theme_select'           => ThemeSelectModule::class,
        'timeline_chart'         => TimelineChartModule::class,
        'todays_events'          => OnThisDayModule::class,
        'todo'                   => ResearchTaskModule::class,
        'top10_givnnames'        => TopGivenNamesModule::class,
        'top10_pageviews'        => TopPageViewsModule::class,
        'top10_surnames'         => TopSurnamesModule::class,
        'tree'                   => InteractiveTreeModule::class,
        'trees-menu'             => TreesMenuModule::class,
        'upcoming_events'        => UpcomingAnniversariesModule::class,
        'user_blog'              => UserJournalModule::class,
        'user_favorites'         => UserFavoritesModule::class,
        'user_messages'          => UserMessagesModule::class,
        'user_welcome'           => UserWelcomeModule::class,
        'webtrees'               => WebtreesTheme::class,
        'xenea'                  => XeneaTheme::class,
        'yahrzeit'               => YahrzeitModule::class,
    ];

    /**
     * All core modules in the system.
     *
     * @return Collection
     */
    private function coreModules(): Collection
    {
        $modules = new Collection(self::CORE_MODULES);

        return $modules->map(function (string $class, string $name): ModuleInterface {
            $module = app()->make($class);

            $module->setName($name);

            return $module;
        });
    }

    /**
     * All custom modules in the system.  Custom modules are defined in modules_v4/
     *
     * @return Collection
     */
    private function customModules(): Collection
    {
        $pattern   = WT_ROOT . Webtrees::MODULES_PATH . '*/module.php';
        $filenames = glob($pattern);

        return (new Collection($filenames))
            ->filter(function (string $filename): bool {
                // Special characters will break PHP variable names.
                // This also allows us to ignore modules called "foo.example" and "foo.disable"
                $module_name = basename(dirname($filename));

                return !Str::contains($module_name, ['.', ' ', '[', ']']) && Str::length($module_name) <= 30;
            })
            ->map(function (string $filename): ?ModuleCustomInterface {
                try {
                    $module = self::load($filename);

                    if ($module instanceof ModuleCustomInterface) {
                        $module_name = '_' . basename(dirname($filename)) . '_';

                        $module->setName($module_name);
                    } else {
                        return null;
                    }

                    return $module;
                } catch (Throwable $ex) {
                    $message = '<pre>' . e($ex->getMessage()) . "\n" . e($ex->getTraceAsString()) . '</pre>';
                    FlashMessages::addMessage($message, 'danger');

                    return null;
                }
            })
            ->filter();
    }

    /**
     * All modules.
     *
     * @return Collection|ModuleInterface[]
     */
    public function all(): Collection
    {
        return app('cache.array')->rememberForever('all_modules', function (): Collection {
            // Modules have a default status, order etc.
            // We can override these from database settings.
            $module_info = DB::table('module')
                ->get()
                ->mapWithKeys(function (stdClass $row): array {
                    return [$row->module_name => $row];
                });

            return $this->coreModules()
                ->merge($this->customModules())
                ->map(function (ModuleInterface $module) use ($module_info): ModuleInterface {
                    $info = $module_info->get($module->name());

                    if ($info instanceof stdClass) {
                        $module->setEnabled($info->status === 'enabled');

                        if ($module instanceof ModuleFooterInterface && $info->footer_order !== null) {
                            $module->setFooterOrder((int) $info->footer_order);
                        }

                        if ($module instanceof ModuleMenuInterface && $info->menu_order !== null) {
                            $module->setMenuOrder((int) $info->menu_order);
                        }

                        if ($module instanceof ModuleSidebarInterface && $info->sidebar_order !== null) {
                            $module->setSidebarOrder((int) $info->sidebar_order);
                        }

                        if ($module instanceof ModuleTabInterface && $info->tab_order !== null) {
                            $module->setTabOrder((int) $info->tab_order);
                        }
                    } else {
                        $module->setEnabled($module->isEnabledByDefault());

                        DB::table('module')->insert([
                            'module_name' => $module->name(),
                            'status'      => $module->isEnabled() ? 'enabled' : 'disabled',
                        ]);
                    }

                    return $module;
                })
                ->sort($this->moduleSorter());
        });
    }

    /**
     * Load a module in a static scope, to prevent it from modifying local or object variables.
     *
     * @param string $filename
     *
     * @return mixed
     */
    private static function load(string $filename)
    {
        return include $filename;
    }

    /**
     * A function to sort modules by name
     *
     * @return Closure
     */
    private function moduleSorter(): Closure
    {
        return function (ModuleInterface $x, ModuleInterface $y): int {
            return I18N::strcasecmp($x->title(), $y->title());
        };
    }

    /**
     * A function to sort footers
     *
     * @return Closure
     */
    private function footerSorter(): Closure
    {
        return function (ModuleFooterInterface $x, ModuleFooterInterface $y): int {
            return $x->getFooterOrder() <=> $y->getFooterOrder();
        };
    }

    /**
     * A function to sort menus
     *
     * @return Closure
     */
    private function menuSorter(): Closure
    {
        return function (ModuleMenuInterface $x, ModuleMenuInterface $y): int {
            return $x->getMenuOrder() <=> $y->getMenuOrder();
        };
    }

    /**
     * A function to sort menus
     *
     * @return Closure
     */
    private function sidebarSorter(): Closure
    {
        return function (ModuleSidebarInterface $x, ModuleSidebarInterface $y): int {
            return $x->getSidebarOrder() <=> $y->getSidebarOrder();
        };
    }

    /**
     * A function to sort menus
     *
     * @return Closure
     */
    private function tabSorter(): Closure
    {
        return function (ModuleTabInterface $x, ModuleTabInterface $y): int {
            return $x->getTabOrder() <=> $y->getTabOrder();
        };
    }

    /**
     * A function to convert modules into their titles - to create option lists, etc.
     *
     * @return Closure
     */
    public function titleMapper(): Closure
    {
        return function (ModuleInterface $module): string {
            return $module->title();
        };
    }

    /**
     * Modules which (a) provide a specific function and (b) we have permission to see.
     *
     * @param string        $interface
     * @param Tree          $tree
     * @param UserInterface $user
     *
     * @return Collection|ModuleInterface[]
     */
    public function findByComponent(string $interface, Tree $tree, UserInterface $user): Collection
    {
        return $this->findByInterface($interface)
            ->filter(function (ModuleInterface $module) use ($interface, $tree, $user): bool {
                return $module->accessLevel($tree, $interface) >= Auth::accessLevel($tree, $user);
            });
    }

    /**
     * All modules which provide a specific function.
     *
     * @param string $interface
     * @param bool   $include_disabled
     *
     * @return Collection|ModuleInterface[]
     */
    public function findByInterface(string $interface, $include_disabled = false): Collection
    {
        $modules = $this->all()
            ->filter(function (ModuleInterface $module) use ($interface): bool {
                return $module instanceof $interface;
            })
            ->filter(function (ModuleInterface $module) use ($include_disabled): bool {
                return $include_disabled || $module->isEnabled();
            });

        switch ($interface) {
            case ModuleFooterInterface::class:
                return $modules->sort($this->footerSorter());

            case ModuleMenuInterface::class:
                return $modules->sort($this->menuSorter());

            case ModuleSidebarInterface::class:
                return $modules->sort($this->sidebarSorter());

            case ModuleTabInterface::class:
                return $modules->sort($this->tabSorter());
        }

        return $modules;
    }

    /**
     * Find a specified module, if it is currently active.
     *
     * @param string $module_name
     *
     * @return ModuleInterface|null
     */
    public function findByName(string $module_name): ?ModuleInterface
    {
        return $this->all()
            ->filter(function (ModuleInterface $module) use ($module_name): bool {
                return $module->isEnabled() && $module->name() === $module_name;
            })
            ->first();
    }

    /**
     * Find a specified module, if it is currently active.
     *
     * @param string $class_name
     *
     * @return ModuleInterface|null
     */
    public function findByClass(string $class_name): ?ModuleInterface
    {
        return $this->all()
            ->filter(function (ModuleInterface $module) use ($class_name): bool {
                return $module->isEnabled() && $module instanceof $class_name;
            })
            ->first();
    }

    /**
     * Configuration settings are available through the various "module component" pages.
     * For modules that do not provide a component, we need to list them separately.
     *
     * @return Collection|ModuleConfigInterface[]
     */
    public function configOnlyModules(): Collection
    {
        return $this->findByInterface(ModuleConfigInterface::class)
            ->filter(function (ModuleConfigInterface $module): bool {
                foreach (self::COMPONENTS as $interface) {
                    if ($module instanceof $interface) {
                        return false;
                    }
                }

                return true;
            });
    }

    /**
     * Generate a list of module names which exist in the database but not on disk.
     *
     * @return Collection|string[]
     */
    public function deletedModules(): Collection
    {
        $database_modules = DB::table('module')->pluck('module_name');

        $disk_modules = $this->all()
            ->map(function (ModuleInterface $module): string {
                return $module->name();
            });

        return $database_modules->diff($disk_modules);
    }
}
