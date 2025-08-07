<?php

if (!function_exists('ShipTown')) {
    /**
     * Get ShipTown service
     *
     */
    function ShipTown(): \App\Services\ShipTownService
    {
        return app(\App\Services\ShipTownService::class);
    }
}

if (!function_exists('t')) {
    /**
     * Translate a given parameter based on the user's locale or configuration
     *
     */
    function t(string $textInEnglish): string
    {
        try {
            $locale = app()->getLocale();

            $fileName = base_path("locales/backend/{$locale}.json");

            $translations = json_decode(file_get_contents($fileName), true);

            $translatedText = data_get($translations, $textInEnglish);

            if ($translatedText) {
                return $translatedText;
            }

            if ($locale === 'en') {
                $translations[$textInEnglish] = $textInEnglish;
                file_put_contents($fileName, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return $textInEnglish;
            }
        } catch (Exception $e) {
            if (!file_exists(dirname($fileName))) {
                mkdir(dirname($fileName), 0777, true);
            }

            // Handle the exception, e.g., log it or report it
            if (!file_exists($fileName)) {
                // If the file does not exist, create it with an empty array
                file_put_contents($fileName, json_encode([]));
            }
            report($e);
        }

        // Return the original text if no translation was found
        return $textInEnglish;
    }
}
