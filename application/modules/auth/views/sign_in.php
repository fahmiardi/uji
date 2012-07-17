<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo lang('sign_in_page_name'); ?></title>
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
<h1><?php echo lang('sign_in_page_name'); ?> 
	<span class="right" style="font-size:12px; font-family:Verdana, Geneva, sans-serif; color:#666; margin-bottom:-5px;">
		<?php if ($this->config->item('social_media_enabled')) : ?>
			<?php $this->load->language('connect/connect'); ?>
			<span style='font-size:small;'><?php echo lang('connect_social_media'); ?></span>
			<?php foreach($this->config->item('social_media_providers') as $provider=>$property) : ?>				
				<?php if ($property['enabled']) : ?>
					<?php echo anchor('connect/'.$provider.'/?client=sign_in', "<img src=".$property['icon']." alt='' />", 
						array('title'=>sprintf(lang('connect_with_x'), lang('connect_'.$provider))) ); ?>
				<?php else : ?>
					<?php echo anchor('#disable', "<img src=".$property['icon']." alt='' />", 
						array('title'=>sprintf(lang('connect_with_x'), lang('connect_'.$provider))) ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</span>
</h1>
<div class="form">
	<img src="<?php echo IMAGES_PATH; ?>auth/top.png" width="676" height="7" />
	<?php echo form_open(uri_string().($this->input->get('continue')?'/?continue='.urlencode($this->input->get('continue')):''), array('class'=>'login_form')); ?>
    <div class="top">
        <div class="col_left">
			<div>
			<?php echo form_label(lang('sign_in_username_email'), 'sign_in_username_email'); ?>
				<?php echo form_input(array(
                        'name' => 'sign_in_username_email',
                        'id' => 'sign_in_username_email',
						'required' => 'required',
                        'value' => set_value('sign_in_username_email'),
                        'maxlength' => '24'
                    )); ?>
            </div>
    	</div><!-- col_left -->
        <div class="col_right">
            <div>
		<?php echo form_label(lang('sign_in_password'), 'sign_in_password'); ?>
                <?php echo form_password(array(
                        'name' => 'sign_in_password',
                        'id' => 'sign_in_password',
						'required' => 'required',
                        'value' => set_value('sign_in_password')
                    )); ?>
                <span><?php echo anchor('auth/forgot_password', lang('sign_in_forgot_your_password')); ?></span>
            </div>
        </div><!-- col_right -->
        <div class="clear"></div>
		
		<?php if (isset($recaptcha)) : ?>
		<div class="">
			<?php echo $recaptcha; ?>
		</div>
		<?php endif; ?>
		
    	<div class="errors">
			<?php echo form_error('sign_in_username_email'); ?>
			
			<?php if (isset($sign_in_username_email_error)) : ?>
		        <p><?php echo $sign_in_username_email_error; ?></p>
		    <?php endif; ?>
			
			<?php if (isset($sign_in_recaptcha_error)) : ?>
				<p><?php echo $sign_in_recaptcha_error; ?></p>
			<?php endif; ?>
			
			<?php if (isset($sign_in_error)) : ?> 
				<p><?php echo $sign_in_error; ?></p>
			<?php endif; ?>
		</div>
		
     </div><!-- top -->
     <div class="bottom">
        <div class="button">
            <div class="left">
                <span style="padding-top:6px; display:inline-block;">
						<?php echo form_checkbox(array(
                            'name' => 'sign_in_remember',
                            'id' => 'sign_in_remember',
                            'value' => 'checked',
                            'checked' => $this->input->post('sign_in_remember')
                        )); ?> <?php echo sprintf(lang('sign_in_remember_me'), 'sign_in_remember'); ?>
				</span>
            </div><!-- left -->
            <div class="right">
				<?php echo form_button(array(
					'type' => 'submit',
					'class' => 'submitBtn',
					'content' => '<span>'.lang('sign_in_sign_in').'</span>',
					'tabindex' => '7'
				)); ?>&nbsp;<?php echo sprintf(lang('sign_in_dont_have_account'), anchor('auth/sign_up', lang('sign_in_sign_up_now'))); ?>
            </div><!-- right -->
            <div class="clear"></div>
        </div><!-- button -->
    </div><!-- bottom -->
    <img src="<?php echo IMAGES_PATH; ?>auth/bottom.png" width="676" height="13" />
    <?php echo form_close(); ?>
</div><!-- form -->      </div><!-- main -->
</body>
</html>
