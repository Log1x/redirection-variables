<?php

namespace Log1x\Plugin\RedirectionVariables;

use Snowplow\RefererParser\Parser;
use Tightenco\Collect\Support\Collection;
use Tightenco\Collect\Support\Arr;

class RedirectionVariables
{
    /**
     * The plugin directory path.
     *
     * @var string
     */
    protected $path;

    /**
     * The plugin directory URI.
     *
     * @var string
     */
    protected $uri;

    /**
     * The RefererParser instance.
     *
     * @var \Snowplow\RefererParser\Parser;
     */
    protected $parser;

    /**
     * The current request URI.
     *
     * @var string
     */
    protected $request;

    /**
     * The known referer URI.
     *
     * @var string
     */
    protected $referer;

    /**
     * The known post ID.
     *
     * @var int
     */
    protected $post;

    /**
     * The available replacement variables.
     *
     * @var array
     */
    protected $variables = [
        '%source%' => 'utm_source',
        '%medium%' => 'utm_medium',
        '%campaign%' => 'utm_campaign',
        '%term%' => 'utm_term',
        '%content%' => 'utm_content',
        '%adgroup%' => 'utm_adgroup',
        '%search_term%' => 'search_term',
        '%gclid%' => 'gclid',
        '%post_id%' => 'post_id',
        '%post_title%' => 'post_title',
        '%post_name%' => 'post_name',
    ];

    /**
     * Initialize the plugin.
     *
     * @param  string $path
     * @param  string $uri
     * @return void
     */
    public function __construct($path, $uri)
    {
        $this->path = $path;
        $this->uri = $uri;

        if (
            ! $this->hasPrettyLinks() &&
            ! $this->hasSafeRedirectManager() &&
            ! $this->hasRedirection()
        ) {
            return;
        }

        $this->request = Arr::get($_SERVER, 'REQUEST_URI');
        $this->referer = wp_get_referer() ?: Arr::get($_SERVER, 'HTTP_REFERER');

        $this->parser = (new Parser())->parse(
            $this->referer,
            $this->request
        );

        if ($this->hasRedirection()) {
            $this->handleRedirection();
        }

        if ($this->hasSafeRedirectManager()) {
            $this->handleSafeRedirectManager();
        }

        if ($this->hasPrettyLinks()) {
            $this->handlePrettyLinks();
        }
    }

    /**
     * Determine if Redirection is installed.
     *
     * @return bool
     */
    public function hasRedirection()
    {
        return defined('REDIRECTION_FILE');
    }

    /**
     * Determine if Safe Redirect Mananger is installed.
     *
     * @return bool
     */
    public function hasSafeRedirectManager()
    {
        return class_exists('\SRM_Redirect');
    }

   /**
     * Determine if Pretty Links is installed.
     *
     * @return bool
     */
    public function hasPrettyLinks()
    {
        return function_exists('\prli_autoloader');
    }

  /**
     * Parse any known variables in the Redirection target URL before
     * processing a redirect.
     *
     * @return void
     */
    public function handleRedirection()
    {
        add_filter('redirection_url_target', function ($value) {
            return $this->parse($value);
        });
    }

    /**
     * Parse any known variables in the Safe Redirect Manager target
     * URL before processing a redirect.
     *
     * @return void
     */
    public function handleSafeRedirectManagerr()
    {
        add_filter('srm_redirect_to', function ($value) {
            return $this->parse($value);
        });
    }

    /**
     * Parse any known variables in the Pretty Links target URL before
     * processing a redirect.
     *
     * @return void
     */
    public function handlePrettyLinks()
    {
        add_filter('prli_target_url', function ($value) {
            return array_merge($value, [
                'url' => $this->parse(Arr::get($value, 'url'))
            ]);
        });
    }

    /**
     * Parse the specified string replacing any known variables.
     *
     * @param  string $value
     * @return string
     */
    public function parse($value = null)
    {
        if (empty(trim($value))) {
            return $value;
        }

        $this->post = get_the_ID() ?: (
            $this->contains(
                $this->referer,
                home_url(),
            ) ? url_to_postid($this->referer) : null
        );

        $variables = $this->collect($this->variables)
            ->map(function ($value) {
                if ($this->contains($value, 'utm_source')) {
                    return sanitize_title(
                        Arr::get($_GET, $value, $this->getSource())
                    );
                }

                if ($this->contains($value, 'utm_medium')) {
                    return sanitize_title(
                        Arr::get($_GET, $value, $this->getMedium())
                    );
                }

                if ($this->contains($value, 'search_term')) {
                    return sanitize_title($this->getSearchTerm());
                }

                if ($this->contains($value, 'post_id')) {
                    return $this->post ?: __('unknown', 'redirection-variables');
                }

                if ($this->contains($value, 'post_name')) {
                    return sanitize_title(
                        get_post_field('post_name', $this->post) ?: __('unknown', 'redirection-variables')
                    );
                }

                if ($this->contains($value, 'post_title')) {
                    return sanitize_title(
                        get_the_title($this->post) ?: __('unknown', 'redirection-variables')
                    );
                }

                return sanitize_title(
                    Arr::get($_GET, $value, __('unknown', 'redirection-variables'))
                );
            });

        return str_replace(
            $variables->keys()->all(),
            $variables->values()->all(),
            $value
        );
    }

    /**
     * Get the current traffic source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->parser->getSource() ?: __('direct', 'redirection-variables');
    }

    /**
     * Retrieve the current traffic medium.
     *
     * @return string
     */
    public function getMedium()
    {
        return $this->contains($this->parser->getMedium(), 'invalid') ?
            'organic' :
            $this->parser->getMedium();
    }

    /**
     * Retrieve the parsed search term.
     *
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->parser->getSearchTerm() ?? __('unknown', 'redirection-variables');
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new collection instance.
     *
     * @param  mixed $items
     * @return \Tightenco\Collect\Support\Collection
     */
    protected function collect($items = [])
    {
        return new Collection($items);
    }
}
