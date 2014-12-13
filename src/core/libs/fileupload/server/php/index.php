<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$custom_dir = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['custom_dir'];
$upload_handler = new UploadHandler(array('upload_dir' => $custom_dir, 'image_versions' => array()));


