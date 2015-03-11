<?php
/*
Plugin Name: PDF Thumbnail-Link
Plugin URI: 
Description: Thumbnail image with a link to a PDF file.
Version: 0.1
Author: kt_shin1
Author URI: 
License: GPL2
*/

class pdf_thumbnailLink{
	
	var $optionKey='pdf-thumbnail-link_imagemagick-path';
	var $optionSize='pdf-thumbnail-link_image-size';
	var $sizeDfault=150;
	
	function __construct(){
		add_filter( 'media_send_to_editor', array($this,'convert'), 10 ,7);
		add_action('admin_menu', array($this, 'adminMenu'));
	}
	
	function convert($html){
		
		//imagemagickパス（※）
		//$imagemagickPath='/usr/local/bin/convert';
		$imagemagickPath=esc_html(get_option($this->optionKey));
		$imageSize=esc_html(get_option($this->optionSize));
		if(!$imageSize)$imageSize=$this->sizeDefault;
	
		if(($imagemagickPath) && (preg_match('/<a.+href\s*=\s*"(.*?\.pdf)".+?>/i',$html,$r))){
			
			$pdfFile=$r[1];
			
			preg_match('/.+\/([0-9]{4})\/([0-9]{2})\/(.+?)(\.pdf)$/i',$r[1],$rr);
			$yearDir=$rr[1];
			$monthDir=$rr[2];
			$fileName=$rr[3];
			$ext=$rr[4];
			
			//パスとか組み立て
			$uploadFile=WP_CONTENT_DIR."/uploads/{$yearDir}/{$monthDir}/{$fileName}{$ext}[0]";
			$imgFile=plugin_dir_path( __FILE__ ).'upload/'.$fileName.'.jpg';
			
			//コマンド作成
			if(preg_match('/convert$/',$imagemagickPath)){
				$cmd="{$imagemagickPath} {$uploadFile} {$imgFile}";
				exec($cmd,$output,$return_var);

			
				$imgUrl=plugin_dir_url(__FILE__).'upload/'.$fileName.'.jpg';
				return "<a href=\"{$r[1]}\"><img src=\"{$imgUrl}\" width=\"{$imageSize}\" /></a>";
			}else{
				return $html;
			}
		}
		
		return $html;
	}
	
	function adminMenu(){
		add_options_page('PDF ThumbnailLink','PDF ThumbnailLink',8,__FILE__,array($this,'addAdmin'));
	}
	function addAdmin(){
		
		$im_postKey='imagemagick_path';
		$size_postKey='size';
		
		$im_val=esc_html(get_option($this->optionKey));
		$size_val=esc_html(get_option($this->optionSize));
		
		if($_POST['ptl']) {
			$im_val=esc_html($_POST['ptl'][$im_postKey]);
			$size_val=esc_html($_POST['ptl'][$size_postKey]);
			
			update_option($this->optionKey,$im_val);
			update_option($this->optionSize,$size_val);
			echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
		}
?>
		<div class="wrap">
			<h2>PDF Thumbnail Link</h2>
			<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<p>ImageMagick path：<input type="text" name="ptl[<?php echo $im_postKey; ?>]" value="<?php echo $im_val; ?>" size="20"></p>
				<p>thumbnail size: <input type="text" name="ptl[<?php echo $size_postKey; ?>]" value="<?php echo $size_val; ?>" size="5"></p>
				<p class="submit"><input type="submit" name="Submit" value="Update Options" /></p>
			</form>
		</div>

<?php
	}
}


$pdfThumbnailLink=new pdf_thumbnailLink();
	/*
	検討事項
	imagemagickのパス
	_blankで開くか？
	サムネイルサイズ
	リサイズ
	保存先フォルダ
	ファイル名のエスケープ
	インストール時のimagemagickのチェック
	*/
	
	/*
	ImageMagickのパス
		サクラインターネット： /usr/local/bin/convert
		CPI：/usr/local/bin/convert
		エックスサーバ：/usr/bin/convert
		
	*/
?>
