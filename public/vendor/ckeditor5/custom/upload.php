<?php
require_once("config.php");

if(!function_exists('ft_nonce_is_valid')){
    include_once('../editor.lib.php');
}

if( !function_exists('che_reprocessImage') ){
    function che_reprocessImage($file_path, $callback){

        $MIME_TYPES_PROCESSORS = array(
            "image/gif"       => array("imagecreatefromgif", "imagegif"),
            "image/jpg"       => array("imagecreatefromjpeg", "imagejpeg"),
            "image/jpeg"      => array("imagecreatefromjpeg", "imagejpeg"),
            "image/png"       => array("imagecreatefrompng", "imagepng"),
            "image/bmp"       => array("imagecreatefromwbmp", "imagewbmp")
        );

        // Extracting mime type using getimagesize
        try {
            $image_info = getimagesize($file_path);
            if ($image_info === null) {
              //throw new Exception("Invalid image type");
              return false;
            }

            $mime_type = $image_info["mime"];

            if (!array_key_exists($mime_type, $MIME_TYPES_PROCESSORS)) {
              //throw new Exception("Invalid image MIME type");
              return false;
            }

            $image_from_file = $MIME_TYPES_PROCESSORS[$mime_type][0];
            $image_to_file = $MIME_TYPES_PROCESSORS[$mime_type][1];

            $reprocessed_image = @$image_from_file($file_path);

            if (!$reprocessed_image) {
              //throw new Exception("Unable to create reprocessed image from file");
              return false;
            }

            // Calling callback(if set) with path of image as a parameter
            if ($callback !== null) {
              $callback($reprocessed_image);
            }

            // Freeing up memory
            imagedestroy($reprocessed_image);
        } catch (Exception $e) {
            unlink($file_path);
            return false;
        }

        return true;
    }
}

$is_editor_upload = false;

$get_nonce = get_session('nonce_'.FT_NONCE_SESSION_KEY);

if( $get_nonce && ft_nonce_is_valid( $get_nonce, 'ckeditor' ) ){
    $is_editor_upload = true;
}

if( !$is_editor_upload ){
    exit;
}

//----------------------------------------------------------------------------
//
//
$tempfile = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];

$type = substr($filename, strrpos($filename, ".")+1);
$found = false;
switch ($type) {
	case "jpg":
	case "jpeg":
	case "gif":
	case "png":
		$found = true;
}

if ($found != true) {
	exit;
}

// 저장 파일 이름: 년월일시분초_렌덤문자8자
// 20140327125959_abcdefghi.jpg
// 원본 파일 이름: $_POST["origname"]

$filename = che_replace_filename($filename);
$savefile = SAVE_DIR . '/' . $filename;

move_uploaded_file($tempfile, $savefile);
$imgsize = getimagesize($savefile);
$filesize = filesize($savefile);

if (!$imgsize) {
	$filesize = 0;
	$random_name = '-ERR';
	unlink($savefile);
};

if ( CHE_UPLOAD_IMG_CHECK && ! che_reprocessImage($savefile, null) ){
	$filesize = 0;
	$random_name = '-ERR';
	unlink($savefile);
}

try {
    if(defined('G5_FILE_PERMISSION')) chmod($savefile, G5_FILE_PERMISSION);
} catch (Exception $e) {
}

$rdata = sprintf('{"fileUrl": "%s/%s", "filePath": "%s", "fileName": "%s", "fileSize": "%d" }',
	SAVE_URL,
	$filename,
	$savefile,
	$filename,
	$filesize );

$rdata = sprintf('{"default": "%s/%s"}',SAVE_URL, $filename);

$rdata = sprintf('{"url": "%s/%s"}',SAVE_URL, $filename);

echo $rdata;

//echo json_encode(array('load' => array('default' => SAVE_URL.'/'.$filename)));
//echo json_encode(array('default' => SAVE_URL.'/'.$filename));
?>