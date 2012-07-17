<?php

//sign in
$config['sign_in_recaptcha_offset']		= 3;
$config['sign_in_recaptcha_enabled']	= TRUE;

$config['social_media_enabled']			= TRUE;
$config['social_media_providers'] 		= array(
												'facebook'=>array(
																'icon'=>'http://cdn3.iconfinder.com/data/icons/socialnetworking/16/facebook.png',
																'enabled'=>TRUE
															), 
												'twitter'=>array(
																'icon'=>'http://cdn3.iconfinder.com/data/icons/socialnetworking/16/twitter.png',
																'enabled'=>TRUE
															), 
												'google'=>array(
																'icon'=>'http://cdn3.iconfinder.com/data/icons/socialnetworking/16/google.png',
																'enabled'=>TRUE
															), 
												'yahoo'=>array(
																'icon'=>'http://cdn5.iconfinder.com/data/icons/yooicons_set01_socialbookmarks/16/social_yahoo_box_lilac.png',
																'enabled'=>TRUE
															), 
												'openid'=>array(
																'icon'=>'http://cdn4.iconfinder.com/data/icons/socialmediaicons_v120/16/openid.png',
																'enabled'=>TRUE
															)
											);
					
//sign out
$config['sign_out_view_enabled'] 			= TRUE;

//sign up
$config['sign_up_recaptcha_enabled'] 		= FALSE;
$config['sign_up_auto_sign_in'] 			= TRUE;

//forgot password
$config['password_reset_expiration'] 		= 1800;
$config['password_reset_secret'] 			= 'F3A36850991A6F8082723C81D43DE030E1DC2501D43E9486E3223DAC4C29CE8B';
$config['password_reset_email'] 			= 'no-reply@lofable.com';
