<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
 * Modified by Gigablah <gigablah@vgmdb.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace VGMdb\Component\Routing\Translation;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for Locale Resolvers.
 *
 * A resolver implementation is triggered only if we match a route that is
 * available for multiple locales.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface LocaleResolverInterface
{
    /**
     * Resolves the locale in case a route is available for multiple locales.
     *
     * @param array $availableLocales
     *
     * @return string|null May return null if no suitable locale is found, may also
     *                     return a locale which is not available for the matched route
     */
    public function resolveLocale(Request $request, array $availableLocales);
}