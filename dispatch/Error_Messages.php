<?php
class Error_messages {
    public static function getEMessages() {
        $EMessages = ['system-messages' =>[
                                            'new'=>[
                                                'warning' =>'',
                                                'error'   =>'Error: You have to indicate the path and foldername to copy files to.. ex. --new:/home/newfolder'
                                            ]
                                          ]
                    ];
        return $EMessages;
    }
}