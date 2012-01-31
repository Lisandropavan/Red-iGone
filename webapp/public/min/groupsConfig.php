<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/



return array(  
  'js' => array(
    '//public/js/rig.js',
    '//public/js/jquery-1.4.min.js',
    '//public/js/jquery.cookie.js',
    '//public/js/swfupload.js',
    '//public/js/jquery.imgareaselect.min.js',
    '//public/js/jquery-ui-1.7.2.custom.min.js',
    '//public/js/jquery.fancybox-1.3.1.pack.js',
    '//public/js/common.js',
    '//public/js/images/email_form.js',
    '//public/js/images/preview.js',
    '//public/js/images/select.js',
    '//public/js/images/send_email.js',
    '//public/js/images/upload.js',
    '//public/js/statics/share.js',
  ),
  
  'css' => array(
    '//public/css/common/styles.css',
    '//public/css/fancybox/jquery.fancybox-1.3.1.css',
    '//public/css/images/email_form.css',
    '//public/css/images/preview.css',
    '//public/css/images/select.css',
    '//public/css/images/send_email.css',
    '//public/css/images/upload.css',
    '//public/css/images/simple_upload.css',
    '//public/css/statics/about.css',
    '//public/css/statics/contact.css',
    '//public/css/statics/index.css',
    '//public/css/statics/help.css',
    '//public/css/statics/share.css',
  ),


    // custom source example
    /*'js2' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => create_function('$a', 'return $a;')
        ))
    ),//*/

    /*'js3' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => array('Minify_Packer', 'minify')
        ))
    ),//*/
);