<?php

namespace BIT\DhvJugend;

use BIT\DhvJugend\Event\RegistrationForm;

/**
 * @author Christoph Bessei
 */
class Bootstrap
{
    /**
     * @var string
     */
    protected $pluginDirUrl;

    /**
     * @var string
     */
    protected $pluginDirPath;

    public function run()
    {
        $baseFile = trailingslashit(dirname(__DIR__)) . '/dhv-jugend.php';
        $this->pluginDirUrl = plugin_dir_url($baseFile);
        $this->pluginDirPath = plugin_dir_path($baseFile);

        $this->enqueueAssets();
        RegistrationForm::registerHooks();
    }

    protected function enqueueAssets()
    {
        add_action(
            'wp_enqueue_scripts',
            function () {
                wp_enqueue_style(
                    'style1',
                    $this->pluginDirUrl . 'assets/dist/css/header.css',
                    [],
                    md5_file($this->pluginDirPath . 'assets/dist/css/header.css')
                );
            }
        );
    }
}
