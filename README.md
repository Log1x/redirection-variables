# Redirection Variables

![Latest Stable Version](https://img.shields.io/packagist/v/log1x/redirection-variables?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/log1x/redirection-variables?style=flat-square)

Redirection Variables adds a few handy referer tracking variables usable inside of redirects created by the WordPress plugins [Redirection](https://wordpress.org/plugins/redirection/), [Safe Redirect Manager](https://wordpress.org/plugins/safe-redirect-manager/), and [Pretty Links](https://wordpress.org/plugins/pretty-link/).

This can be particularly useful for passing tracking data from your referring posts/pages to affiliate URL's.

## Requirements

- [PHP](https://secure.php.net/manual/en/install.php) >= 7.2.5
- [Composer](https://getcomposer.org/download/)

## Installation

### Bedrock

Install via Composer:

```bash
$ composer require log1x/redirection-variables
```

### Manual

Download the release `.zip` and install into `wp-content/plugins`.

## Usage

An example redirect URL destination would look something like:

```py
/go/google -> https://www.google.com/?example=%source%&example2=%post_name%
```

### Variables

| Variable        | Description                                                                          | Example Value             | Default Value |
| :-------------- | :----------------------------------------------------------------------------------- | :------------------------ | :------------ |
| `%source%`      | The referring page source determined by `utm_source` or the `HTTP_REFERER` variable. | google                    | direct        |
| `%medium%`      | The referring page medium determined by `utm_medium`.                                | search                    | organic       |
| `%campaign%`    | The referring page campaign determined by `utm_campaign`.                            | summer-sale               | unknown       |
| `%term%`        | The referring page campaign determined by `utm_term`.                                | fish-oil                  | unknown       |
| `%content%`     | The referring page content determined by `utm_content`.                              | cta-link                  | unknown       |
| `%adgroup%`     | The referring page adgroup determined by `utm_adgroup`.                              | ppc-1                     | unknown       |
| `%search_term%` | The referring page search term passed by various search engines.                     | Best Fish Oil             | unknown       |
| `%gclid%`       | The referring page gclid passed by Google Ads.                                       | lorem-1234                | unknown       |
| `%post_id%`     | The referring page post ID.                                                          | 17                        | unknown       |
| `%post_title%`  | The referring page post title.                                                       | the-best-fish-oil-of-2020 | unknown       |
| `%post_name%`   | The referring page post name (slug).                                                 | best-fish-oil             | unknown       |

## Bug Reports

If you discover a bug in Redirection Variables, please [open an issue](https://github.com/log1x/redirection-variables/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

Redirection Variables is provided under the [MIT License](https://github.com/log1x/redirection-variables/blob/master/LICENSE.md).
