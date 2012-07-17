<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo lang('connect_linking_page_name'); ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH; ?>auth.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo JS_PATH; ?>jqueryui/jquery-ui-1.8.7.custom.css" media="all" />

	<script src="<?php echo JS_PATH; ?>jquery_new.js"></script>
	<script src="<?php echo JS_PATH; ?>jqueryui/jquery-ui-1.8.12.custom.min.js"></script>
	<script src="<?php echo JS_PATH; ?>jqpass.js"></script>
	<script src="<?php echo JS_PATH; ?>jqvalidation.js"></script>
</head>

<body>
<div style="background:url() no-repeat; width:218px; height:44px;" class="logo"></div>
<div style="text-align:center;"><img src="<?php echo IMAGES_PATH; ?>auth/top_light.png" width="877" height="139" /></div>
<div class="main">
<div style="font-size:12px; font-family:Verdana, Geneva, sans-serif; color:#666; margin-bottom:5px;">
	<?php
		if(isset($account_linking[1]['firstname'])) {
			$name = $account_linking[1]['firstname'];
		}
		else {
			if(isset($account_linking[1]['fullname'])) {
				$name = $account_linking[1]['fullname'];
			}
			else {
				$name=null;
			}
		}
	?>
	<?php echo sprintf(lang('connect_linking_info'), ucwords($name) ) ; ?>
</div>
<h1><?php echo sprintf(lang('connect_linking_heading'), ucwords($account_linking[0]['provider'])); ?></h1>
<div class="form">
	<img src="<?php echo IMAGES_PATH; ?>auth/top.png" width="676" height="7" />
	<?php echo form_open(uri_string(), array('class'=>'login_form')); ?>
    <div class="top">
        <div class="col_left">
			<div>
			<?php echo form_label(lang('connect_linking_username_email'), 'connect_linking_username_email'); ?>
				<?php echo form_input(array(
                        'name' => 'connect_linking_username_email',
                        'id' => 'connect_linking_username_email',
						'required' => 'required',
                        'value' => set_value('connect_linking_username_email'),
                        'maxlength' => '24'
                    )); ?>
            </div>
    	</div><!-- col_left -->
        <div class="col_right">
            <div>
		<?php echo form_label(lang('connect_linking_password'), 'connect_linking_password'); ?>
                <?php echo form_password(array(
                        'name' => 'connect_linking_password',
                        'id' => 'connect_linking_password',
						'required' => 'required',
                        'value' => set_value('connect_linking_password')
                    )); ?>
                <span><?php echo anchor('auth/forgot_password', lang('connect_linking_forgot_your_password')); ?></span>
            </div>
        </div><!-- col_right -->
        <div class="clear"></div>
		
		<?php if (isset($recaptcha)) : ?>
		<div class="">
			<?php echo $recaptcha; ?>
		</div>
		<?php endif; ?>
		
    	<div class="errors">
			<?php echo form_error('connect_linking_username_email'); ?>
			
			<?php if (isset($connect_linking_username_email_error)) : ?>
		        <p><?php echo $connect_linking_username_email_error; ?></p>
		    <?php endif; ?>
			
			<?php if (isset($connect_linking_recaptcha_error)) : ?>
				<p><?php echo $connect_linking_recaptcha_error; ?></p>
			<?php endif; ?>
			
			<?php if (isset($connect_linking_error)) : ?>
				<p><?php echo $connect_linking_error; ?></p>
			<?php endif; ?>
			
			
		</div>
     </div><!-- top -->
     <div class="bottom">
        <div class="button">
            <div class="left">
                <span style="padding-top:6px; display:inline-block;">
						<?php echo form_checkbox(array(
                            'name' => 'connect_linking_remember',
                            'id' => 'connect_linking_remember',
                            'value' => 'checked',
                            'checked' => $this->input->post('connect_linking_remember')
                        )); ?> <?php echo sprintf(lang('connect_linking_remember_me'), 'connect_linking_remember'); ?>
				</span>
            </div><!-- left -->
            <div class="right">
				<?php echo form_button(array(
					'type' => 'submit',
					'class' => 'submitBtn',
					'content' => '<span>'.lang('connect_linking_button').'</span>',
					'tabindex' => '7'
				)); ?>&nbsp;<?php echo sprintf(lang('connect_linking_dont_have_account'), anchor('connect/'.$account_linking[0]['provider'].'/?client=sign_up', lang('connect_linking_sign_up_now'))); ?>
            </div><!-- right -->
            <div class="clear"></div>
        </div><!-- button -->
    </div><!-- bottom -->
    <img src="<?php echo IMAGES_PATH; ?>auth/bottom.png" width="676" height="13" />
    <?php echo form_close(); ?>
</div><!-- form -->      </div><!-- main -->
</body>
</html>
