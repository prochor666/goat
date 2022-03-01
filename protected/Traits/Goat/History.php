<?php
namespace Goat;


trait History
{
    public function record($data)
    {
        $normData = [
            'targetid'      => ark($data, 'id', 0),
            'type'          => ark($data, 'type', 'history-default'),
            'message'       => ark($data, 'message', ''),
            'data'          => json_encode(ark($data, 'data', [])),
            'username'      => ark($data, 'username', ''),
            'userid'        => ark($data, 'userid', -1),
            'created_at'    => date( 'Y-m-d H:i:s', ark($data, 'created_at', time())),
        ];

        return $normData;
    }
}
