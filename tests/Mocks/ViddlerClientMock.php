<?php

namespace Zenapply\Viddler\Upload\Tests\Mocks;

use Zenapply\Viddler\Upload\Models\Viddler;
use Zenapply\Viddler\Upload\Components\ViddlerClient;

class ViddlerClientMock extends ViddlerClient
{
    public function prepareUpload()
    {
        return [
            'upload' => [
                'token' => 'foo',
                'endpoint' => 'bar'
            ]
        ];
    }

    public function executeUpload($endpoint, $postFields)
    {
        return [
            'video' => [
                'id' => 'foobar',
            ]
        ];
    }

    public function executeCheck(Viddler $model)
    {
        return [
            "list_result" => [
                "video_encoding_list" => [
                    [
                        "created_at" => 1431697206,
                        "original_file_name" => "Batman vs Superman Trailer.mp4",
                        "video" => [
                            "id" => "aecfca57",
                            "status" => "ready",
                            "author" => "vt_brendan",
                            "title" => "TEST VIDEO",
                            "upload_time" => "1431602832",
                            "updated_at" => "1431696928",
                            "made_public_time" => "1431602832",
                            "length" => "135",
                            "description" =>" ",
                            "age_limit" =>" ",
                            "url" => "http://www.viddler.com/v/aecfca57",
                            "thumbnail_url" => "http://thumbs.cdn-ec.viddler.com/thumbnail_2_aecfca57_v1.jpg",
                            "thumbnail_version" => "v1",
                            "permalink" => "http://www.viddler.com/v/aecfca57",
                            "html5_video_source" => "http://www.viddler.com/file/aecfca57/html5",
                            "view_count" => "16",
                            "impression_count" => "39",
                            "favorite" => "0",
                            "comment_count" => "0",
                            "password_protection" => "0",
                            "thumbnails_count" => "11",
                            "thumbnail_index" => "0",
                        ],
                        "video_file_encoding_list" => [
                            [
                                "id" => "778002534022d6de8b8298b61270da22",
                                "status" => "ready",
                                "ext" => "mp4",
                                "source" => "0",
                                "flash" => "on",
                                "iphone" => "na",
                                "ipad" => "on",
                                "itunes" => "on",
                                "profile_id" => "4",
                                "profile_name" => "360p",
                                "created_at" => "1431602832",
                                "status_update_at" => "1431602983",
                                "encoding_created_at" => "1431602832",
                                "encoding_last_updated_at" => "1431602983",
                                "encoding_started_at" => "1431602833",
                                "encoding_token" => "e583eeea00b39b2da463195b7c1ea2bfa39bc690f_4",
                                "encoding_status" => "success",
                                "encoding_status_id" => "4",
                                "encoding_progress" => 100,
                                "encoding_queue_position" => "0",
                            ],
                            [
                                "id" => "778002534022d6dad5647237a5369b76",
                                "status" => "ready",
                                "ext" => "3gp",
                                "source" => "0",
                                "flash" => "na",
                                "iphone" => "na",
                                "ipad" => "na",
                                "itunes" => "na",
                                "profile_id" => "5",
                                "profile_name" => "3GP",
                                "created_at" => "1431602832",
                                "status_update_at" => "1431602875",
                                "encoding_created_at" => "1431602832",
                                "encoding_last_updated_at" => "1431602875",
                                "encoding_started_at" => "1431602845",
                                "encoding_token" => "e583eeea00b39b2da463195b7c1ea2bfa39bc690f_5",
                                "encoding_status" => "success",
                                "encoding_status_id" => "4",
                                "encoding_progress" => 100,
                                "encoding_queue_position" => "0",
                            ],
                            [
                                "id" => "778002534022d6d45b06be25c077de52",
                                "status" => "ready",
                                "ext" => "webm",
                                "source" => "0",
                                "flash" => "na",
                                "iphone" => "na",
                                "ipad" => "na",
                                "itunes" => "na",
                                "profile_id" => "6",
                                "profile_name" => "WebM",
                                "created_at" => "1431602832",
                                "status_update_at" => "1431602905",
                                "encoding_created_at" => "1431602832",
                                "encoding_last_updated_at" => "1431602905",
                                "encoding_started_at" => "1431602845",
                                "encoding_token" => "e583eeea00b39b2da463195b7c1ea2bfa39bc690f_6",
                                "encoding_status" => "success",
                                "encoding_status_id" => "4",
                                "encoding_progress" => 100,
                                "encoding_queue_position" => "0",
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
