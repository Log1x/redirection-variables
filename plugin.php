<?php

/**
 * Plugin Name: Redirection Variables
 * Plugin URI:  https://github.com/log1x/redirection-variables
 * Description: Provides referer tracking variables for popular WordPress redirection plugins.
 * Version:     1.0.4
 * Author:      Brandon Nifong
 * Author URI:  https://github.com/log1x
 * Licence:     MIT
 */

namespace Log1x\Plugin\RedirectionVariables;

if (file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    require_once $composer;
}

add_action('after_setup_theme', new class
{
    /**
     * Invoke the plugin.
     *
     * @return void
     */
    public function __invoke()
    {
        return new RedirectionVariables(
            plugin_dir_path(__FILE__),
            plugin_dir_url(__FILE__)
        );
    }
});
