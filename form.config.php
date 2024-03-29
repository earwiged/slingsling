<?php
/**

Copyright 2015 JQueryForm.com
License: http://jqueryform.com/license.php

FormID: jqueryform-c96556
Date: 2015-11-02 19:47:59
Generated by http://www.jqueryform.com

IF your form uses mailgun AND the form has file upload field, PHP 5.5+ is required.

*/

namespace JF;

class Config {

    public static function getConfig( $decode = true ){
        ob_start();
        // ---------------------------------------------------------------------
        // JSON format config
        // Note: please make a copy before you edit config manually
        // ---------------------------------------------------------------------
?>        
{
    "formId": "jqueryform-c96556",
    "email": {
        "to": "bjz73in7@robot.zapier.com",
        "cc": "",
        "bcc": "",
        "subject": "SLING Names",
        "template": ""
    },
    "admin": {
        "users": "admin:Elihu513",
        "dataDelivery": "emailAndFile"
    },
    "thankyou": {
        "url": "",
        "message": "Your choice is now under consideration.  Exciting, eh?  Reloading page so you can do this again if you wish...",
        "seconds": "3"
    },
    "seo": {
        "trackerId": "",
        "title": "SLINGalytics",
        "description": "",
        "keywords": "",
        "author": ""
    },
    "mailer": "local",
    "smtp": {
        "host": "",
        "user": "",
        "password": ""
    },
    "mailgun": {
        "domain": "",
        "apiKey": "",
        "fromEmail": "",
        "fromName": ""
    },
    "logics": [

    ],
    "fields": [
        {
            "label": "Choose your naming scenario:",
            "field_type": "radio",
            "required": true,
            "field_options": {
                "options": [
                    {
                        "label": "Product Version Name (i.e., Mavericks)",
                        "checked": false,
                        "value": ""
                    },
                    {
                        "label": "Product Name (i.e., Windozer8)",
                        "checked": false
                    },
                    {
                        "label": "Type of Solution (i.e., ILS)",
                        "checked": false
                    }
                ],
                "presetJson": "",
                "validators": {
                    "required": {
                        "enabled": true
                    }
                }
            },
            "cid": "c33"
        },
        {
            "label": "Your name suggested name:",
            "field_type": "paragraph",
            "field_options": {
                "validators": {
                    "required": {
                        "enabled": true
                    },
                    "minlength": {
                        "enabled": false
                    },
                    "maxlength": {
                        "enabled": true,
                        "val": "100"
                    }
                }
            },
            "cid": "c3"
        }
    ],
    "lang": {
        "btnSubmit": "Submit"
    }
}        
<?php
	    $json = ob_get_clean() ;

        return $decode ? json_decode( trim($json), true ) : $json;
    } // end of getConfig()

} // end of Config class
