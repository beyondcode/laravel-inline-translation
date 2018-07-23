<?php

namespace BeyondCode\InlineTranslation;


use Illuminate\Http\Request;

class InlineTranslationController
{

    public function store(Request $request, InlineTranslation $inlineTranslation)
    {
        $inlineTranslation->updateTranslationFiles(app()->getLocale(), $request->get('key'), $request->get('value'));
    }

}