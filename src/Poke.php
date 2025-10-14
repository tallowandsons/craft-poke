<?php

namespace tallowandsons\poke;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\SectionEvent;
use craft\services\Entries;
use craft\services\Plugins;
use tallowandsons\craftpermissionreminder\models\Settings;
use yii\base\Event;

/**
 * Poke plugin
 *
 * @method static Poke getInstance()
 * @method Settings getSettings()
 * @author tallowandsons <support@tallowandsons.com>
 * @copyright tallowandsons
 * @license https://craftcms.github.io/license/ Craft License
 */
class Poke extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function () {
            // ...
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('poke/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            Entries::class,
            Entries::EVENT_AFTER_SAVE_SECTION,
            function (SectionEvent $event) {
                if ($event->isNew) {
                    Craft::$app->getSession()->setNotice(
                        Craft::t(
                            'poke',
                            'Remember to update user permissions for the new "{section}" section in Settings → Users',
                            ['section' => $event->section->name]
                        )
                    );
                }
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                Craft::$app->getSession()->setNotice(
                    Craft::t(
                        'poke',
                        'Remember to update user permissions for the "{plugin}" plugin in Settings → Users',
                        ['plugin' => $event->plugin->name]
                    )
                );
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_ENABLE_PLUGIN,
            function (PluginEvent $event) {
                Craft::$app->getSession()->setNotice(
                    Craft::t(
                        'poke',
                        'Remember to update user permissions for the "{plugin}" plugin in Settings → Users',
                        ['plugin' => $event->plugin->name]
                    )
                );
            }
        );
    }
}
