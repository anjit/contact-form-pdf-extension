<?php
/*

   Plugin Name: Contact Form Mail PDF
   Plugin URI: http://wordpress.org/extend/plugins/contact-form-7-pdf-extension/
   Version: 1.0
   Author: Anjit Vishwakarma
   Author URI:anjitvishwakarma28.wordpress.com
   Description: Send form Data to the mail in PDF attachment from <a href="http://wordpress.org/extend/plugins/contact-form-7/">Contact Form 7</a>, <a href="http://wordpress.org/extend/plugins/si-contact-form/">Fast Secure Contact Form</a> and <a href="http://www.gravityforms.com">Gravity Forms</a>. Click <a href="admin.php?page=contact_mail_pdf">Settings</a> To start
   Text Domain: contact-form-7-pdf-extension
   License: GPL3
  */

// Add admin option

add_action( 'admin_menu', 'conf7_pdf_init' );


	function conf7_pdf_init() {

	add_menu_page('contact_mail_pdf', "Contact Form7 Mail-PDF", "manage_options", "conf7_mail_pdf_ext", "conf7_mail_pdf_ext",plugins_url('ico.png',__FILE__));

	add_action( 'admin_init', 'update_conf7_pdf_setting' );
	}

	function conf7_mail_pdf_ext(){
?>

<h1>Contact Form 7 Data Mail As PDF </h1>
<h2>Settings</h2>

<form method="post" action="options.php" >
<?php settings_fields( 'contact_mail_pdf-settings' ); ?>
<?php do_settings_sections( 'contact_mail_pdf-settings' ); ?>
 <?php wp_nonce_field( 'update_conf7_pdf_setting', 'conf7_nonce' ); ?>
	<div class="form-group">
	<table class="form-table">
	<tbody>
		
		<tr>
		<th scope="row">Enter Contact form ID</th>
		<td>
			<fieldset><legend class="screen-reader-text"><span>Enter Contact form ID</span></legend>
			<label><input type="text" class="form-control" name="form_id" value="<?php if(get_option( 'form_id' )): echo get_option( 'form_id' ); endif;?>" > Form ID</label>
			<br>
			</fieldset>
		</td>
		</tr>
		
		<tr>
		<th scope="row">PDF Templates</th>
		<td>
			<fieldset><legend class="screen-reader-text"><span>PDF Templates</span></legend>
			Coming Soon!
			<br>
			</fieldset>
		</td>
		</tr>
	
	</tbody>
	</table>
<?php submit_button(); ?>
	</div>
</form>
<p style="font-size: 19px;text-align: center;">Do you want full plugin?</br>Please Contact me <a href="http://helponsoftware.com/contact/" target="blank">Anjit Vishwakarma</a></p>
<?php }?>
<?php

if( !function_exists("update_conf7_pdf_setting") ){

	function update_conf7_pdf_setting() {

		register_setting( 'contact_mail_pdf-settings', 'form_id','intval' );
		}
	}

if(isset($_REQUEST["submit"])){ 

		update_option('form_id',sanitize_text_field($_REQUEST['form_id']));    
	}


/* sending the attachments with email by- Anjit vishwakarma */

add_action( 'wpcf7_before_send_mail', 'send_conf7_attachment_file',10, 1 );
 
 	function send_conf7_attachment_file($cf7){

 //check if this is the right form ID
 if ($cf7->id==get_option( 'form_id' )){
 
 	// ...
 	if ($cf7->mail['use_html']==true) $nl="<br/>"; else $nl="\n";
 
 	
 	// getting all submitted data
 	$submission = WPCF7_Submission::get_instance();
	$data = $submission->get_posted_data();

	
	unset($data[_wpcf7]);
	unset($data[_wpcf7_version]);
	unset($data[_wpcf7_locale]);
	unset($data[_wpcf7_is_ajax_call]);
	unset($data[_wpcf7_unit_tag]);
	
	// making the html

	$html .='<h2>Submitted Form Data</h2>';
	$html .='<table align="center" border="1">';
	foreach($data as $key=>$value):
	if(is_array($value)):
	$html .='<tr><td>'.ucfirst($key).'</td><td>'.ucfirst($value[0]).'</td></tr>';
	else:	
	$html .='<tr><td>'.ucfirst($key).'</td><td>'.ucfirst($value).'</td></tr>';
	endif;
	endforeach;	
	$html .='</table>';

	// library for genrating the pdf file 
	require('pdflib/html2fpdf.php');
	$pdf=new HTML2FPDF();
	$pdf->AddPage();
	$strContent = $html;
	$pdf->WriteHTML($strContent);
	$pdf->Output("wp-content/uploads/form-".get_option( 'form_id' ).".pdf");
	

	//I omitted all the stuff used to create
 	//the pdf file, you have just to know that
 	//$pdf_filename contains the filename to attach
 	//Let'go to the file attachment!

	// $pdf_filename with the extenstion not just the filename
 	$pdf_filename = "from".get_option( 'form_id' ).".pdf";

	// geting the real mail /////
	$mail = $cf7->prop('mail');  

	// giving the attachment path//// 
	$mail['attachments']='uploads/'.$pdf_filename;

	$cf7->set_properties(array("mail" => $mail));
	
		
  	}
}