<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


function editor_html($id, $content, $is_dhtml_editor=true)
{

    global $g5, $config, $w, $board, $write;
    static $js = true;

    if(
        $is_dhtml_editor && $content &&
        (
        (!$w && (isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])))
        || ($w == 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false )
        )
    ){
        //글쓰기 기본 내용 처리
        if( preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>') ) {  //textarea로 작성되고, html 내용이 없다면
            $content = nl2br($content);
        }
    }
    //$data = htmlspecialchars("<p>Hello, world!</p>", ENT_QUOTES, 'UTF-8');
    $editor_url = 'https://blot23.mycafe24.com/plugin/editor/' . $config['cf_editor'];
    $nonce_key = ft_nonce_create('ckeditor');

    $html =
<<<EOD
<link rel="stylesheet" href="{$editor_url}/ckeditor5/ckeditor5.css">
<div class="main-container">
  <div class="editor-container editor-container_classic-editor editor-container_include-style" id="editor-container">
    <div class="editor-container__editor">
      <textarea id="{$id}" name="{$id}">{$content}</textarea>
    </div>
  </div>
</div>

<script type="importmap">
{
  "imports": {
    "ckeditor5": "{$editor_url}/ckeditor5/ckeditor5.js",
    "ckeditor5/": "{$editor_url}/ckeditor5/",
    "custom/": "{$editor_url}/custom/"
  }
}
</script>

<script type="module" src="{$editor_url}/main.js"></script>
<script src="{$editor_url}/custom/customupload.editor.js"></script> 
<script> var ed_nonce = "{$nonce_key}"; </script>
<script type="module">
// ckeditor5에서 직접 ClassicEditor 가져오기
import { ClassicEditor, editorConfig } from "{$editor_url}/main.js";

document.addEventListener("DOMContentLoaded", () => {
  //let id}_editor = null;
  ClassicEditor
    .create(document.querySelector("#{$id}"), editorConfig)
    .then(editor => { 
        window.editor = editor; // 필요시 전역 변수로 저장 
      
        //id}_editor = editor;   
        // setTimeout(() => {
        //     const sourceButton = document.querySelector('.ck-source-editing-button'); 
        //     if (sourceButton) {
        //         sourceButton.click();
        //     }
        // }, 300);          
        // dewbian 커스텀 플러그인 인스턴스 생성 및 초기화
        const customImageUpload = new CustomImageUpload(editor);
        customImageUpload.init(); 
      //return editor;
    })
    .catch(error => {
      console.error("에디터 초기화 실패:", error);
    });
});
</script>
EOD;


    return $html;
}




function editor_html_origin($id, $content, $is_dhtml_editor=true){

//    echo "************** /www/plugin/editor/ckeditor5/editor.lib.php<br>";
//    echo "id==>[".$id."]<br>";
//    echo "content==>[".$content."]<br>";
//    echo "$is_dhtml_editor==>[".$is_dhtml_editor."]<br>";
//    echo "**********************************************************<br>";

    global $g5, $config, $w, $board, $write;
    static $js = true;

    $editor_url = '/plugin/editor/'.$config['cf_editor'];
    $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];

//    if(
//        $is_dhtml_editor && $content &&
//        (
//        (!$w && (isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])))
//        || ($w == 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false )
//        )
//    ){       //글쓰기 기본 내용 처리
//        if( preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>') ) {  //textarea로 작성되고, html 내용이 없다면
//            $content = nl2br($content);
//        }
//    }


//    //$data = htmlspecialchars("<p>Hello, world!</p>", ENT_QUOTES, 'UTF-8');

    echo "editor_url ===>[".$editor_url."]<br>";
    echo "1 main--->[".$editor_url.'/main.js'."]<br>";
    echo "2 main--->[".$editor_url.'/ckeditor5/ckeditor5.js'."]<br>";
    echo "3 main--->[".$editor_url.'/ckeditor5/'."]<br>";

    $html = '';
//    if ($is_dhtml_editor && $js) {
//
//        $html .= '<script type="module" src="'.$editor_url.'/main.js"></script>';
////        $html .= '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/ckeditor.config.js"></script>';
////        // 업로드어댑터
////        $html .= '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/simpleupload.editor.js"></script>';
////        // [봄봄봄] 이미지 업로드 플러그인
////        $html .= '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/customupload.editor.js"></script>';
////        $html .= '<script> var ed_nonce = "'.ft_nonce_create('ckeditor').'"; </script>';
//		$js = false;
//    }
    $html .= '<link rel="stylesheet" href="'.$editor_url.'/ckeditor5/ckeditor5.css">';
    $html .= '<div class="main-container">';
	$html .= '		<div class="editor-container editor-container_classic-editor editor-container_include-style" id="editor-container">';
    $html .= '			<div class="editor-container__editor">';
    $html .= '              <textarea id="'.$id.'" name="'.$id.'" >'.$content.'</textarea>';
	$html .= '          </div>';
	$html .= '		</div>';
    $html .= '	</div>';
    //$html .= '<script type="module" src="'.$editor_url.'/main.js"></script>';
    $html .= '
    
    <script type="importmap">
        {
            "imports": {
                "ckeditor5": "'.$editor_url.'/ckeditor5/ckeditor5.js",
                "ckeditor5/": "'.$editor_url.'/ckeditor5/"
                "custom/": "'.$editor_url.'/custom/"
            }
        }
    </script>     
    <script type="module" src="'.$editor_url.'/main.js"></script>    
    <script type="module">
        import { ClassicEditor, editorConfig } from "'.$editor_url.'/main.js";
 
        document.addEventListener("DOMContentLoaded", () => {
            ClassicEditor
                    .create(document.querySelector("#"'.$id.'""), editorConfig)
                    .then(editor => {
                        alert("melong___에디터라이브러리를 통해서 오예!!!!!!!!!!!!!!!!!!!!!!!!!!! !!!!!!!!!!!!!! ");

                        // 여기에 원하는 추가 코드 작성 가능

                        return editor;
                    })
                    .catch(error => {
                        console.error("에디터 초기화 실패:", error);
                    });
        }); 
    </script>
    ';

	return $html;
}


// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id, $is_dhtml_editor=true)
{   
    if ($is_dhtml_editor) { 
        return ' var '.$id.'_editor_data = '.$id.'.getData(); ';
    } else { 
        return ' var '.$id.'_editor = document.getElementById("'.$id.'"); ';
    }
}


//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $is_dhtml_editor=true)
{ 
    if ($is_dhtml_editor) {
        return ' if (!'.$id.'_editor_data) { alert("내용을 입력해 주십시오."); '.$id.'_editor.editing.view.focus(); return false; } if (typeof(f.'.$id.')!="undefined") f.'.$id.'.value = '.$id.'_editor_data; ';
    } else {
        return ' if (!'.$id.'_editor.value) { alert("내용을 입력해 주십시오."); '.$id.'_editor.focus(); return false; } ';
    }
}

/*
https://github.com/timostamm/NonceUtil-PHP
*/

if (!defined('FT_NONCE_UNIQUE_KEY'))
    define( 'FT_NONCE_UNIQUE_KEY' , sha1($_SERVER['SERVER_SOFTWARE'].G5_MYSQL_USER.session_id().G5_TABLE_PREFIX) );

if (!defined('FT_NONCE_SESSION_KEY'))
    define( 'FT_NONCE_SESSION_KEY' , substr(md5(FT_NONCE_UNIQUE_KEY), 5) );

if (!defined('FT_NONCE_DURATION'))
    define( 'FT_NONCE_DURATION' , 60 * 30  ); // 300 makes link or form good for 5 minutes from time of generation,  300은 5분간 유효, 60 * 60 은 1시간

if (!defined('FT_NONCE_KEY'))
    define( 'FT_NONCE_KEY' , '_nonce' );

// This method creates a key / value pair for a url string
if(!function_exists('ft_nonce_create_query_string')){
    function ft_nonce_create_query_string( $action = '' , $user = '' ){
        return FT_NONCE_KEY."=".ft_nonce_create( $action , $user );
    }
}

if(!function_exists('ft_get_secret_key')){
    function ft_get_secret_key($secret){
        return md5(FT_NONCE_UNIQUE_KEY.$secret);
    }
}

// This method creates an nonce. It should be called by one of the previous two functions.
if(!function_exists('ft_nonce_create')){
    function ft_nonce_create( $action = '',$user='', $timeoutSeconds=FT_NONCE_DURATION ){

        $secret = ft_get_secret_key($action.$user);

		$salt = ft_nonce_generate_hash();
		$time = time();
		$maxTime = $time + $timeoutSeconds;
		$nonce = $salt . "|" . $maxTime . "|" . sha1( $salt . $secret . $maxTime );

        set_session('nonce_'.FT_NONCE_SESSION_KEY, $nonce);

		return $nonce;

    }
}

// This method validates an nonce
if(!function_exists('ft_nonce_is_valid')){
    function ft_nonce_is_valid( $nonce, $action = '', $user='' ){
        $secret = ft_get_secret_key($action.$user);
		if (is_string($nonce) == false) {
			return false;
		}
		$a = explode('|', $nonce);
		if (count($a) != 3) {
			return false;
		}
		$salt = $a[0];
		$maxTime = intval($a[1]);
		$hash = $a[2];
		$back = sha1( $salt . $secret . $maxTime );
		if ($back != $hash) {
			return false;
		}
		if (time() > $maxTime) {
			return false;
		}
		return true;
    }
}

// This method generates the nonce timestamp
if(!function_exists('ft_nonce_generate_hash')){
    function ft_nonce_generate_hash(){
		$length = 10;
		$chars='1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		$ll = strlen($chars)-1;
		$o = '';
		while (strlen($o) < $length) {
			$o .= $chars[ rand(0, $ll) ];
		}
		return $o;
    }
}
?>