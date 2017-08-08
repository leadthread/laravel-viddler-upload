<?php

return [

    /**
     * Your Auth information.
     */
    "auth" => [
        "key"  => env("VIDDLER_KEY"),
        "user" => env("VIDDLER_USER"),
        "pass" => env("VIDDLER_PASS"),
    ],

    /**
     * Log the steps of the video
     */
    "log" => false,

    /**
     * The table to use.
     */
    "table" => "viddler",

    /**
     * The Eloquent Model to use
     * (Must extend \LeadThread\Viddler\Upload\Models\Viddler)
     */
    "model" => \LeadThread\Viddler\Upload\Models\Viddler::class,

    /**
     * The storage disk to use.
     */
    "disk" => "default",

    /**
     * Video conversion settings.
     * Requires ffmpeg v2 to be installed.
     * Only video/mp4 is supported as a target.
     * Conversion is recommended to avoid some issues with iPhones
     */
    "convert" => [
        "enabled" => true,

        "instructions" => [
            "video/quicktime" => "video/mp4",
            "application/octet-stream" => "video/mp4"
        ]
    ],
];
