<?php

namespace BeyondCode\InlineTranslation;

use Illuminate\Support\NamespacedItemResolver;
use View;
use File;

/**
 * Class InlineTranslation
 * @package BeyondCode\InlineTranslation
 */
class InlineTranslation
{

    public function isEnabled()
    {
        return config('inline-translation.enabled');
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            $viewContent = file_get_contents($view->getPath());

            $file = tempnam(sys_get_temp_dir(), $view->name());

            (new TemplateParser($view->getData()))->parseTranslationTags($viewContent);

            file_put_contents($file, $viewContent);

            $view->setPath($file);
        });
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string $value
     */
    public function updateTranslationFiles($locale, $key, $value)
    {
        // Try JSON lookup first.
        $this->updateJsonLanguageFile(resource_path('lang/' . $locale . '.json'), $key, $value);

        // JSON does not exist - try php based language files
        list($namespace, $group, $item) = app(NamespacedItemResolver::class)->parseKey($key);

        if (is_null($namespace) || $namespace == '*') {
            $this->updateRequiredLanguageFile(resource_path("lang/$locale/$group.php"), $item, $value);
        } else {
            $this->updateRequiredLanguageFile(resource_path("lang/vendor/$namespace/$locale/$group.php"), $item, $value);
        }
    }

    /**
     * @param string $path
     * @param string $item
     * @param string $value
     */
    private function updateRequiredLanguageFile($path, $item, $value)
    {
        if (File::exists($path)) {
            $content = File::getRequire($path);

            if (array_has($content, $item)) {
                $content[$item] = $value;

                File::put($path, '<?php return '.var_export($content, true).';');
            }
        }
    }

    /**
     * @param string $path
     * @param string $item
     * @param string $value
     */
    private function updateJsonLanguageFile($path, $item, $value)
    {
        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);

            if (!is_null($decoded) && array_has($decoded, $item)) {
                $decoded[$item] = $value;

                File::put($path, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

}