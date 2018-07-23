<?php

namespace BeyondCode\InlineTranslation;

use Closure;

class InlineTranslationMiddleware
{
    /** @var InlineTranslation */
    private $inlineTranslation;

    public function __construct(InlineTranslation $inlineTranslation)
    {
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->inlineTranslation->isEnabled()) {
            return $next($request);
        }

        $this->inlineTranslation->boot();

        $response = $next($request);

        if ($response->isRedirection()) {
            return $response;
        } elseif (
            ($response->headers->has('Content-Type') &&
                strpos($response->headers->get('Content-Type'), 'html') === false)
            || $request->getRequestFormat() !== 'html'
            || $response->getContent() === false
        ) {
            return $response;
        } elseif (is_null($response->exception)) {
            $this->injectTranslationView($response);
        }

        return $response;
    }

    protected function injectTranslationView($response)
    {
        $content = $response->getContent();

        $renderedContent = view('inline-translation::translation');

        $pos = strripos($content, '</body>');

        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $renderedContent . substr($content, $pos);
        } else {
            $content = $content . $renderedContent;
        }

        // Update the new content and reset the content length
        $response->setContent($content);
        $response->headers->remove('Content-Length');
    }

}